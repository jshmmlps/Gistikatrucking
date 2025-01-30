<?php

namespace App\Controllers;

use App\Models\DriverModel;
use CodeIgniter\Controller;

class DriverController extends BaseController
{
    protected $driverModel;

    public function __construct()
    {
        $this->driverModel = new DriverModel();
    }

    public function drivers()
    {
        $data['drivers'] = $this->driverModel->findAll();
        return view('driver_management', $data);
    }

    public function details($id)
    {
        $driver = $this->driverModel->find($id);
        return $this->response->setJSON($driver);
    }
}
