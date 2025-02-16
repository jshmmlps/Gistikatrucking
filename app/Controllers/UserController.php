<?php

namespace App\Controllers;

use App\Models\UserModels;
use CodeIgniter\Controller;

class UserController extends Controller
{
    public function user()
    {
        $model = new UserModels();
        $data['users'] = $model->findAll();
        return view('user_account', $data);
    }

    public function getUserDetails($id)
    {
        $model = new UserModels();
        $user = $model->find($id);
        return $this->response->setJSON($user);
    }
}
