<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use CodeIgniter\Controller;

class ClientController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        // Load your session and check admin auth
        $session = session();
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'client') {
            $session->setFlashdata('error', 'No authorization.');
            redirect()->to(base_url('login'))->send();
            exit; // Stop further execution
        }        
        
        $this->userModel = new UserModel();
    }

    public function dashboard()
    {
        return view('client/dashboard');
    }

    public function bookings()
    {
        return view('client/bookings');
    }

    public function profile()
    {
        return view('client/profile');
    }

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
