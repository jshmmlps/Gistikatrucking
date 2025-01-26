<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_name', 'booking_date', 'dispatch_date', 'cargo_type', 
        'drop_off_location', 'status'
    ];
}
