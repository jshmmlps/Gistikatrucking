<?php

namespace App\Models;

class TruckModel
{
    protected $db;

    public function __construct()
    {
        // Load the Firebase service from your Services configuration
        $this->db = service('firebase');
    }

    /**
     * Retrieve all trucks from Firebase.
     *
     * @return array|null
     */
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
            'truckId'        => $truckData['Truck_ID']            ?? '',
            'plate_number'        => $truckData['License_plate']            ?? '',
            'name'                => $truckData['Truck_name']               ?? '',
            'fuel_type'           => $truckData['Fuel_type']                ?? '',
            'registration_expiry' => $truckData['Registration_expiry']      ?? '',
            'type'                => $truckData['Truck_type']               ?? '',
            'tmodel'              => $truckData['Truck_model']              ?? '',
            'enginenumber'        => $truckData['Engine_number']            ?? '',
            'chassis_number'      => $truckData['Chassis']                  ?? '',
            'cor'                 => $truckData['COR']                      ?? '',
            'insurance'           => $truckData['Insurance_details']        ?? '',
            'license_expiry'      => $truckData['Lisence_expiry_date']      ?? '',
            'capacity'            => $truckData['Load_capcity']             ?? '',
            'technician'          => $truckData['Maintenance_technician']   ?? '',
            'length'              => $truckData['Truck_length']             ?? '',
            'color'               => $truckData['Truck_color']              ?? '',
        ];  
    }

    return $formattedTrucks;
}

    /**
     * Retrieve a single truck by its license plate.
     *
     * @param string $licensePlate
     * @return array|null
     */
    public function getTruckByLicense(string $licensePlate)
    {
        return $this->db->getReference('trucks/' . $licensePlate)->getValue();
    }
}
