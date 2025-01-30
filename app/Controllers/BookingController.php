<?php

namespace App\Controllers;

use App\Models\BookingModel;
use CodeIgniter\Controller;

class BookingController extends Controller
{
    protected $bookingModel;

    public function __construct()
    {
        $this->bookingModel = new BookingModel();
    }

    public function index()
    {
        $data['bookings'] = $this->bookingModel->findAll();
        return view('booking_management', $data);
    }

    public function details($id)
    {
        $booking = $this->bookingModel->find($id);
        return $this->response->setJSON($booking);
    }
}
