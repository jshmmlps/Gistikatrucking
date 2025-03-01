<?php
namespace App\Models;

use CodeIgniter\Model;

class TruckModel extends Model
{
    protected $firebase;
    protected $dbRef;
    
    public function __construct()
    {
        parent::__construct();
        // Get the Firebase database instance from your service
        $this->firebase = service('firebase');
        // All trucks will be stored under the "Trucks" node (capital T)
        $this->dbRef = $this->firebase->getReference('Trucks');
    }
    
    // Retrieve all truck records
    public function getTrucks()
    {
        $snapshot = $this->dbRef->getSnapshot();
        return $snapshot->getValue();
    }
    
    // Retrieve a single truck by its ID
    public function getTruck($truckId)
    {
        $snapshot = $this->firebase->getReference('Trucks/' . $truckId)->getSnapshot();
        return $snapshot->getValue();
    }
    
    // Insert a new truck record with an auto-incremented Truck ID (Truck1, Truck2, etc.)
    public function insertTruck($data)
    {
        // Get all trucks to determine the highest truck id currently
        $trucks = $this->getTrucks();
        $maxId = 0;
        if ($trucks) {
            foreach ($trucks as $key => $value) {
                // Assuming keys are in the format "Truck1", "Truck2", etc.
                if (preg_match('/Truck(\d+)/', $key, $matches)) {
                    $id = (int)$matches[1];
                    if ($id > $maxId) {
                        $maxId = $id;
                    }
                }
            }
        }
        // New Truck ID is the next number (Truck1, Truck2, ...)
        $newTruckId = 'Truck' . ($maxId + 1);
        $data['truck_id'] = $newTruckId;
        // Save the new truck record under the "Trucks" node
        $this->firebase->getReference('Trucks/' . $newTruckId)->set($data);
        return $newTruckId;
    }
    
    // Update an existing truck record
    public function updateTruck($truckId, $data)
    {
        $this->firebase->getReference('Trucks/' . $truckId)->update($data);
        return true;
    }
    
    // Delete a truck record
    public function deleteTruck($truckId)
    {
        $this->firebase->getReference('Trucks/' . $truckId)->remove();
        return true;
    }
}
