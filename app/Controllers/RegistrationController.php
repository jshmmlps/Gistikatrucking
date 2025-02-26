<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class RegistrationController extends Controller
{
    /**
     * Display the registration form.
     */
    public function createForm()
    {
        return view('auth/create_account', [
            'errors' => session()->getFlashdata('errors'),
        ]);
    }

    /**
     * Process the registration form.
     * Validate inputs, generate OTP, send email and store data temporarily.
     */
    public function createAccount()
    {
        // 1) Define validation rules
        $rules = [
            'first_name' => 'required',
            'last_name'  => 'required',
            'email'      => [
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
            'password' => [
                'label' => 'Password',
                'rules' => 'required|check_password_strength',
                'errors' => [
                    'check_password_strength' =>
                        'Password must be at least 8 characters, contain an uppercase, '
                        . 'a lowercase, a number, and a symbol.',
                ],
            ],
            'confirm_password' => [
                'label' => 'Confirm Password',
                'rules' => 'required|matches[password]',
                'errors' => [
                    'matches' => 'Passwords do not match.',
                ],
            ],
        ];

        // 2) Run validation
        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // 3) Collect and prepare user data
        $data = [
            'first_name'     => $this->request->getPost('first_name'),
            'last_name'      => $this->request->getPost('last_name'),
            'email'          => $this->request->getPost('email'),
            'username'       => $this->request->getPost('username'),
            'contact_number' => $this->request->getPost('contact_number'),
            'address'        => $this->request->getPost('address'),
            'birthday'       => $this->request->getPost('birthday'),
            'gender'         => $this->request->getPost('gender'),
            // Default values:
            'user_level'     => 'customer',
            'address_dropoff'=> '',
        ];

        // 4) Hash the password
        $plainPassword       = $this->request->getPost('password');
        $data['password']    = password_hash($plainPassword, PASSWORD_BCRYPT);

        // 5) Generate OTP and set expiration (5 minutes from now)
        $otp = random_int(100000, 999999);
        $otpExpiration = time() + 300; // 5 minutes in seconds

        // Store the time the OTP was sent (for resend cooldown)
        $otpLastSent = time();

        // 6) Save pending registration data in session
        session()->set('pending_registration', [
            'data'           => $data,
            'otp'            => $otp,
            'otp_expiration' => $otpExpiration,
            'otp_last_sent'  => $otpLastSent
        ]);

        // 7) Send OTP email via Gmail SMTP
        $emailService = \Config\Services::email();
        $emailService->setFrom('yourgmail@gmail.com', 'Gistika');
        $emailService->setTo($data['email']);
        $emailService->setSubject('OTP Verification');
        $emailService->setMessage("Your OTP is: <strong>$otp</strong>. It will expire in 5 minutes.");

        if (! $emailService->send()) {
            return redirect()->to('register')->with('error', 'Failed to send OTP email.');
        }

        // 8) Redirect to the OTP verification page (GET route)
        return redirect()->to('register/verifyOTP');

    }

    /**
     * Process OTP verification.
     * If OTP is valid and not expired, register the user in Firebase.
     */
    public function verifyOTP()
    {
        $inputOTP = $this->request->getPost('otp');
        $pending  = session()->get('pending_registration');

        if (!$pending) {
            return redirect()->to('register/createForm')->with('error', 'No pending registration found.');
        }

        // Check if the OTP has expired
        if (time() > $pending['otp_expiration']) {
            session()->remove('pending_registration');
            return redirect()->to('register/createForm')->with('error', 'OTP expired. Please register again.');
        }

        // Validate OTP
        if ($inputOTP != $pending['otp']) {
            return redirect()->back()->with('error', 'Invalid OTP. Please try again.');
        }

        // OTP is valid; create user in Firebase
        $userModel = new UserModel();
        $newKey = $userModel->createUser($pending['data']);

        // Clear pending registration session data
        session()->remove('pending_registration');

        return redirect()->to('login')->with('success', "User account created successfully");
    }

    /**
     * Resend OTP email.
     * This function checks if at least 1 minute has passed since the last send.
     */
    public function resendOTP()
    {
        $pending = session()->get('pending_registration');

        if (!$pending) {
            return redirect()->to('register')->with('error', 'No pending registration found.');
        }

        // Check if 1 minute has passed since the last OTP was sent
        if (time() - $pending['otp_last_sent'] < 60) {
            return redirect()->to('register/verifyOTP')->with('error', 'Please wait 1 minute before resending OTP.');
        }

        // Generate a new OTP and update expiration and last sent time
        $otp = random_int(100000, 999999);
        $pending['otp'] = $otp;
        $pending['otp_expiration'] = time() + 300; // 5 minutes from now
        $pending['otp_last_sent']  = time();
        session()->set('pending_registration', $pending);

        // Resend OTP email
        $emailService = \Config\Services::email();
        $emailService->setFrom('yourgmail@gmail.com', 'Gistika Trucking');
        $emailService->setTo($pending['data']['email']);
        $emailService->setSubject('OTP Verification - Resend');
        $emailService->setMessage("Your new OTP is: <strong>$otp</strong>. It will expire in 5 minutes.");

        if (! $emailService->send()) {
            return redirect()->to('register/verifyOTP')->with('error', 'Failed to resend OTP email.');
        }

        return redirect()->to('register/verifyOTP')->with('success', 'OTP resent successfully.');
    }

    public function showOTPForm()
    {
        $pending = session()->get('pending_registration');
        if (!$pending) {
            return redirect()->to('register')->with('error', 'No pending registration found.');
        }
        return view('auth/verify_otp', ['email' => $pending['data']['email']]);
    }

}
