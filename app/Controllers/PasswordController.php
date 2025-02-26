<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;

class PasswordController extends Controller
{
    /**
     * Display the forgot password form.
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * Process the forgot password form submission.
     * Generate a reset token and send an email with reset instructions.
     */
    public function sendResetLink()
    {
        $email = $this->request->getPost('email');
        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Please provide a valid email address.');
        }

        $userModel = new UserModel();
        $user = $userModel->getUserByField('email', $email);
        if (!$user) {
            // For security, you might not want to reveal if an email exists.
            return redirect()->back()->with('error', 'If the email exists, you will receive instructions.');
        }

        // Generate a unique reset token and set expiration (e.g. 1 hour from now)
        $token = bin2hex(random_bytes(16));
        $expiration = time() + 3600; // 1 hour

        if (! $userModel->setResetToken($email, $token, $expiration)) {
            return redirect()->back()->with('error', 'Unable to process your request.');
        }

        // Prepare reset link (adjust the URL as needed)
        $resetLink = base_url("password/reset/{$token}");

        // Send email with instructions via Gmail SMTP
        $emailService = \Config\Services::email();
        $emailService->setFrom('yourgmail@gmail.com', 'Gistika');
        $emailService->setTo($email);
        $emailService->setSubject('Password Reset Instructions');
        $message = "You have requested a password reset. Please click the link below to reset your password:<br><br>";
        $message .= "<a href='{$resetLink}'>Reset Password</a><br><br>";
        $message .= "This link will expire in 1 hour.";
        $emailService->setMessage($message);

        if (! $emailService->send()) {
            return redirect()->back()->with('error', 'Failed to send reset instructions.');
        }

        return redirect()->back()->with('success', 'Reset instructions have been sent to your email.');
    }

    /**
     * Display the reset password form.
     * The token is passed via the URL.
     */
    public function resetPassword($token)
    {
        $userModel = new UserModel();
        $user = $userModel->verifyResetToken($token);
        if (!$user) {
            return redirect()->to('password/forgot')->with('error', 'Invalid or expired token.');
        }
        return view('auth/reset_password', ['token' => $token]);
    }

    /**
     * Process the reset password form submission.
     */
    public function updatePassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirmPassword = $this->request->getPost('confirm_password');

        // Basic validation
        if (empty($password) || empty($confirmPassword)) {
            return redirect()->back()->with('error', 'Please fill all fields.');
        }
        if ($password !== $confirmPassword) {
            return redirect()->back()->with('error', 'Passwords do not match.');
        }
        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'Password must be at least 8 characters.');
        }

        $userModel = new UserModel();
        $user = $userModel->verifyResetToken($token);
        if (!$user) {
            return redirect()->to('password/forgot')->with('error', 'Invalid or expired token.');
        }

        // Update password using the user's Firebase key
        $firebaseKey = $user['firebaseKey'];
        $userModel->updatePassword($firebaseKey, $password);

        return redirect()->to('login')->with('success', 'Password has been reset successfully.');
    }
}
