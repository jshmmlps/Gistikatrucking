<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function login()
    {
        return view('auth/login');
    }

    /**
     * Process login form submission.
     * Supports using either email or username as the identifier.
     */
    public function processLogin()
    {
        // Retrieve the login identifier (email or username) from the form input.
        $identifier = $this->request->getPost('username'); 
        $password   = $this->request->getPost('password');
        
        $userModel = new UserModel();
        $user      = $userModel->verifyCredentials($identifier, $password);

        if (!$user) {
            // If no user returned, credentials are invalid
            return redirect()->back()->with('error', 'Invalid credentials.');
        }

        // ---------------------------------------------------------------------
        //  Store session data. 
        //  IMPORTANT: Use one consistent ID (like 'user_id') so you can 
        //  reference it for Bookings, etc.
        // ---------------------------------------------------------------------
        session()->set([
            'loggedIn'   => true,
            // If "firebaseKey" is something like "User1", store that as 'user_id':
            'firebaseKey' => $user['firebaseKey'], // Store Firebase key explicitly.
            'user_id'    => $user['firebaseKey'],
            // Or if your database uses a numeric ID, do:
            // 'user_id' => $user['user_id'],

            'user_level' => $user['user_level'],
            'username'   => $user['username'],
            'first_name' => $user['first_name'],
            'last_name'  => $user['last_name'],
            // Add any other user info you need, e.g. 'email', 'contact_number', etc.
        ]);

        // Redirect based on user level
        switch ($user['user_level']) {
            case 'admin':
                return redirect()->to('admin/dashboard');
            case 'operation manager':
                return redirect()->to('operations/dashboard');
            case 'resource manager':
                return redirect()->to('resource/dashboard');
            case 'client':
                return redirect()->to('client/dashboard');
            default:
                return redirect()->back()->with('error', 'User role not recognized.');
        }
    }

    /**
     * Log the user out.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
