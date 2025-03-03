<?php
namespace App\Models;

use CodeIgniter\Model;

class DriverModel extends Model
{
    protected $firebase;
    protected $dbRef;
    
    public function __construct()
    {
        parent::__construct();
        // Get the Firebase database instance from our service
        $this->firebase = service('firebase');
        // All driver records will be stored under the "Drivers" node
        $this->dbRef = $this->firebase->getReference('Drivers');
    }
    
    // Retrieve all driver records
    public function getDrivers()
    {
        $snapshot = $this->dbRef->getSnapshot();
        return $snapshot->getValue();
    }
    
    // Retrieve a single driver by its ID
    public function getDriver($driverId)
    {
        $snapshot = $this->firebase->getReference('Drivers/' . $driverId)->getSnapshot();
        return $snapshot->getValue();
    }
    
    // Insert a new driver record with an auto-incremented Driver ID
    public function insertDriver($data)
    {
        // Get existing drivers to determine the highest driver id currently
        $drivers = $this->getDrivers();
        $maxId = 0;
        if ($drivers) {
            foreach ($drivers as $key => $value) {
                if (preg_match('/Driver(\d+)/', $key, $matches)) {
                    $id = (int)$matches[1];
                    if ($id > $maxId) {
                        $maxId = $id;
                    }
                }
            }
        }
        // New driver id will be Driver1, Driver2, etc.
        $newDriverId = 'Driver' . ($maxId + 1);
        $data['driver_id'] = $newDriverId;
        $this->firebase->getReference('Drivers/' . $newDriverId)->set($data);
        return $newDriverId;
    }
    
    // Update an existing driver record
    public function updateDriver($driverId, $data)
    {
        $this->firebase->getReference('Drivers/' . $driverId)->update($data);
        return true;
    }
    
    // Delete a driver record (only from the Drivers collection)
    public function deleteDriver($driverId)
    {
        $this->firebase->getReference('Drivers/' . $driverId)->remove();
        return true;
    }
}
