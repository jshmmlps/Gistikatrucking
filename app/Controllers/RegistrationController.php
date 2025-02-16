<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class RegistrationController extends Controller
{
    /**
     * Displays the create account form.
     */
    public function createForm()
    {
        return view('auth/create_account', [
            'errors' => session()->getFlashdata('errors'),
        ]);
    }

    /**
     * Processes the account creation.
     * Includes form validation for uniqueness and password strength.
     */
    public function createAccount()
    {
        // 1) Define CodeIgniter validation rules
        $rules = [
            'first_name'      => 'required',
            'last_name'       => 'required',
            'email' => [
                'label' => 'Email',
                'rules' => 'required|valid_email|is_unique_in_firebase[Users.email]',
                'errors' => [
                    'is_unique_in_firebase' => 'Email is already registered. Please use another one.',
                ],
            ],
            'username' => [
                'label' => 'Username',
                'rules' => 'required|min_length[4]|is_unique_in_firebase[Users.username]',
                'errors' => [
                    'is_unique_in_firebase' => 'Username exists already. Create a unique one.',
                ],
            ],
            'password'        => [
                'label' => 'Password',
                'rules' => 'required|check_password_strength',
                'errors' => [
                    'check_password_strength' =>
                        'Password must be at least 8 characters, contain an uppercase, '
                        . 'a lowercase, a number, and a symbol.',
                ],
            ],
        ];

        // 2) Run validation
        if (! $this->validate($rules)) {
            // Validation failed - redirect back with errors and old input
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 3) If validation passes, collect form data
        $data = [
            'first_name'      => $this->request->getPost('first_name'),
            'last_name'       => $this->request->getPost('last_name'),
            'email'           => $this->request->getPost('email'),
            'username'        => $this->request->getPost('username'),
            'contact_number'  => $this->request->getPost('contact_number'),
            'address'         => $this->request->getPost('address'),
            'address_dropoff' => $this->request->getPost('address_dropoff'),
            'birthday'        => $this->request->getPost('birthday'),
            'gender'          => $this->request->getPost('gender'),
            'user_level'      => $this->request->getPost('user_level'),
        ];


        // 4) Hash the password
        $plainPassword = $this->request->getPost('password');
        $data['password'] = password_hash($plainPassword, PASSWORD_BCRYPT);

        // 5) Create user in Firebase via the model (with auto-increment ID)
        $userModel = new UserModel();
        $newKey = $userModel->createUser($data);

        // 6) Redirect with success message
        return redirect()
            ->back()
            ->with('success', 'User account created with key: ' . $newKey);
    }
}
