<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use App\Models\BookingModel;
use App\Models\ClientManagementModel;
use CodeIgniter\Controller;
use DateTime;
use DateInterval;

class AdminController extends Controller
{
    protected $userModel;
    protected $clientManagementModel;

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
        $this->driverModel = new DriverModel();
        // Initialize the model
        $this->clientManagementModel = new ClientManagementModel();
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
    
        // Fetch user data from the UserModel (including the profile_picture field)
        $userData = $this->userModel->getUser($firebaseKey);
    
        // If the user exists, pass the data to the view
        if ($userData) {
            // Check if profile_picture exists or use default
            if (empty($userData['profile_picture'])) {
                $userData['profile_picture'] = base_url('public/images/default.jpg');
            }
    
            return view('admin/users/profile', ['user' => $userData]);
        }
    
        // Optionally handle the case where no user is found (e.g. redirect or show error)
        $session->setFlashdata('error', 'User not found');
        return redirect()->to('/admin');
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
     * Process the upload of a new profile picture.
     */
    public function uploadProfilePicture()
    {
        $session = session();
        $firebaseKey = $session->get('firebaseKey');

        // Retrieve the user data
        $user = $this->userModel->getUser($firebaseKey);
        if (!$user) {
            $session->setFlashdata('error', 'User not found.');
            return redirect()->to('admin/profile');
        }

        // Retrieve the uploaded file
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Build the file name (example: 7_john.jpg)
            $extension = $file->getClientExtension();
            $newName = $user['user_id'] . '_' . strtolower(str_replace(' ', '_', $user['username'])) . '.' . $extension;

            // Temporarily move the file
            $file->move(WRITEPATH . 'uploads/', $newName);
            $localPath = WRITEPATH . 'uploads/' . $newName;

            // Get Storage instance
            $storage = \Config\Services::firebaseStorage();
            // Retrieve your bucket name from .env (NO "gs://")
            $bucketName = env('FIREBASE_STORAGE_BUCKET'); 
            // Now get the actual bucket
            $bucket = $storage->getBucket($bucketName);

            // Upload the file to "profile_images/" folder in the bucket
            $object = $bucket->upload(
                fopen($localPath, 'r'),
                ['name' => 'profile_images/' . $newName]
            );

            // Construct the public URL
            $imageUrl = sprintf(
                'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                $bucket->name(),
                urlencode($object->name())
            );

            // Update the user's profile with the new image URL
            $this->userModel->updateUser($firebaseKey, ['profile_picture' => $imageUrl]);

            // Cleanup
            unlink($localPath);

            $session->setFlashdata('success', 'Profile picture updated successfully.');
            return redirect()->to('admin/profile');
        } else {
            $session->setFlashdata('error', 'Failed to upload image. Please try again.');
            return redirect()->to('admin/profile');
        }
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
        $trucks = $truckModel->getTrucks();

        // Re-index the array in case it is associative
        $trucks = array_values($trucks);
        
        // Sort the trucks naturally by truck_id (e.g., Truck1, Truck2, ..., Truck10)
        usort($trucks, function($a, $b) {
            return strnatcmp($a['truck_id'], $b['truck_id']);
        });
        
        $data['trucks'] = $trucks;
        return view('admin/truck_management', $data);
    }

    // Create a new truck (process create form submission)
    public function storeTruck()
    {
        $data = [
            'truck_model'             => $this->request->getPost('truck_model'),
            'plate_number'            => $this->request->getPost('plate_number'),
            'engine_number'           => $this->request->getPost('engine_number'),
            'chassis_number'          => $this->request->getPost('chassis_number'),
            'color'                   => $this->request->getPost('color'),
            'cor_number'              => $this->request->getPost('cor_number'),
            'insurance_details'       => $this->request->getPost('insurance_details'),
            'license_plate_expiry'    => $this->request->getPost('license_plate_expiry'),
            'registration_expiry'     => $this->request->getPost('registration_expiry'),
            'truck_type'              => $this->request->getPost('truck_type'),
            'fuel_type'               => $this->request->getPost('fuel_type'),
            'truck_length'            => $this->request->getPost('truck_length'),
            'load_capacity'           => $this->request->getPost('load_capacity'),
            'maintenance_technician'  => $this->request->getPost('maintenance_technician'),
            'last_inspection_date'    => $this->request->getPost('last_inspection_date'),
            'last_inspection_mileage' => $this->request->getPost('last_inspection_mileage'),
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
            'truck_model'             => $this->request->getPost('truck_model'),
            'plate_number'            => $this->request->getPost('plate_number'),
            'engine_number'           => $this->request->getPost('engine_number'),
            'chassis_number'          => $this->request->getPost('chassis_number'),
            'color'                   => $this->request->getPost('color'),
            'cor_number'              => $this->request->getPost('cor_number'),
            'insurance_details'       => $this->request->getPost('insurance_details'),
            'license_plate_expiry'    => $this->request->getPost('license_plate_expiry'),
            'registration_expiry'     => $this->request->getPost('registration_expiry'),
            'truck_type'              => $this->request->getPost('truck_type'),
            'fuel_type'               => $this->request->getPost('fuel_type'),
            'truck_length'            => $this->request->getPost('truck_length'),
            'load_capacity'           => $this->request->getPost('load_capacity'),
            'maintenance_technician'  => $this->request->getPost('maintenance_technician'),
            'last_inspection_date'    => $this->request->getPost('last_inspection_date'),
            'last_inspection_mileage' => $this->request->getPost('last_inspection_mileage'),
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

    // View a truck's details
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
     * Validations are applied to required fields, dates, trips count, and uniqueness constraints.
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

            // Validate that all required fields are provided (except medical_record)
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

            // Driver ID is auto-generated in the DriverModel to be unique.
            $newDriverId = $driverModel->insertDriver($data);
            return redirect()->to(base_url('admin/driver'))
                             ->with('success', 'Driver/Conductor created successfully with ID: ' . $newDriverId);
        }
        return redirect()->to(base_url('admin/driver'))
                         ->with('error', 'Invalid request.');
    }
    
    /**
     * Update an existing Driver/Conductor record.
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

            // Fetch all drivers and enforce uniqueness for employee_id and license_number
            $driverModel = new DriverModel();
            $existingDrivers = $driverModel->getDrivers();
            if ($existingDrivers) {
                foreach ($existingDrivers as $id => $driver) {
                    // Skip the driver being updated.
                    if ($id == $driverId) {
                        continue;
                    }
                    if (isset($driver['employee_id']) && $driver['employee_id'] === $data['employee_id']) {
                        return redirect()->to(base_url('admin/driver'))->with('error', 'Employee ID already exists.');
                    }
                    if (isset($driver['license_number']) && $driver['license_number'] === $data['license_number']) {
                        return redirect()->to(base_url('admin/driver'))->with('error', 'License Number already exists.');
                    }
                }
            }

            // If no uniqueness conflicts, perform the update.
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


    // ============== BOOKING MODULE ===================  //
    // List all bookings for admin review
    public function bookings()
    {
        $bookingModel = new BookingModel();
        $data['bookings'] = $bookingModel->getAllBookings();
        return view('admin/bookings', $data);
    }

    // Update booking status (approval/rejection)
    public function updateBookingStatus()
        {
            $bookingId  = $this->request->getPost('booking_id');
            $status     = $this->request->getPost('status');     // e.g., "approved", "rejected", etc.
            $distance   = $this->request->getPost('distance');   // New distance value
            $driverId   = $this->request->getPost('driver');       // Selected driver id (if any)
            $conductorId= $this->request->getPost('conductor');    // Selected conductor id (if any)
            $truckId    = $this->request->getPost('truck_id');     // Hidden field updated via JS

            // Prepare the update data array
            $updateData = [
                'status'   => $status,
                'distance' => $distance
            ];
            
            // If a new driver is selected, lookup its full name from Firebase and update booking
            if (!empty($driverId)) {
                $firebase    = service('firebase');
                $driverData  = $firebase->getReference('Drivers/' . $driverId)->getValue();
                if ($driverData) {
                    $fullName = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                    $updateData['driver_name'] = $fullName;
                }
            }
            
            // Similarly for conductor
            if (!empty($conductorId)) {
                $firebase      = service('firebase');
                $conductorData = $firebase->getReference('Drivers/' . $conductorId)->getValue();
                if ($conductorData) {
                    $fullName = trim(($conductorData['first_name'] ?? '') . ' ' . ($conductorData['last_name'] ?? ''));
                    $updateData['conductor_name'] = $fullName;
                }
            }
            
            // If a new truck id is provided (via driver selection), update truck details
            if (!empty($truckId)) {
                $firebase  = service('firebase');
                $truckData = $firebase->getReference('Trucks/' . $truckId)->getValue();
                if ($truckData) {
                    $updateData['truck_id']       = $truckData['truck_id'] ?? $truckId;
                    $updateData['truck_model']    = $truckData['truck_model'] ?? '';
                    $updateData['license_plate']  = $truckData['plate_number'] ?? '';
                    $updateData['type_of_truck']  = $truckData['truck_type'] ?? '';
                }
            }

            $bookingModel = new BookingModel();
            $bookingModel->updateBooking($bookingId, $updateData);
            
            return redirect()->to(base_url('admin/bookings'));
        }


    // ============== CLIENT MANAGEMENT MODULE ===================  //
    /**
     * List all clients with key booking details.
     * Also gather detailed client data for use in the modals.
     */
    public function clientManagement()
    {
        $clients = $this->clientManagementModel->getAllClients();
        $dataClients = [];
        $clientDetails = []; // detailed info for modals

        if ($clients) {
            foreach ($clients as $clientId => $client) {
                $lastBooking = $this->clientManagementModel->getLastBooking($clientId);
                $dataClients[] = [
                    'clientId'         => $clientId,
                    'clientName'       => $client['first_name'] . ' ' . $client['last_name'],
                    'booking_date'     => $lastBooking ? $lastBooking['booking_date'] : 'N/A',
                    'dispatch_date'    => $lastBooking ? $lastBooking['dispatch_date'] : 'N/A',
                    'cargo_type'       => $lastBooking ? $lastBooking['cargo_type'] : 'N/A',
                    'drop_off_address' => $lastBooking ? $lastBooking['drop_off_address'] : 'N/A',
                    'status'           => $lastBooking ? $lastBooking['status'] : 'N/A',
                ];
                $clientDetails[$clientId] = [
                    'client'      => $client,
                    'lastBooking' => $lastBooking
                ];
            }
        }

        $data = [
            'clients'       => $dataClients,
            'clientDetails' => $clientDetails
        ];
        return view('admin/client_management', $data);
    }

    /**
     * Update client details (e.g. Business Type and Payment Mode) via AJAX.
     *
     * @param string $clientId The unique key of the client.
     */
    public function clientEdit($clientId)
    {
        if ($this->request->getMethod() === 'post') {
            $dataUpdate = [
                'business_type' => $this->request->getPost('business_type'),
                'payment_mode'  => $this->request->getPost('payment_mode'),
            ];
            $this->clientManagementModel->updateClient($clientId, $dataUpdate);
            session()->setFlashdata('success', 'Client details updated successfully.');
            return json_encode(['success' => true]);
        }
    }

    // ================== REPORT MANAGEMENT MODULE ===================  //
    
    public function Report()
    {
        // Here you can gather any reports data or analytics if needed.
        // For now, we simply load the view.
        return view('admin/reports_management');
    }


    // ================== GEOLOCATION MODULE ===================  //

    public function geolocation()
    {
        // Get only drivers with valid geolocation fields
        $drivers = $this->driverModel->getDriversWithLocation();

        return view('admin/geolocation', [
            'drivers' => $drivers
        ]);
    }

   // ================== MAINTENANCE MODULE ===================  //
    public function Maintenance()
    {
        // 1) Get Firebase Realtime Database instance
        $db = service('firebase');

        // 2) Fetch all trucks from your "Trucks" node
        $trucksRef = $db->getReference('Trucks');
        $snapshot = $trucksRef->getSnapshot();

        if (!$snapshot->exists()) {
            // If no data in 'Trucks' node, pass empty arrays
            return view('maintenance', [
                'totalTrucks'      => 0,
                'dueTrucks'        => [],
                'chartData'        => [],
                'availableTrucks'  => [],
            ]);
        }

        // Convert the snapshot into an associative array
        $trucksData = $snapshot->getValue();

        // Natural sort the trucks data by truck ID (keys)
        uksort($trucksData, 'strnatcmp');

        // 3) Determine which trucks are due for inspection
        // A truck is "due for inspection" if:
        // - Its last_inspection_date is older than 6 months, OR
        // - (currentMileage - lastInspectionMileage) >= 20,000
        $dueTrucks = [];
        $timeInterval = new \DateInterval('P6M'); // 6 months using global namespace
        $mileageThreshold = 20000;

        foreach ($trucksData as $truckId => $truck) {
            // Extract required fields
            $lastInspectionDate    = $truck['last_inspection_date']   ?? null;
            $lastInspectionMileage = $truck['last_inspection_mileage'] ?? 0;
            $currentMileage        = $truck['current_mileage']         ?? 0;

            // Time-based check
            $timeOverdue = false;
            if ($lastInspectionDate) {
                try {
                    $dateNow  = new \DateTime();
                    $dateLast = new \DateTime($lastInspectionDate);
                    $dateLast->add($timeInterval); // last inspection + 6 months
                    if ($dateNow > $dateLast) {
                        $timeOverdue = true;
                    }
                } catch (\Exception $e) {
                    // Optionally log or handle invalid date formats
                }
            }

            // Mileage-based check
            $mileageOverdue = false;
            if (($currentMileage - $lastInspectionMileage) >= $mileageThreshold) {
                $mileageOverdue = true;
            }

            // If either condition is met, mark truck as due for inspection
            if ($timeOverdue || $mileageOverdue) {
                $dueTrucks[] = [
                    'truckId' => $truckId,
                    'details' => $truck,
                ];
            }
        }

        // Sort the due trucks naturally by truckId
        usort($dueTrucks, function($a, $b) {
            return strnatcmp($a['truckId'], $b['truckId']);
        });

        // Filter out trucks that are due for inspection to create the available trucks list
        $dueTruckIds = array_map(function ($dueTruck) {
            return $dueTruck['truckId'];
        }, $dueTrucks);
        
        $availableTrucks = [];
        foreach ($trucksData as $truckId => $truck) {
            if (!in_array($truckId, $dueTruckIds)) {
                $availableTrucks[$truckId] = $truck;
            }
        }
        
        // Natural sort the available trucks by truckId
        uksort($availableTrucks, 'strnatcmp');

        // Prepare summary data for chart: count of due vs. not due trucks
        $totalTrucks = count($trucksData);
        $dueCount    = count($dueTrucks);
        $notDueCount = count($availableTrucks);

        // Build a simple data structure for Chart.js with updated colors for due trucks
        $chartData = [
            'labels'   => ['Due For Inspection', 'Not Due'],
            'datasets' => [[
                'label' => 'Inspection Status',
                'data'  => [$dueCount, $notDueCount],
                'backgroundColor' => [
                    'rgba(255, 0, 0, 0.6)',   // Red for "Due For Inspection"
                    'rgba(75, 192, 192, 0.6)' // Alternate color for "Not Due"
                ],
            ]]
        ];

        // Pass everything to the view
        return view('admin/maintenance', [
            'totalTrucks'     => $totalTrucks,
            'dueTrucks'       => $dueTrucks,
            'chartData'       => $chartData,
            'availableTrucks' => $availableTrucks,
        ]);
    }

    

}
