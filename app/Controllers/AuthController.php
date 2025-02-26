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
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->verifyCredentials($username, $password);

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
                return redirect()->to('/admin/dashboard');
            case 'staff_op':
                return redirect()->to('/staff_operation/dashboard');
            case 'staff_res':
                return redirect()->to('/staff_resource/dashboard');
            case 'customer':
                return redirect()->to('/dashboard');
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
