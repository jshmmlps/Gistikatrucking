<?php

namespace App\Controllers;

use App\Models\DriverModel;
use CodeIgniter\Controller;

class DriverController extends Controller
{
    public function driver()
    {
        $model = new DriverModel();
        $data['drivers'] = $model->findAll();
        return view('driver_list', $data);
    }

    public function getDetails($employee_id)
    {
        $model = new DriverModel();
        $driver = $model->where('employee_id', $employee_id)->first();
        return $this->response->setJSON($driver);
    }
}
