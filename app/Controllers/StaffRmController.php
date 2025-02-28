<?php

namespace App\Controllers;

use App\Models\UserModel; // Assuming you have a UserModel for fetching user details
use CodeIgniter\Controller;

class StaffRmController extends Controller
{
    public function dashboard()
    {
        return view('resource_manager/dashboard');
    }

    public function userAccount()
    {
        // Load user data from session (or database)
        $data['user'] = session()->get('user'); // Assuming session has user info

        return view('resource_manager/user_account', $data);
    }

    public function uploadProfile()
    {
        if ($this->request->getMethod() == 'post') {
            $file = $this->request->getFile('profile_picture');

            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/profile_pictures', $newName);

                // Update user profile picture in the database
                $userId = session()->get('user_id'); // Get user ID from session
                $userModel = new UserModel();
                $userModel->update($userId, ['profile_picture' => $newName]);

                return redirect()->to(base_url('resource/user_account'))->with('success', 'Profile updated successfully.');
            }
        }

        return redirect()->to(base_url('resource/user_account'))->with('error', 'Failed to upload.');
    }
}
