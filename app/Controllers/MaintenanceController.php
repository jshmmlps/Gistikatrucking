<?php

namespace App\Controllers;

use App\Models\MaintenanceModel;
use CodeIgniter\Controller;

class MaintenanceController extends Controller
{
    public function maintenance()
    {
        $model = new MaintenanceModel();
        $data['records'] = $model->findAll(); // Fetch all maintenance records

        return view('maintenance_record', $data);
    }

    public function view($id)
    {
        $model = new MaintenanceModel();
        $data['record'] = $model->find($id); // Fetch record by ID

        if (!$data['record']) {
            return redirect()->to('/maintenance')->with('error', 'Record not found.');
        }

        return view('maintenance_view', $data);
    }
}
