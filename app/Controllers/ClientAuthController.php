<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class ClientAuthController extends Controller
{
    public function login()
    {
        // Show client login form
        return view('auth/client_login');
    }

    public function processLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $user = $userModel->verifyCredentials($username, $password);

        if (!$user || $user['user_level'] !== 'customer') {
            return redirect()->back()->with('error', 'Invalid credentials or not a customer account.');
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

        return redirect()->to('/customer/home'); // or any customer page
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/client/login');
    }
}
