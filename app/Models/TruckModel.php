<?php

namespace App\Models;

use CodeIgniter\Model;

class TruckModel extends Model
{
    protected $table = 'trucks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'plate_number', 'type', 'wheels', 'status'];
}
