<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use CodeIgniter\Controller;

class AdminController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        // Load your session and check admin auth
        $session = session();
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'admin') {
            $session->setFlashdata('error', 'No authorization.');
            redirect()->to(base_url('login'))->send();
            exit; // Stop further execution
        }        
        
        $this->userModel = new UserModel();
    }

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        return view('admin/dashboard');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }


    /**
     * Display the admin profile page.
     */
    public function profile()
    {
        $session = session();
        $firebaseKey = $session->get('firebaseKey');
        $userData = $this->userModel->getUser($firebaseKey);
        return view('admin/users/profile', ['user' => $userData]);
    }

    /**
     * Process the update of profile details.
     */
    public function updateProfile()
    {
        $session = session();
        $firebaseKey = $session->get('firebaseKey');

        $data = [
            'first_name'     => $this->request->getPost('first_name'),
            'last_name'      => $this->request->getPost('last_name'),
            'email'          => $this->request->getPost('email'),
            'username'       => $this->request->getPost('username'),
            'contact_number' => $this->request->getPost('contact_number'),
            'address'        => $this->request->getPost('address'),
            'birthday'       => $this->request->getPost('birthday'),
            'gender'         => $this->request->getPost('gender'),
        ];

        $this->userModel->updateUser($firebaseKey, $data);
        $session->setFlashdata('success', 'Profile updated successfully.');
        return redirect()->to('admin/profile');
    }

    /**
     * Display the user management page.
     */
    public function users()
    {
        $users = $this->userModel->getAllUsers();
        
        // $users is an associative array: [ 'User1' => [...], 'User2' => [...], ... ]
        // We'll pass it to the view
        return view('admin/users/manage', [
            'users' => $users,
        ]);
    }

    /**
     * Create user (Process the POST request from the modal).
     */
    public function create()
    {
        if ($this->request->getMethod() === 'POST') {
            
            // Capture the password fields
            $plainPassword   = $this->request->getPost('password');
            $confirmPassword = $this->request->getPost('confirm_password');

            // Check if they match
            if ($plainPassword !== $confirmPassword) {
                session()->setFlashdata('error', 'Passwords do not match!');
                return redirect()->back(); // or redirect to the same page
            }

            // Prepare the data
            $data = [
                'first_name'      => $this->request->getPost('first_name'),
                'last_name'       => $this->request->getPost('last_name'),
                'email'           => $this->request->getPost('email'),
                'username'        => $this->request->getPost('username'),
                'contact_number'  => $this->request->getPost('contact_number'),
                'address'         => $this->request->getPost('address'),
                'birthday'        => $this->request->getPost('birthday'),
                'gender'          => $this->request->getPost('gender'),
                'user_level'      => $this->request->getPost('user_level'),
                'address_dropoff' => $this->request->getPost('address_dropoff'),
                // Hash the password before storing
                'password'        => password_hash($plainPassword, PASSWORD_BCRYPT),
            ];

            // Call the model to create user
            $this->userModel->createUser($data);

            // Redirect back
            session()->setFlashdata('message', 'User created successfully!');
            return redirect()->to(base_url('admin/users'));
        }

        // If not a POST request, just redirect
        return redirect()->to(base_url('admin/users'));
    }


    /**
     * Edit user (Process the POST request from the modal).
     */
    public function edit($userKey)
    {
        if ($this->request->getMethod() === 'POST') {
            $data = [
                'first_name'      => $this->request->getPost('first_name'),
                'last_name'       => $this->request->getPost('last_name'),
                'email'           => $this->request->getPost('email'),
                'username'        => $this->request->getPost('username'),
                'contact_number'  => $this->request->getPost('contact_number'),
                'address'         => $this->request->getPost('address'),
                'birthday'        => $this->request->getPost('birthday'),
                'gender'          => $this->request->getPost('gender'),
                'user_level'      => $this->request->getPost('user_level'),
                'address_dropoff' => $this->request->getPost('address_dropoff'),
            ];

            // Update user
            $this->userModel->updateUser($userKey, $data);

            session()->setFlashdata('message', 'User updated successfully!');
            return redirect()->to(base_url('admin/users'));
        }

        // If GET, you might load the user and show an edit page or return 404
        return redirect()->to(base_url('admin/users'));
    }

    /**
     * Delete user (Process the POST request from the modal).
     */
    public function delete($userKey)
    {
        if ($this->request->getMethod() === 'POST') {
            $this->userModel->deleteUser($userKey);
            session()->setFlashdata('message', 'User deleted successfully!');
        }

        return redirect()->to(base_url('admin/users'));
    }

    // public function index()
    // {
    //     echo "Hello Admin";
    // }

    // ============== TRUCK MANAGEMENT MODULE ===================  //

    // List all trucks
    public function truck()
    {
        $truckModel = new TruckModel();
        $data['trucks'] = $truckModel->getTrucks();
        return view('admin/truck_management', $data);
    }
    
    // Create a new truck (process create form submission)
    public function storeTruck()
    {
        $data = [
            'truck_model'            => $this->request->getPost('truck_model'),
            'plate_number'           => $this->request->getPost('plate_number'),
            'engine_number'          => $this->request->getPost('engine_number'),
            'chassis_number'         => $this->request->getPost('chassis_number'),
            'color'                  => $this->request->getPost('color'),
            'cor_number'             => $this->request->getPost('cor_number'),
            'insurance_details'      => $this->request->getPost('insurance_details'),
            'license_plate_expiry'   => $this->request->getPost('license_plate_expiry'),
            'registration_expiry'    => $this->request->getPost('registration_expiry'),
            'truck_type'             => $this->request->getPost('truck_type'),
            'fuel_type'              => $this->request->getPost('fuel_type'),
            'truck_length'           => $this->request->getPost('truck_length'),
            'load_capacity'          => $this->request->getPost('load_capacity'),
            'maintenance_technician' => $this->request->getPost('maintenance_technician'),
        ];
        
        $truckModel = new TruckModel();
        $newTruckId = $truckModel->insertTruck($data);
        session()->setFlashdata('success', 'Truck created successfully with ID: ' . $newTruckId);
        return redirect()->to(base_url('admin/trucks'));
    }
    
    // Update an existing truck
    public function updateTruck($truckId)
    {
        $data = [
            'truck_model'            => $this->request->getPost('truck_model'),
            'plate_number'           => $this->request->getPost('plate_number'),
            'engine_number'          => $this->request->getPost('engine_number'),
            'chassis_number'         => $this->request->getPost('chassis_number'),
            'color'                  => $this->request->getPost('color'),
            'cor_number'             => $this->request->getPost('cor_number'),
            'insurance_details'      => $this->request->getPost('insurance_details'),
            'license_plate_expiry'   => $this->request->getPost('license_plate_expiry'),
            'registration_expiry'    => $this->request->getPost('registration_expiry'),
            'truck_type'             => $this->request->getPost('truck_type'),
            'fuel_type'              => $this->request->getPost('fuel_type'),
            'truck_length'           => $this->request->getPost('truck_length'),
            'load_capacity'          => $this->request->getPost('load_capacity'),
            'maintenance_technician' => $this->request->getPost('maintenance_technician'),
        ];
        
        $truckModel = new TruckModel();
        $truckModel->updateTruck($truckId, $data);
        session()->setFlashdata('success', 'Truck updated successfully.');
        return redirect()->to(base_url('admin/trucks'));
    }
    
    // Delete a truck
    public function deleteTruck($truckId)
    {
        $truckModel = new TruckModel();
        $truckModel->deleteTruck($truckId);
        session()->setFlashdata('success', 'Truck deleted successfully.');
        return redirect()->to(base_url('admin/trucks'));
    }
    
    // View a truck's details (could be loaded into a modal via AJAX or as a partial view)
    public function viewTruck($truckId)
    {
        $truckModel = new TruckModel();
        $data['truck'] = $truckModel->getTruck($truckId);
        return view('admin/truck_detail', $data);
    }
}
