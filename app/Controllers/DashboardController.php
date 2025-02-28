<?php

namespace App\Controllers;

class DashboardController extends BaseController
{
    public function __construct()
    {
        helper('url'); // ensure URL helper is available
        $session = session();

        // Check if the user is logged in and has an admin role
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'admin') {
            // Set a flash error message
            $session->setFlashdata('error', 'No authorization.');
            
            // Redirect to the unified login page
            header("Location: " . base_url('login'));
            exit();
        }
    }

    public function dashboard()
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
