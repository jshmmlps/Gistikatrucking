<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

/**
 * Manages driver/conductor data stored under the "Drivers" node in Firebase.
 * Each record can be either a driver or a conductor, determined by the 'position' field.
 */
class DriverModel extends Model
{
    protected $firebase;
    protected $driversRef;

    public function __construct()
    {
        parent::__construct();
        // Get the Firebase Realtime Database instance
        $this->firebase = Services::firebase();
        // Drivers are stored under the "Drivers" node
        $this->driversRef = $this->firebase->getReference('Drivers');
    }

    /**
     * Retrieve all drivers (and conductors) from Firebase.
     */
    public function getDrivers()
    {
        $snapshot = $this->driversRef->getSnapshot();
        return $snapshot->getValue(); // returns an assoc array or null
    }

    /**
     * Retrieve a single driver/conductor record by driver_id.
     */
    public function getDriver($driverId)
    {
        $snapshot = $this->firebase->getReference('Drivers/' . $driverId)->getSnapshot();
        return $snapshot->getValue();
    }

    /**
     * Insert a new driver/conductor record with an auto-incremented driver_id ("Driver1", "Driver2", etc.).
     */
    public function insertDriver(array $data)
    {
        // Determine the highest existing driver id
        $allDrivers = $this->getDrivers();
        $maxNum = 0;
        if ($allDrivers && is_array($allDrivers)) {
            foreach ($allDrivers as $key => $value) {
                // We expect keys like "Driver1", "Driver2", etc.
                if (preg_match('/Driver(\d+)/', $key, $matches)) {
                    $num = (int)$matches[1];
                    if ($num > $maxNum) {
                        $maxNum = $num;
                    }
                }
            }
        }

        $newDriverId = 'Driver' . ($maxNum + 1);
        $data['driver_id'] = $newDriverId;

        $this->firebase->getReference('Drivers/' . $newDriverId)->set($data);
        return $newDriverId;
    }

    /**
     * Update an existing driver/conductor record.
     */
    public function updateDriver($driverId, array $data)
    {
        $this->firebase->getReference('Drivers/' . $driverId)->update($data);
        return true;
    }

    /**
     * Delete a driver/conductor record.
     */
    public function deleteDriver($driverId)
    {
        $this->firebase->getReference('Drivers/' . $driverId)->remove();
        return true;
    }

    /**
     * Retrieve only drivers with valid geolocation (last_lat and last_lng).
     */
    public function getDriversWithLocation()
    {
        $allDrivers = $this->getDrivers();
        $driversWithLocation = [];
        
        if ($allDrivers && is_array($allDrivers)) {
            foreach ($allDrivers as $driverId => $driver) {
                if (!empty($driver['last_lat']) && !empty($driver['last_lng'])) {
                    $driversWithLocation[$driverId] = $driver;
                }
            }
        }
        
        return $driversWithLocation;
    }

}
