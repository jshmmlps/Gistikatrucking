<?php

namespace App\Models;

use CodeIgniter\Model;

class MaintenanceModel extends Model
{
    protected $table = 'maintenance';
    protected $primaryKey = 'id';
    protected $allowedFields = ['report_number', 'report_type', 'date'];
}
