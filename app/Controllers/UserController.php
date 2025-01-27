<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class UserController extends Controller
{
    public function user()
    {
        $model = new UserModel();
        $data['users'] = $model->findAll();
        return view('user_account', $data);
    }

    public function getUserDetails($id)
    {
        $model = new UserModel();
        $user = $model->find($id);
        return $this->response->setJSON($user);
    }
}
