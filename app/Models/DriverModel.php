<?php

namespace App\Models;

class DriverModel
{
    protected $db;

    public function __construct()
    {
        // Load the Firebase service from your Services configuration
        $this->db = service('firebase');
    }

    /**
     * Retrieve all drivers from the "Tandem" node.
     *
     * @return array
     */
    public function getAllDrivers()
    {
        $reference = $this->db->getReference('Tandem');
        $snapshot  = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            return [];
        }

        $allDrivers = $snapshot->getValue();
        $formattedDrivers = [];

        foreach ($allDrivers as $key => $driverData) {
            // Build each driver record, matching the keys used in your view
            $formattedDrivers[] = [
                'firebaseKey'            => $key,
                'first_name'             => $driverData['First_name']          ?? '',
                'last_name'              => $driverData['Last_name']           ?? '',
                'contact_number'         => $driverData['Contact_number']      ?? '',
                'date_of_employment'     => $driverData['Date_of_employment']  ?? '',
                'home_address'           => $driverData['Home_address']        ?? '',
                'position'               => $driverData['Position']            ?? '',
                'employee_id'            => $driverData['Employee_ID']         ?? '',
                'last_truck_assigned'    => $driverData['Last_truck']          ?? '',
                'license_number'         => $driverData['License_number']      ?? '',
                'license_expiry_date'    => $driverData['License_expiry']      ?? '',
                'birthday'               => $driverData['Birthday']            ?? '',
                'medical_record'         => $driverData['Medical_record']      ?? '',
                'trips_completed'        => $driverData['Trip_completed']      ?? '',
                'notes'                  => $driverData['Notes']               ?? '',
            ];
        }

        return $formattedDrivers;
    }
}
