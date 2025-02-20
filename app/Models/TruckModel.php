<?php

namespace App\Models;

use CodeIgniter\Model;

class TruckModel extends Model
{
    protected $table = 'trucks';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name', 'plate_number', 'engine_number', 'chassis_number', 'color',
        'certificate_registration', 'insurance_details', 'license_plate_expiry',
        'registration_expiry', 'type', 'wheels', 'fuel_type', 'truck_length',
        'load_capacity', 'maintenance_technician', 'status'
    ];
}