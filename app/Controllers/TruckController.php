<?php

namespace App\Controllers;

use App\Models\TruckModel;
use CodeIgniter\Controller;

class TruckController extends Controller
{
    public function trucks()
    {
        $model = new TruckModel();
        $data['trucks'] = $model->findAll(); // Fetch all truck records

        return view('trucks', $data);
    }

    public function view($id)
    {
        $model = new TruckModel();
        $data['truck'] = $model->find($id); // Fetch single truck details

        if (!$data['truck']) {
            return redirect()->to('/trucks')->with('error', 'Truck not found.');
        }

        return view('trucks', $data);
    }
}
