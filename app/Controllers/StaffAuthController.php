<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class StaffAuthController extends Controller
{
    public function login()
    {
        // Show staff login form
        return view('auth/staff_login');
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

        // Check if user is staff_op or staff_res
        if ($user['user_level'] !== 'staff_op' && $user['user_level'] !== 'staff_res') {
            return redirect()->back()->with('error', 'You are not authorized as staff.');
        }

        // set session
        session()->set([
            'loggedIn'     => true,
            'firebaseKey'  => $user['firebaseKey'],
            'user_level'   => $user['user_level'],
            'username'     => $user['username'],
            'first_name'   => $user['first_name'],
            'last_name'    => $user['last_name'],
        ]);

        // Could redirect to different dashboards based on sub-role
        if ($user['user_level'] === 'staff_op') {
            return redirect()->to('/staff/operation-dashboard');
        } else {
            return redirect()->to('/staff/resource-dashboard');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/staff/login');
    }
}
