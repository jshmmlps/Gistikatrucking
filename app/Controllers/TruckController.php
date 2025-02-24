<?php

namespace App\Controllers;

use App\Models\TruckModel;
use CodeIgniter\Controller;

class TruckController extends Controller
{
    public function trucks()
    {
        $model = new TruckModel();
        $data['trucks'] = $model->getAllTrucks(); // custom method name
    
        return view('trucks', $data);
    }    

    public function view($id)
    {
        $model = new TruckModel();

        $data['trucks'] = $model->find($id); // retriving truck data by ID

        if (!$data['trucks']) {
            return redirect()->to('/trucks')->with('error', 'Truck not found.');
        }

        return view('trucks', $data);
    }
}
