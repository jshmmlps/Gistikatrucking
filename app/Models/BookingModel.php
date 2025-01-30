<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'booking_id', 'client_name', 'booking_date', 'dispatch_date', 'cargo_type', 
        'cargo_weight', 'drop_off_location', 'contact_number', 'pick_up_location',
        'truck_model', 'conductor_name', 'license_plate', 'driver_name', 
        'distance', 'type_of_truck', 'person_of_contact'
    ];
}
