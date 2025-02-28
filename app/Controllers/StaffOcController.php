<?php

namespace App\Controllers;

use App\Models\BookingModel; // Assuming there's a BookingModel for fetching bookings
use App\Models\UserModel;
use App\Models\TruckModel;
use CodeIgniter\Controller;

class StaffOcController extends Controller
{
    public function dashboard()
    {
        return view('operations_coordinator/dashboard');
    }

    public function userAccount()
    {
        // Load user data from session (or database)
        $data['user'] = session()->get('user'); // Assuming session has user info

        return view('operations_coordinator/user_account', $data);
    }

    public function bookingManagement()
    {
        $model = new BookingModel();
        $data['bookings'] = $model->findAll(); // Fetch all bookings

        return view('operations_coordinator/booking_management', $data);
    }

    public function viewBooking($id)
    {
        $model = new BookingModel();
        $data['booking'] = $model->find($id);

        if (!$data['booking']) {
            return redirect()->to('/operations/booking_management')->with('error', 'Booking not found.');
        }

        return view('operations_coordinator/booking_details', $data);
    }

    public function truckMonitoring()
    {
        $model = new TruckModel();
        $data['trucks'] = $model->findAll();

        return view('operations_coordinator/truck_monitoring', $data);
    }

    public function reportManagement()
    {
        return view('operations_coordinator/report_management');
    }

    public function uploadProfile()
    {
        if ($this->request->getMethod() == 'post') {
            $file = $this->request->getFile('profile_picture');

            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/profile_pictures', $newName);

                // Update user profile picture in the database
                $userId = session()->get('user_id'); // Get user ID from session
                $userModel = new UserModel();
                $userModel->update($userId, ['profile_picture' => $newName]);

                return redirect()->to(base_url('operations/user_account'))->with('success', 'Profile updated successfully.');
            }
        }

        return redirect()->to(base_url('operations/user_account'))->with('error', 'Failed to upload.');
    }
}
