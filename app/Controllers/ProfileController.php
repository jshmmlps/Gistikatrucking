<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function profile()
    {
        $userModel = new UserModel();
        $user = $userModel->where('user_id', '202110719')->first();

        return view('profile', ['user' => $user]);
    }

    public function update()
    {
        $userModel = new UserModel();
        $data = $this->request->getPost();
        $userModel->update($data['id'], $data);

        return redirect()->to('/profile');
    }
}
