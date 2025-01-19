<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'first_name', 'last_name', 'email', 'contact_number', 'address',
        'position', 'user_id', 'username', 'birthday', 'gender'
    ];
}
