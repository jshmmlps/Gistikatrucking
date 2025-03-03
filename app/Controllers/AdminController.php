<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
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


    // ============== DRIVER MANAGEMENT MODULE ===================  //

   /**
     * Display the Driver/Conductor management page.
     * - Fetch all driver records.
     * - Fetch eligible users (with user_level "driver" or "conductor") not already assigned.
     * - Compute available trucks separately for drivers and conductors.
     */
    public function driverManagement()
    {
        $driverModel = new DriverModel();
        $data['drivers'] = $driverModel->getDrivers();

        // Get eligible users from the Users collection.
        $userModel = new UserModel();
        $allEligibleUsers = $userModel->getEligibleUsers();
        
        // Remove users already assigned in the Drivers collection (assuming driver record stores 'user_id').
        if (!empty($data['drivers'])) {
            foreach ($data['drivers'] as $driver) {
                if (isset($driver['user_id'])) {
                    unset($allEligibleUsers[$driver['user_id']]);
                }
            }
        }
        $data['eligibleUsers'] = $allEligibleUsers;

        // Get all trucks from the Trucks collection.
        $truckModel = new TruckModel();
        $allTrucks = $truckModel->getTrucks();

        // Initialize available trucks arrays for driver and conductor.
        $availableTrucksForDriver = $allTrucks;
        $availableTrucksForConductor = $allTrucks;

        if ($data['drivers']) {
            foreach ($data['drivers'] as $driver) {
                if (isset($driver['truck_assigned']) && !empty($driver['truck_assigned'])) {
                    if (isset($driver['position'])) {
                        $position = strtolower($driver['position']);
                        if ($position === 'driver') {
                            $truckId = $driver['truck_assigned'];
                            unset($availableTrucksForDriver[$truckId]);
                        } elseif ($position === 'conductor') {
                            $truckId = $driver['truck_assigned'];
                            unset($availableTrucksForConductor[$truckId]);
                        }
                    }
                }
            }
        }
        $data['availableTrucksForDriver'] = $availableTrucksForDriver;
        $data['availableTrucksForConductor'] = $availableTrucksForConductor;

        return view('admin/driver_management', $data);
    }
    
    /**
     * Create a new Driver/Conductor record.
     * Validations:
     * - All fields are required except Medical Record.
     * - Date of Employment cannot exceed today's date.
     * - License Expiry must be today or later.
     * - Trips Completed cannot be less than 0.
     * - Employee ID and License Number must be unique.
     * - The selected truck must not be already assigned for the same role.
     */
    public function createDriver()
    {
        if ($this->request->getMethod() == 'POST') {
            // Retrieve POST data
            $user_id            = $this->request->getPost('user_id'); // Selected eligible user
            $employee_id        = $this->request->getPost('employee_id');
            $date_of_employment = $this->request->getPost('date_of_employment');
            $truck_assigned     = $this->request->getPost('truck_assigned');
            $license_number     = $this->request->getPost('license_number');
            $license_expiry     = $this->request->getPost('license_expiry');
            $medical_record     = $this->request->getPost('medical_record'); // Optional
            $trips_completed    = $this->request->getPost('trips_completed');

            // Validate that all required fields are provided (all except medical_record)
            if (empty($user_id) || empty($employee_id) || empty($date_of_employment) || empty($truck_assigned) || empty($license_number) || empty($license_expiry) || $trips_completed === '' || $trips_completed === null) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'All fields except Medical Record are required.');
            }

            $today = date('Y-m-d');
            // Validate date_of_employment should not exceed today
            if ($date_of_employment > $today) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'Date of Employment cannot exceed today.');
            }
            // Validate license_expiry should be today or in the future
            if ($license_expiry < $today) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'License Expiry must be today or later.');
            }
            // Validate trips_completed is not less than 0
            if ($trips_completed < 0) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'Trips Completed cannot be less than 0.');
            }

            // Retrieve the user details from the Users collection.
            $userModel = new UserModel();
            $user = $userModel->getUser($user_id);
            if (!$user) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'User not found.');
            }
            
            // Determine the user's position (driver or conductor)
            $user_position = strtolower($user['user_level']);

            // Initialize DriverModel and fetch existing driver records.
            $driverModel = new DriverModel();
            $existingDrivers = $driverModel->getDrivers();
            if ($existingDrivers) {
                foreach ($existingDrivers as $driver) {
                    if (isset($driver['employee_id']) && $driver['employee_id'] === $employee_id) {
                        return redirect()->to(base_url('admin/driver'))->with('error', 'Employee ID already exists.');
                    }
                    if (isset($driver['license_number']) && $driver['license_number'] === $license_number) {
                        return redirect()->to(base_url('admin/driver'))->with('error', 'License Number already exists.');
                    }
                    if (
                        isset($driver['truck_assigned']) && 
                        $driver['truck_assigned'] === $truck_assigned && 
                        isset($driver['position']) && strtolower($driver['position']) === $user_position
                    ) {
                        return redirect()->to(base_url('admin/driver'))->with('error', 'This truck is already assigned to a ' . ucfirst($user_position) . '.');
                    }
                }
            }

            // Merge user details with additional form data.
            $data = [
                'user_id'            => $user_id,
                'first_name'         => $user['first_name'],
                'last_name'          => $user['last_name'],
                'contact_number'     => $user['contact_number'],
                'position'           => $user['user_level'], // Using user_level as position
                'home_address'       => $user['address'],
                'birthday'           => $user['birthday'],
                // Additional fields
                'employee_id'        => $employee_id,
                'date_of_employment' => $date_of_employment,
                'truck_assigned'     => $truck_assigned,
                'license_number'     => $license_number,
                'license_expiry'     => $license_expiry,
                'medical_record'     => $medical_record,
                'trips_completed'    => $trips_completed,
            ];

            $newDriverId = $driverModel->insertDriver($data);
            return redirect()->to(base_url('admin/driver'))
                             ->with('success', 'Driver/Conductor created successfully with ID: ' . $newDriverId);
        }
        return redirect()->to(base_url('admin/driver'))
                         ->with('error', 'Invalid request.');
    }
    
    /**
     * Update an existing Driver/Conductor record.
     * Basic validations are applied for required fields and date/trip constraints.
     */
    public function updateDriver($driverId)
    {
        if ($this->request->getMethod() == 'POST') {
            $data = [
                'employee_id'        => $this->request->getPost('employee_id'),
                'date_of_employment' => $this->request->getPost('date_of_employment'),
                'truck_assigned'     => $this->request->getPost('truck_assigned'),
                'license_number'     => $this->request->getPost('license_number'),
                'license_expiry'     => $this->request->getPost('license_expiry'),
                'medical_record'     => $this->request->getPost('medical_record'),
                'trips_completed'    => $this->request->getPost('trips_completed'),
            ];

            // Validate required fields
            if (empty($data['employee_id']) || empty($data['truck_assigned']) || empty($data['license_number']) || empty($data['license_expiry']) || $data['trips_completed'] === '' || $data['trips_completed'] === null) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'All required fields must be provided.');
            }

            $today = date('Y-m-d');
            if ($data['date_of_employment'] > $today) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'Date of Employment cannot exceed today.');
            }
            if ($data['license_expiry'] < $today) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'License Expiry must be today or later.');
            }
            if ($data['trips_completed'] < 0) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'Trips Completed cannot be less than 0.');
            }

            $driverModel = new DriverModel();
            $driverModel->updateDriver($driverId, $data);
            return redirect()->to(base_url('admin/driver'))
                             ->with('success', 'Driver/Conductor updated successfully.');
        }
        return redirect()->to(base_url('admin/driver'))
                         ->with('error', 'Invalid request.');
    }
    
    /**
     * Delete a Driver/Conductor record.
     */
    public function deleteDriver($driverId)
    {
        $driverModel = new DriverModel();
        $driverModel->deleteDriver($driverId);
        return redirect()->to(base_url('admin/driver'))
                         ->with('success', 'Driver/Conductor deleted successfully.');
    }
    
    /**
     * View details of a specific Driver/Conductor.
     */
    public function viewDriver($driverId)
    {
        $driverModel = new DriverModel();
        $data['driver'] = $driverModel->getDriver($driverId);
        return view('admin/driver_detail', $data);
    }

}
