<?php

namespace App\Controllers;

use App\Models\TruckModel;
use CodeIgniter\Controller;

class TruckController extends Controller
{
    public function trucks()
    {
        $model = new TruckModel();
        $data['trucks'] = $model->findAll(); // Fetch all trucks

        return view('trucks', $data); // Load directly from trucks.php
    }

    public function view($id)
    {
        $model = new TruckModel();
        $data['truck'] = $model->find($id); // Fetch truck by ID

        if (!$data['truck']) {
            return redirect()->to('/trucks')->with('error', 'Truck not found.');
        }

        return view('trucks/view', $data);
    }
}
