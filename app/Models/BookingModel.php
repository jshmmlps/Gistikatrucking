<?php 
namespace App\Models;

use Models\UserModel;
use CodeIgniter\Model;
use Config\Services;

/**
 * Handles all booking-related operations on the Firebase Realtime Database.
 */
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
        $data['distance']     = 0;
    
        // Auto-assign truck & driver if cargo_weight is set
        if (isset($data['cargo_weight'])) {
            $assignment = $this->assignTruckAndDriver($data['cargo_weight']);
            if (!empty($assignment)) {
                // Merge
                $data = $data + $assignment;
            }
        }
    
        // Now $data should already have ['client_id'] from the controller
        $this->db->getReference('Bookings/' . $bookingId)->set($data);
    
        // $clientId = session()->get('user_id'); 
        // dd($clientId); // or log_message('debug', 'Client ID: '. $clientId);

        return $bookingId;
    }
    

    /**
     * Auto-assign a truck and its driver/conductor based on cargo weight.
     * Excludes any trucks that are currently assigned to active (non-completed) bookings.
     *
     * @param float|int $cargoWeight  The weight of the cargo to be transported.
     * @return array                  An associative array of assignment data (truck_id, driver_name, etc.), or empty if none found.
     */
    public function assignTruckAndDriver($cargoWeight)
    {
        // Get all trucks
        $trucksRef = $this->db->getReference('Trucks');
        $trucks    = $trucksRef->getValue();

        // Get current active bookings to filter out trucks in use
        $bookingsRef = $this->db->getReference('Bookings');
        $bookings    = $bookingsRef->getValue();

        $assignedTruckIds = [];
        if ($bookings) {
            foreach ($bookings as $booking) {
                // If booking is not completed/rejected, that truck is considered in use
                if (isset($booking['truck_id']) && !in_array($booking['status'], ['rejected', 'completed'])) {
                    $assignedTruckIds[] = $booking['truck_id'];
                }
            }
        }

        // Try to find a truck that can handle the cargo weight and is not already assigned
        if ($trucks && is_array($trucks)) {
            foreach ($trucks as $truckKey => $truck) {
                if (!isset($truck['truck_id'], $truck['load_capacity'])) {
                    continue; // skip invalid truck entries
                }

                if (
                    $truck['load_capacity'] >= $cargoWeight 
                    && !in_array($truck['truck_id'], $assignedTruckIds)
                ) {
                    // Found a suitable truck; now find driver & conductor
                    $driverInfo = $this->getDriverAndConductor($truck['truck_id']);

                    // Return all relevant assignment data
                    return [
                        'truck_id'       => $truck['truck_id'],
                        'truck_model'    => $truck['truck_model']    ?? '',
                        'license_plate'  => $truck['plate_number']   ?? '',
                        'type_of_truck'  => $truck['truck_type']     ?? '',
                        'driver_name'    => $driverInfo['driver']    ?? '',
                        'conductor_name' => $driverInfo['conductor'] ?? ''
                    ];
                }
            }
        }

        // If no truck found, return empty array
        return [];
    }

    /**
     * Helper function to get the assigned driver and conductor for a given truck ID.
     *
     * @param string|int $truckId
     * @return array  e.g. ['driver' => 'John Doe', 'conductor' => 'Jane Roe']
     */
    protected function getDriverAndConductor($truckId)
    {
        $driversRef = $this->db->getReference('Driver');
        $drivers    = $driversRef->getValue();

        $result = [
            'driver'    => '',
            'conductor' => ''
        ];

        if ($drivers && is_array($drivers)) {
            foreach ($drivers as $driverKey => $driverData) {
                if (
                    isset($driverData['truck_assigned'], $driverData['position']) 
                    && $driverData['truck_assigned'] == $truckId
                ) {
                    $fullname = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                    if ($driverData['position'] === 'driver' && empty($result['driver'])) {
                        $result['driver'] = $fullname;
                    } elseif ($driverData['position'] === 'conductor' && empty($result['conductor'])) {
                        $result['conductor'] = $fullname;
                    }
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
}
