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
                throw new \RuntimeException('No available driver or conductors');
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
     * Excludes trucks in current active (non-completed/rejected) bookings on the first pass.
     * If none is found, it re-tries ignoring the concurrency restriction
     * so the same truck can be used again.
     *
     * @param float|int $cargoWeight  The weight of the cargo to be transported.
     * @return array                  An associative array of assignment data, or empty if none found.
     */
    public function assignTruckAndDriver($cargoWeight)
    {
        // Get all trucks
        $trucksRef = $this->db->getReference('Trucks');
        $trucks    = $trucksRef->getValue();

        // Get current bookings to figure out which trucks are in active use
        $bookingsRef = $this->db->getReference('Bookings');
        $bookings    = $bookingsRef->getValue();

        // Collect IDs of trucks that are "in use" (not completed/rejected)
        $assignedTruckIds = [];
        if ($bookings && is_array($bookings)) {
            foreach ($bookings as $booking) {
                if (
                    isset($booking['truck_id']) 
                    && !in_array($booking['status'], ['rejected', 'completed'])
                ) {
                    $assignedTruckIds[] = $booking['truck_id'];
                }
            }
        }

        // ------ First Pass: exclude trucks that are currently "in use" ------
        $firstPass = $this->tryAssign($trucks, $assignedTruckIds, $cargoWeight, $excludeInUse = true);
        if (!empty($firstPass)) {
            return $firstPass;
        }

        // ------ Second Pass (Fallback): ignore the concurrency rule ------
        // Let the same truck (already in use) be used again, if necessary
        $secondPass = $this->tryAssign($trucks, $assignedTruckIds, $cargoWeight, $excludeInUse = false);
        if (!empty($secondPass)) {
            return $secondPass;
        }

        // Return empty if no suitable truck found even after fallback
        return [];
    }

    /**
     * Internal helper that loops through the trucks array, applying an optional "excludeInUse" filter,
     * to see if we can find a truck that can handle the cargo weight and has at least one assigned driver or conductor.
     */
    protected function tryAssign($trucks, $assignedTruckIds, $cargoWeight, $excludeInUse)
    {
        if ($trucks && is_array($trucks)) {
            foreach ($trucks as $truck) {
                if (!isset($truck['truck_id'], $truck['load_capacity'])) {
                    continue; // skip invalid truck
                }

                // If excluding in-use trucks, skip if truck is in assignedTruckIds
                if ($excludeInUse && in_array($truck['truck_id'], $assignedTruckIds)) {
                    continue;
                }

                // Check capacity
                if ($truck['load_capacity'] >= $cargoWeight) {
                    // Get assigned driver info
                    $driverInfo = $this->getDriverAndConductor($truck['truck_id']);

                    // If we have at least a driver or conductor
                    if (!empty($driverInfo['driver_name']) || !empty($driverInfo['conductor_name'])) {
                        return [
                            'truck_id'        => $truck['truck_id'],
                            'truck_model'     => $truck['truck_model']    ?? '',
                            'license_plate'   => $truck['plate_number']   ?? '',
                            'type_of_truck'   => $truck['truck_type']     ?? '',
                            'driver_id'       => $driverInfo['driver_id']       ?? '',
                            'driver_name'     => $driverInfo['driver_name']     ?? '',
                            'conductor_id'    => $driverInfo['conductor_id']    ?? '',
                            'conductor_name'  => $driverInfo['conductor_name']  ?? ''
                        ];
                    }
                }
            }
        }
        // If no match found, return empty
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
    public function isAnyDriverAvailable(): bool
    {
        // 1) Get the list of trucks
        $trucksRef = $this->db->getReference('Trucks');
        $trucks = $trucksRef->getValue();

        // 2) Get all bookings to figure out which trucks are in active use
        $bookingsRef = $this->db->getReference('Bookings');
        $bookings = $bookingsRef->getValue();

        // 3) Determine which trucks are in use (not completed or rejected)
        $inUseTruckIds = [];
        if ($bookings && is_array($bookings)) {
            foreach ($bookings as $booking) {
                if (
                    isset($booking['truck_id']) &&
                    !in_array($booking['status'], ['rejected', 'completed']) 
                ) {
                    $inUseTruckIds[] = $booking['truck_id'];
                }
            }
        }

        // 4) Check among the *free* trucks if at least one has a driver
        if ($trucks && is_array($trucks)) {
            foreach ($trucks as $truck) {
                if (!isset($truck['truck_id'])) {
                    continue;  // skip invalid entries
                }

                // if truck is not in use, see if it has at least a driver
                if (!in_array($truck['truck_id'], $inUseTruckIds)) {
                    // Reuse your existing helper to find an assigned driver
                    $driverInfo = $this->getDriverAndConductor($truck['truck_id']);

                    // if we have a driver name or ID, that means there's a driver available
                    if (!empty($driverInfo['driver_name'])) {
                        return true;  // found a free truck with a driver
                    }
                }
            }
        }

        // If we never found a free truck that has a driver, return false
        return false;
    }

}
