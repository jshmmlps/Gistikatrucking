<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\TripTicketModel;
use CodeIgniter\Controller;

class BookingController extends Controller
{
    public function index()
    {
        $bookingModel = new BookingModel();
        $data['bookings'] = $bookingModel->findAll();

        return view('booking_view', $data);
    }

    public function view($id)
    {
        $bookingModel = new BookingModel();
        $tripModel = new TripTicketModel();

        $data['booking'] = $bookingModel->find($id);
        $data['tripTickets'] = $tripModel->where('booking_id', $id)->findAll();

        return view('booking_details', $data);
    }

    public function uploadTripTicket()
    {
        $tripModel = new TripTicketModel();
        
        $file = $this->request->getFile('trip_image');
        if ($file->isValid() && !$file->hasMoved()) {
            $filePath = $file->store('uploads/trip_tickets');
            $tripModel->insert([
                'booking_id' => $this->request->getPost('booking_id'),
                'image_path' => $filePath,
            ]);

            return redirect()->back()->with('message', 'Trip ticket uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload trip ticket.');
    }
}
