<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class AdminAuthController extends Controller
{
    public function login()
    {
        //This is a test
        // Show admin login form
        return view('auth/admin_login');
    }

    public function processLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->verifyCredentials($username, $password);

        if (!$user || $user['user_level'] !== 'admin') {
            return redirect()
                ->back()
                ->with('error', 'Invalid credentials or you are not an admin.');
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

        // redirect to admin dashboard or admin home
        return redirect()->to('/admin/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
