<?php

namespace App\Controllers;

use App\Models\MaintenanceModel;
use CodeIgniter\Controller;

class MaintenanceController extends Controller
{
    public function maintenance()
    {
        $model = new MaintenanceModel();
        // Retrieve maintenance data from the model
        $data['maintenanceData'] = $model->getMaintenanceData();
        return view('maintenance', $data);
    }
}
