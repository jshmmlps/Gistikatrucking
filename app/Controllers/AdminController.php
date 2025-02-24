<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class AdminController extends Controller
{
    public function __construct()
    {
        helper('url'); // ensure URL helper is available
        $session = session();

        // Check if the user is logged in and has an admin role
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'admin') {
            // Set a flash error message
            $session->setFlashdata('error', 'No authorization.');
            
            // Redirect to the unified login page
            header("Location: " . base_url('login'));
            exit();
        }
    }

    public function index()
    {
        echo "Hello Admin";
    }
}
