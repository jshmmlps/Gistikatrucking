<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class AuthController extends Controller
{
    public function login()
    {
        // Display the unified login form
        return view('auth/login');
    }

    public function processLogin()
    {
        // Retrieve the login identifier (email or username) from the form input.
        $identifier = $this->request->getPost('username'); // Can be email or username
        $password   = $this->request->getPost('password');
    
        $userModel = new UserModel();
        $user = $userModel->verifyCredentials($identifier, $password);
    
        if (!$user) {
            return redirect()->back()->with('error', 'Invalid credentials.');
        }
    
        // Set session data for the logged-in user
        session()->set([
            'loggedIn'    => true,
            'firebaseKey' => $user['firebaseKey'],
            'user_level'  => $user['user_level'],
            'username'    => $user['username'],
            'first_name'  => $user['first_name'],
            'last_name'   => $user['last_name'],
        ]);
    
        // Redirect based on user level
        switch ($user['user_level']) {
            case 'admin':
                return redirect()->to('admin/dashboard');
            case 'operations coordinator':
                return redirect()->to('/operations/dashboard');
            case 'resource manager':
                return redirect()->to('/resource/dashboard');
            case 'client':
                return redirect()->to('client/dashboard');
            default:
                return redirect()->back()->with('error', 'User role not recognized.');
        }
    }
    
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
