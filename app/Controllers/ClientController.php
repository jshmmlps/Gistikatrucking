<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use App\Models\BookingModel;
use CodeIgniter\Controller;

class ClientController extends BaseController
{
    protected $userModel;
    protected $bookingModel;

    public function __construct()
    {
        // Load session and verify client authorization
        $session = session();
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'client') {
            $session->setFlashdata('error', 'No authorization.');
            redirect()->to(base_url('login'))->send();
            exit; // Stop further execution
        }
        
        $this->userModel    = new UserModel();
        $this->bookingModel = new BookingModel();
    }

    public function dashboard()
    {
        return view('client/dashboard');
    }
    
    // =================== PROFILE =================== 
    
     /**
     * Display the admin profile page.
     */
    public function profile()
    {
        $session = session();
        $firebaseKey = $session->get('firebaseKey');
        $userData = $this->userModel->getUser($firebaseKey);
        // var_dump($firebaseKey);
        // var_dump($userData);
        // exit;
        return view('client/profile', ['user' => $userData]);
    }
    
    /**
     * Process the update of profile details.
     */
    public function updateProfile()
    {
        $session = session();
        $firebaseKey = $session->get('firebaseKey');

        $data = [
            'first_name'     => $this->request->getPost('first_name'),
            'last_name'      => $this->request->getPost('last_name'),
            'email'          => $this->request->getPost('email'),
            'username'       => $this->request->getPost('username'),
            'contact_number' => $this->request->getPost('contact_number'),
            'address'        => $this->request->getPost('address'),
            'birthday'       => $this->request->getPost('birthday'),
            'gender'         => $this->request->getPost('gender'),
        ];

        $this->userModel->updateUser($firebaseKey, $data);
        $session->setFlashdata('success', 'Profile updated successfully.');
        return redirect()->to('client/profile');
    }

    public function bookings()
    {
        $session  = session();
        $clientId = $session->get('user_id');    // Must match what was stored when user logged in

        $bookingModel = new BookingModel();
        $data['bookings'] = $bookingModel->getBookingsByClient($clientId);

        return view('client/bookings', $data);
    }

    // Show the create booking form
    public function createBooking()
    {
        return view('client/create_booking');
    }

    // Process the create booking form submission
    public function storeBooking()
    {
        $session  = session();
        $clientId = $session->get('user_id'); // get user_id here in the controller
    
        $data = $this->request->getPost();
        // Tag your booking with the client ID
        $data['client_id'] = $clientId;
    
        // Now pass $data to the model
        $bookingId = $this->bookingModel->createBooking($data);

        $session->setFlashdata('success', 'Booking created with ID: ' . $bookingId);
        return redirect()->to(base_url('client/bookings'));
    }

    // public function profile()
    // {
    //     return view('client/profile');
    // }

    public function geolocation()
    {
        return view('client/geolocation');
    }

    public function report()
    {
        return view('client/report');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('login'))->send();
    }
}
