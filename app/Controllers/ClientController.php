<?php

namespace App\Controllers;

class ClientController extends BaseController
{
    public function test()
    {
        echo "Hello Client";
    }

    public function clients()
    {
        $clients = [
            [
                'id' => 1,
                'name' => 'Fresh Farms Corporation',
                'booking_date' => '2024-11-08',
                'dispatch_date' => '2024-11-13',
                'cargo_type' => 'Fresh Produce',
                'drop_off' => 'Pasay City',
                'status' => 'Pending'
            ],
            [
                'id' => 2,
                'name' => 'Karen Villanueva',
                'booking_date' => '2024-11-09',
                'dispatch_date' => '2024-11-18',
                'cargo_type' => 'Frozen Goods',
                'drop_off' => 'Pasig City',
                'status' => 'Pending'
            ],
            // Add more sample data...
        ];

        return view('client_management', ['clients' => $clients]);
    }

    public function view($id)
    {
        // Example data for a single client
        $client = [
            'id' => $id,
            'name' => 'Fresh Farms Corporation',
            'contact_person' => 'John Doe',
            'email' => 'contact@freshfarms.com',
            'contact_number' => '09171234567',
            'address' => '123 Market Street, Pasay City',
            'username' => 'freshfarms',
            'business_type' => 'Agriculture',
            'preferred_truck' => 'Refrigerated Truck',
            'cargo_type' => 'Fresh Produce',
            'payment_mode' => 'Cash',
            'pickup_location' => 'Farm, Laguna',
            'dropoff_location' => 'Pasay City',
            'client_since' => '2020-01-15',
            'notes' => 'Deliver only in the morning.'
        ];

        return view('client_details', ['client' => $client]);
    }
}
