<?php

namespace App\Models;

use CodeIgniter\Model;

class TripTicketModel extends Model
{
    protected $table = 'trip_tickets';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'booking_id', 'image_path', 'uploaded_at'
    ];
}
