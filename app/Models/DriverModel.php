<?php

namespace App\Models;

use CodeIgniter\Model;

class DriverModel extends Model
{
    protected $table = 'drivers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['first_name', 'last_name', 'contact_number', 'position', 'home_address', 'employee_id'];
}
