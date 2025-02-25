<?php

namespace App\Models;

class MaintenanceModel
{
    public function getMaintenanceData()
    {
        // For demonstration, return hard-coded data.
        return [
            [
                'truckId'         => 101,
                'lastMaintenance' => "2023-09-01",
                'nextMaintenance' => "2023-12-01",
                'mileage'         => 12000,
                'distanceTraveled'=> 5000,
                'status'          => "Good",
                'engine'          => "Good",
                'battery'         => "Good",
                'oil'             => "80%",
                'gas'             => "50L",
                'fuelConsumption' => 120
            ],
            [
                'truckId'         => 102,
                'lastMaintenance' => "2023-08-15",
                'nextMaintenance' => "2023-11-15",
                'mileage'         => 15000,
                'distanceTraveled'=> 7000,
                'status'          => "Due Soon",
                'engine'          => "Fair",
                'battery'         => "Low",
                'oil'             => "60%",
                'gas'             => "40L",
                'fuelConsumption' => 150
            ],
            [
                'truckId'         => 103,
                'lastMaintenance' => "2023-07-20",
                'nextMaintenance' => "2023-10-20",
                'mileage'         => 18000,
                'distanceTraveled'=> 9000,
                'status'          => "Overdue",
                'engine'          => "Needs Service",
                'battery'         => "Critical",
                'oil'             => "30%",
                'gas'             => "20L",
                'fuelConsumption' => 200
            ]
        ];
    }
}
