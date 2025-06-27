<?php
namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class BookingModel extends Model
{
    /**
     * @var \Kreait\Firebase\Database\Database
     */
    protected $db;

    public function __construct()
    {
        parent::__construct();
        // Get the Firebase Realtime Database instance from your Services config
        $this->db = Services::firebase();
    }

    /**
     * Retrieve the highest existing booking_id in Firebase so we can manually increment.
     *
     * @return int
     */
    public function getLastBookingId()
    {
        $ref      = $this->db->getReference('Bookings');
        $bookings = $ref->getValue();

        $maxId = 0;
        if ($bookings) {
            foreach ($bookings as $booking) {
                if (isset($booking['booking_id']) && $booking['booking_id'] > $maxId) {
                    $maxId = $booking['booking_id'];
                }
            }
        }
        return $maxId;
    }

    /**
     * Create a new booking entry in Firebase, including auto-assignment of truck & driver if applicable.
     *
     * @param array $data  Data from form submission (includes 'cargo_weight', 'client_id', etc.)
     * @return int         The newly created booking_id.
     * @throws \RuntimeException if no truck with an available driver or conductor is found.
     */
    public function createBooking($data)
    {
        // Manually increment booking_id
        $lastId    = $this->getLastBookingId();
        $bookingId = $lastId + 1;
    
        // Force booking_id, date, status, etc.
        $data['booking_id']   = $bookingId;
        $data['booking_date'] = date('Y-m-d H:i:s');
        $data['status']       = 'pending';

        // Auto-assign truck & driver if cargo_weight is set
        if (isset($data['cargo_weight'])) {
            $assignment = $this->assignTruckAndDriver($data['cargo_weight']);
            if (empty($assignment)) {
                // Throw an exception if no truck/driver match is found
                throw new \RuntimeException('No available trucks can handle the specified cargo weight.');
            }
            // Merge assignment data while preserving existing keys (e.g., client_id)
            $data = $data + $assignment;
        }

        // Save booking into the "Bookings" node under booking_id
        $this->db->getReference('Bookings/' . $bookingId)->set($data);

        return $bookingId;
    }

    /**
     * Auto-assign a truck and its driver/conductor based on cargo weight.
     * Now handles both numeric weight values and weight range selections.
     */
    public function assignTruckAndDriver($cargoWeight)
    {
        $maxWeight = $this->convertWeightRangeToMax($cargoWeight);
        
        // Get all data
        $trucks = $this->db->getReference('Trucks')->getValue() ?? [];
        $bookings = $this->db->getReference('Bookings')->getValue() ?? [];
        $drivers = $this->db->getReference('Drivers')->getValue() ?? [];

        // 1. Count active bookings per truck
        $truckUsageCount = [];
        $activeTruckIds = [];
        
        foreach ($bookings as $booking) {
            if (!in_array($booking['status'] ?? null, ['rejected', 'completed']) && isset($booking['truck_id'])) {
                $truckId = $booking['truck_id'];
                $activeTruckIds[$truckId] = true;
                $truckUsageCount[$truckId] = ($truckUsageCount[$truckId] ?? 0) + 1;
            }
        }

        // 2. Find all qualified trucks (regardless of availability)
        $qualifiedTrucks = [];
        foreach ($trucks as $truck) {
            if (isset($truck['truck_id'], $truck['load_capacity'])) {
                $truckCapacity = (float)$truck['load_capacity'];
                if ($truckCapacity >= $maxWeight) {
                    // Store the capacity difference for sorting
                    $truck['capacity_diff'] = $truckCapacity - $maxWeight;
                    $qualifiedTrucks[$truck['truck_id']] = $truck;
                }
            }
        }

        // 3. Prioritize assignment - first try available trucks, then busy ones
        $assignment = $this->findBestAssignment($qualifiedTrucks, $activeTruckIds, $truckUsageCount, $drivers, $maxWeight);
        
        return $assignment ?? [];
    }

    protected function findBestAssignment($qualifiedTrucks, $activeTruckIds, $truckUsageCount, $drivers, $maxWeight)
    {
        // Group trucks by availability
        $availableTrucks = [];
        $busyTrucks = [];
        
        foreach ($qualifiedTrucks as $truckId => $truck) {
            if (!isset($activeTruckIds[$truckId])) {
                $availableTrucks[$truckId] = $truck;
            } else {
                $busyTrucks[$truckId] = $truck;
            }
        }

        // Sort available trucks by how close they are to the required capacity (smallest diff first)
        uasort($availableTrucks, function($a, $b) {
            return $a['capacity_diff'] <=> $b['capacity_diff'];
        });

        // 1. First try available trucks, starting with the one closest to required capacity
        foreach ($availableTrucks as $truckId => $truck) {
            if ($assignment = $this->buildAssignment($truck, $drivers)) {
                return $assignment;
            }
        }

        // Sort busy trucks by usage count (least used first) and then by capacity diff
        uasort($busyTrucks, function($a, $b) use ($truckUsageCount) {
            // First sort by usage count
            $usageCompare = ($truckUsageCount[$a['truck_id']] ?? 0) <=> ($truckUsageCount[$b['truck_id']] ?? 0);
            if ($usageCompare !== 0) {
                return $usageCompare;
            }
            // If usage is equal, sort by capacity difference
            return $a['capacity_diff'] <=> $b['capacity_diff'];
        });

        // 2. If none available, try busy trucks sorted by least used and closest capacity
        foreach ($busyTrucks as $truckId => $truck) {
            if ($assignment = $this->buildAssignment($truck, $drivers)) {
                return $assignment;
            }
        }

        return null;
    }

    protected function buildAssignment($truck, $drivers)
    {
        // Find drivers assigned to this truck
        $assignedDrivers = array_filter($drivers, function($driver) use ($truck) {
            return ($driver['truck_assigned'] ?? null) === $truck['truck_id'];
        });

        // Find first available driver-conductor pair
        foreach ($assignedDrivers as $driver) {
            if ($driver['position'] === 'driver') {
                $driverInfo = [
                    'driver_id' => $driver['driver_id'],
                    'driver_name' => ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')
                ];
                
                // Try to find conductor for this driver
                foreach ($assignedDrivers as $conductor) {
                    if ($conductor['position'] === 'conductor') {
                        return [
                            'truck_id' => $truck['truck_id'],
                            'truck_model' => $truck['truck_model'] ?? '',
                            'license_plate' => $truck['plate_number'] ?? '',
                            'type_of_truck' => $truck['truck_type'] ?? '',
                            'driver_id' => $driverInfo['driver_id'],
                            'driver_name' => $driverInfo['driver_name'],
                            'conductor_id' => $conductor['driver_id'],
                            'conductor_name' => ($conductor['first_name'] ?? '') . ' ' . ($conductor['last_name'] ?? '')
                        ];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Converts weight range selection to maximum weight value
     */
    protected function convertWeightRangeToMax($weightInput)
    {
        if (is_numeric($weightInput)) {
            return (float)$weightInput;
        }

        $ranges = [
            '0-500' => 500,
            '500-1000' => 1000,
            '1000-5000' => 5000,
            '5000-10000' => 10000,
            '10000-20000' => 20000,
            '20000+' => PHP_FLOAT_MAX // Handle very large loads
        ];

        return $ranges[$weightInput] ?? 0;
    }

    /**
     * Internal helper to find suitable truck assignments
     */
    protected function tryAssign($trucks, $assignedTruckIds, $maxWeight, $excludeInUse)
    {
        if ($trucks && is_array($trucks)) {
            foreach ($trucks as $truck) {
                if (!isset($truck['truck_id'], $truck['load_capacity'])) {
                    continue;
                }

                // Skip if excluding in-use trucks and this truck is busy
                if ($excludeInUse && in_array($truck['truck_id'], $assignedTruckIds)) {
                    continue;
                }

                // Convert truck's load capacity to float (in case it's stored as string)
                $truckCapacity = (float)$truck['load_capacity'];

                // Check if truck can handle the weight
                if ($truckCapacity >= $maxWeight) {
                    $driverInfo = $this->getDriverAndConductor($truck['truck_id']);

                    if (!empty($driverInfo['driver_name']) || !empty($driverInfo['conductor_name'])) {
                        return [
                            'truck_id' => $truck['truck_id'],
                            'truck_model' => $truck['truck_model'] ?? '',
                            'license_plate' => $truck['plate_number'] ?? '',
                            'type_of_truck' => $truck['truck_type'] ?? '',
                            'driver_id' => $driverInfo['driver_id'] ?? '',
                            'driver_name' => $driverInfo['driver_name'] ?? '',
                            'conductor_id' => $driverInfo['conductor_id'] ?? '',
                            'conductor_name' => $driverInfo['conductor_name'] ?? ''
                        ];
                    }
                }
            }
        }
        return [];
    }

    /**
     * Helper function to get the assigned driver and conductor for a given truck ID.
     * 1) Checks if any driver/conductor has truck_assigned == $truckId.
     * 2) If none, picks a random driver/conductor who has either no truck assigned or the same truck ID.
     *    Returns both the name and the firebase key (as driver_id or conductor_id).
     */
    protected function getDriverAndConductor($truckId)
    {
        $driversRef = $this->db->getReference('Drivers');
        $drivers    = $driversRef->getValue();

        $result = [
            'driver_id'       => '',
            'driver_name'     => '',
            'conductor_id'    => '',
            'conductor_name'  => ''
        ];

        if ($drivers && is_array($drivers)) {

            //------------------------------------------
            // 1) Check for driver/conductor on this truck
            //------------------------------------------
            foreach ($drivers as $driverKey => $driverData) {
                if (
                    isset($driverData['truck_assigned'], $driverData['position']) &&
                    $driverData['truck_assigned'] === $truckId
                ) {
                    $fullname = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                    $pos      = strtolower($driverData['position']);
                    if ($pos === 'driver' && empty($result['driver_id'])) {
                        $result['driver_id']   = $driverKey;
                        $result['driver_name'] = $fullname;
                    } elseif ($pos === 'conductor' && empty($result['conductor_id'])) {
                        $result['conductor_id']   = $driverKey;
                        $result['conductor_name'] = $fullname;
                    }
                }
            }

            //------------------------------------------
            // 2) If still missing a driver or conductor,
            //    pick a random one who is either unassigned
            //    or assigned to the same truck ID.
            //------------------------------------------
            if (empty($result['driver_id'])) {
                $possibleDrivers = [];
                foreach ($drivers as $key => $driverData) {
                    if (
                        isset($driverData['position']) &&
                        strtolower($driverData['position']) === 'driver'
                    ) {
                        // If they're assigned to a different truck, skip
                        if (
                            !empty($driverData['truck_assigned']) &&
                            $driverData['truck_assigned'] !== $truckId
                        ) {
                            continue;
                        }
                        $fullname = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                        $possibleDrivers[] = [
                            'driver_key' => $key,
                            'driver_name'=> $fullname
                        ];
                    }
                }
                if (!empty($possibleDrivers)) {
                    $randomDriver = $possibleDrivers[array_rand($possibleDrivers)];
                    $result['driver_id']   = $randomDriver['driver_key'];
                    $result['driver_name'] = $randomDriver['driver_name'];
                }
            }

            if (empty($result['conductor_id'])) {
                $possibleConductors = [];
                foreach ($drivers as $key => $driverData) {
                    if (
                        isset($driverData['position']) &&
                        strtolower($driverData['position']) === 'conductor'
                    ) {
                        // Skip if assigned to a different truck
                        if (
                            !empty($driverData['truck_assigned']) &&
                            $driverData['truck_assigned'] !== $truckId
                        ) {
                            continue;
                        }
                        $fullname = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                        $possibleConductors[] = [
                            'cond_key'  => $key,
                            'cond_name' => $fullname
                        ];
                    }
                }
                if (!empty($possibleConductors)) {
                    $randomCond = $possibleConductors[array_rand($possibleConductors)];
                    $result['conductor_id']   = $randomCond['cond_key'];
                    $result['conductor_name'] = $randomCond['cond_name'];
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve all bookings for a given client.
     *
     * @param string|int $client_id
     * @return array
     */
    public function getBookingsByClient($client_id)
    {
        $ref      = $this->db->getReference('Bookings');
        $bookings = $ref->getValue();
        $results  = [];

        if ($bookings && is_array($bookings)) {
            foreach ($bookings as $booking) {
                if (
                    is_array($booking)
                    && isset($booking['client_id'])
                    && $booking['client_id'] == $client_id
                ) {
                    $results[] = $booking;
                }
            }
        }
        return $results;
    }

    /**
     * Retrieve all bookings (suitable for admins or global views).
     *
     * @return array|null
     */
    public function getAllBookings()
    {
        $ref = $this->db->getReference('Bookings');
        return $ref->getValue(); // returns null if no data
    }

    /**
     * Update a booking's status in Firebase (e.g. 'approved', 'rejected', 'completed', etc.).
     *
     * @param int    $bookingId
     * @param string $status
     * @return bool
     */
    public function updateBookingStatus($bookingId, $status)
    {
        $ref = $this->db->getReference('Bookings/' . $bookingId);
        $ref->update(['status' => $status]);
        return true;
    }

    /**
     * Update a booking with arbitrary data in Firebase.
     *
     * @param int   $bookingId
     * @param array $data
     * @return bool
     */
    public function updateBooking($bookingId, $data)
    {
        $ref = $this->db->getReference('Bookings/' . $bookingId);
        $ref->update($data);
        return true;
    }

    /**
     * Checks if there is at least one free truck that has a driver or conductor assigned.
     * Returns true if at least one driver is available, false otherwise.
     */
    // public function isAnyDriverAvailable(): bool
    // {
    //     // 1) Get the list of trucks
    //     $trucksRef = $this->db->getReference('Trucks');
    //     $trucks = $trucksRef->getValue();

    //     // 2) Get all bookings to figure out which trucks are in active use
    //     $bookingsRef = $this->db->getReference('Bookings');
    //     $bookings = $bookingsRef->getValue();

    //     // 3) Determine which trucks are in use (not completed or rejected)
    //     $inUseTruckIds = [];
    //     if ($bookings && is_array($bookings)) {
    //         foreach ($bookings as $booking) {
    //             if (
    //                 isset($booking['truck_id']) &&
    //                 !in_array($booking['status'], ['rejected', 'completed']) 
    //             ) {
    //                 $inUseTruckIds[] = $booking['truck_id'];
    //             }
    //         }
    //     }

    //     // 4) Check among the *free* trucks if at least one has a driver
    //     if ($trucks && is_array($trucks)) {
    //         foreach ($trucks as $truck) {
    //             if (!isset($truck['truck_id'])) {
    //                 continue;  // skip invalid entries
    //             }

    //             // if truck is not in use, see if it has at least a driver
    //             if (!in_array($truck['truck_id'], $inUseTruckIds)) {
    //                 // Reuse your existing helper to find an assigned driver
    //                 $driverInfo = $this->getDriverAndConductor($truck['truck_id']);

    //                 // if we have a driver name or ID, that means there's a driver available
    //                 if (!empty($driverInfo['driver_name'])) {
    //                     return true;  // found a free truck with a driver
    //                 }
    //             }
    //         }
    //     }

    //     // If we never found a free truck that has a driver, return false
    //     return false;
    // }

    public function isAnyDriverAvailable(): ?bool
    {
        // 1) Get all drivers and their assigned trucks
        $driversRef = $this->db->getReference('Drivers');
        $drivers = $driversRef->getValue();

        // 2) Check if there are any drivers with assigned trucks at all
        $hasDriversWithTrucks = false;
        if ($drivers && is_array($drivers)) {
            foreach ($drivers as $driver) {
                if (is_array($driver) && !empty($driver['truck_assigned'])) {
                    $hasDriversWithTrucks = true;
                    break;
                }
            }
        }

        // If no drivers have assigned trucks, return null (special case)
        if (!$hasDriversWithTrucks) {
            return null;
        }

        // 3) Get all bookings to check active (non-completed/rejected) bookings
        $bookingsRef = $this->db->getReference('Bookings');
        $bookings = $bookingsRef->getValue();

        // 4) Determine which drivers are busy (either as driver OR conductor)
        $busyDriverIds = [];
        if ($bookings && is_array($bookings)) {
            foreach ($bookings as $booking) {
                // Skip if booking is not an array or has no status
                if (!is_array($booking) || !isset($booking['status'])) {
                    continue;
                }

                // Skip completed/rejected bookings
                if (in_array($booking['status'], ['rejected', 'completed'])) {
                    continue;
                }

                // Mark driver as busy if assigned (either as driver or conductor)
                if (isset($booking['driver_id'])) {
                    $busyDriverIds[] = $booking['driver_id'];
                }
                if (isset($booking['conductor_id'])) {
                    $busyDriverIds[] = $booking['conductor_id'];
                }
            }
        }

        // 5) Check all drivers to find at least one who:
        //    - Has a truck assigned
        //    - Is NOT busy with an active booking (as driver or conductor)
        if ($drivers && is_array($drivers)) {
            foreach ($drivers as $driver) {
                // Skip if driver data is invalid
                if (!is_array($driver) || !isset($driver['driver_id'])) {
                    continue;
                }

                // Driver must:
                // - Have a truck assigned
                // - Not be in the busy list (either as driver or conductor)
                if (!empty($driver['truck_assigned']) && 
                    !in_array($driver['driver_id'], $busyDriverIds)) {
                    return true;  // found an available driver
                }
            }
        }

        // If we never found an available driver but some have trucks assigned, return false (busy)
        return false;
    }

    public function getAvailableDrivers(): array
    {
        // 1) Get all drivers and their assigned trucks
        $driversRef = $this->db->getReference('Drivers');
        $drivers = $driversRef->getValue();

        // 2) Get all bookings to check active (non-completed/rejected) bookings
        $bookingsRef = $this->db->getReference('Bookings');
        $bookings = $bookingsRef->getValue();

        // 3) Determine which drivers are busy (either as driver OR conductor)
        $busyDriverIds = [];
        if ($bookings && is_array($bookings)) {
            foreach ($bookings as $booking) {
                // Skip if booking is not an array or has no status
                if (!is_array($booking) || !isset($booking['status'])) {
                    continue;
                }

                // Skip completed/rejected bookings
                if (in_array($booking['status'], ['rejected', 'completed'])) {
                    continue;
                }

                // Mark driver as busy if assigned (either as driver or conductor)
                if (isset($booking['driver_id'])) {
                    $busyDriverIds[] = $booking['driver_id'];
                }
                if (isset($booking['conductor_id'])) {
                    $busyDriverIds[] = $booking['conductor_id'];
                }
            }
        }

        // 4) Check which drivers are free (have a truck assigned and no active booking)
        $availableDrivers = [];
        if ($drivers && is_array($drivers)) {
            foreach ($drivers as $driver) {
                // Skip if driver data is invalid
                if (!is_array($driver) || !isset($driver['driver_id'])) {
                    continue;
                }

                // Driver must:
                // - Have a truck assigned
                // - Not be in the busy list (either as driver or conductor)
                if (!empty($driver['truck_assigned']) && 
                    !in_array($driver['driver_id'], $busyDriverIds)) {
                    $availableDrivers[] = $driver;
                }
            }
        }

        return $availableDrivers;
    }
    

}
