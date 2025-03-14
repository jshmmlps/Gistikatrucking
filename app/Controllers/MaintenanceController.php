<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use DateTime;
use DateInterval;

class MaintenanceController extends Controller
{
    public function index()
    {
        // Get the Firebase Realtime Database instance
        $db = service('firebase');

        // Fetch data from the "Trucks" node
        $trucksRef = $db->getReference('Trucks');
        $snapshot = $trucksRef->getSnapshot();

        if (!$snapshot->exists()) {
            // If no truck records exist, pass empty data to the view
            return view('maintenance', [
                'totalTrucks' => 0,
                'dueTrucks'   => [],
                'chartData'   => [],
                'trucksData'  => [],
            ]);
        }

        // Get all truck records from Firebase
        $trucksData = $snapshot->getValue();

        // Array to hold trucks that are due for inspection
        $dueTrucks = [];
        
        // Define thresholds: 6 months for time and 20,000 km for mileage
        $timeInterval = new DateInterval('P6M');
        $mileageThreshold = 20000;

        // Loop through each truck record and apply inspection logic
        foreach ($trucksData as $truckId => $truck) {
            // Expected fields: 
            // - last_inspection_date (format: "YYYY-MM-DD")
            // - last_inspection_mileage
            // - current_mileage
            $lastInspectionDate    = $truck['last_inspection_date']    ?? null;
            $lastInspectionMileage = $truck['last_inspection_mileage'] ?? 0;
            $currentMileage        = $truck['current_mileage']         ?? 0;

            // Check if truck is overdue based on time
            $timeOverdue = false;
            if ($lastInspectionDate) {
                $dateNow  = new DateTime();
                $dateLast = new DateTime($lastInspectionDate);
                $dateLast->add($timeInterval);
                if ($dateNow > $dateLast) {
                    $timeOverdue = true;
                }
            }

            // Check if truck is overdue based on mileage
            $mileageOverdue = false;
            if (($currentMileage - $lastInspectionMileage) >= $mileageThreshold) {
                $mileageOverdue = true;
            }

            // Mark truck as due if either condition is met
            if ($timeOverdue || $mileageOverdue) {
                $dueTrucks[] = [
                    'truckId' => $truckId,
                    'details' => $truck
                ];
            }
        }

        // Calculate totals for chart data
        $totalTrucks = count($trucksData);
        $dueCount    = count($dueTrucks);
        $notDueCount = $totalTrucks - $dueCount;

        // Prepare chart data for Chart.js
        $chartData = [
            'labels'   => ['Due For Inspection', 'Not Due'],
            'datasets' => [[
                'label' => 'Inspection Status',
                'data'  => [$dueCount, $notDueCount],
            ]]
        ];

        // Pass all data to the view
        return view('maintenance', [
            'totalTrucks' => $totalTrucks,
            'dueTrucks'   => $dueTrucks,
            'chartData'   => $chartData,
            'trucksData'  => $trucksData,
        ]);
    }
}
