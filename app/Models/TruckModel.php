<?php

namespace App\Models;

// use CodeIgniter\Model;
use Config\Services;

class TruckModel
{
    protected $db;

    public function __construct()
    {
        // parent::construct();
        // Initialize Firebase Realtime Database from your Services
        $this->db = Services::firebase();
    }
    public function getTruckdetails($field, $value)
    {
        $reference = $this->db->getReference('trucks');
        $snapshot = $reference->getSnapshot();

        if (!$snapshot->exists()) {
            return null;
        }

        $trucks = $snapshot->getValue();  // all /trucks
        foreach ($trucks as $key => $trucksData) {
            if (isset($trucksData[$field]) && $trucksData[$field] === $value) {
                // Include the Firebase key in the returned data
                $trucksData['firebaseKey'] = $key;
                return $trucksData;
            }
        }

        return null;
    }

    public function getAllTrucks()
{
    $reference = $this->db->getReference('Truckings');
    $snapshot = $reference->getSnapshot();

    if (!$snapshot->exists()) {
        return [];
    }

    $allTrucks = $snapshot->getValue();
    $formattedTrucks = [];

    foreach ($allTrucks as $key => $truckData) {
        $formattedTrucks[] = [
            'firebaseKey'         => $key,
            'plate_number'        => $truckData['License_plate']       ?? '',
            'name'                => $truckData['Truck_name']          ?? '',
            'fuel_type'           => $truckData['Fuel_type']           ?? '',
            'registration_expiry' => $truckData['Registration_expiry'] ?? '',
            'type'                => $truckData['Truck_type']          ?? '',
        ];
    }

    return $formattedTrucks;
}


}

?>