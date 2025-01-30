<?php

namespace App\Models;

use CodeIgniter\Model;

class DriverModel extends Model
{
    protected $table = 'drivers';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'first_name', 'last_name', 'contact_number', 'position', 
        'home_address', 'employee_id', 'date_of_employment',
        'last_truck_assigned', 'license_number', 'license_expiry_date',
        'birthday', 'medical_record', 'trips_completed', 'notes'
    ];
}
