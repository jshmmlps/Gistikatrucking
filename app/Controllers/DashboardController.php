<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function index()
    {
        // Data for the dashboard (this can be fetched from a database)
        $data = [
            'available_trucks' => 4,
            'chart_data' => [
                'good_condition' => 55.3,
                'requires_maintenance' => 23.1,
                'critical_condition' => 21.6,
            ],
            'maintenance_days' => [3, 7, 2, 10, 4, 5, 8],
        ];

        // Return the view and pass the data
        return view('dashboard', $data);
    }
}
