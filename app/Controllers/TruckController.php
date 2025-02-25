<?php

namespace App\Controllers;

use App\Models\TruckModel;
use CodeIgniter\Controller;

class TruckController extends Controller
{
    public function trucks()
    {
        $truckModel = new TruckModel();
        $data['trucks'] = $truckModel->getAllTrucks();
        
        return view('trucks', $data);
    }
}

