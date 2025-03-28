<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use App\Models\BookingModel;
use App\Models\ClientManagementModel;
use CodeIgniter\Controller;
use Config\Services;
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
        // Initialize Firebase
        $db = Services::firebase();

        // ---------------------
        // A) BOOKING DATA
        // ---------------------
        $bookingsRef = $db->getReference('Bookings');
        $bookingsSnapshot = $bookingsRef->getSnapshot();
        $allBookings = $bookingsSnapshot->getValue() ?? [];

        $totalBookings = 0;
        $pendingBookings = 0;
        $numRequests = 0; // Number of "pending" bookings
        
        if (is_array($allBookings)) {
            $totalBookings = count($allBookings);
            foreach ($allBookings as $bk) {
                if (is_array($bk) && isset($bk['status'])) {
                    if (strtolower($bk['status']) === 'pending') {
                        $pendingBookings++;
                    }
                    $numRequests = $pendingBookings; 
                }
            }
        }

        // ---------------------
        // B) USERS DATA (to count number of users)
        // ---------------------
        $usersRef = $db->getReference('Users');
        $usersSnapshot = $usersRef->getSnapshot();
        $allUsers = $usersSnapshot->getValue() ?? [];

        // Count the number of users
        $numUsers = is_array($allUsers) ? count($allUsers) : 0;

        // ---------------------
        // C) TRUCKS DATA
        // ---------------------
        $trucksRef = $db->getReference('Trucks');
        $trucksSnapshot = $trucksRef->getSnapshot();
        $allTrucks = $trucksSnapshot->getValue() ?? [];

        $goodConditionCount = 0;
        $needsMaintenanceCount = 0;
        $trucksList = $allTrucks;

        if (is_array($allTrucks)) {
            foreach ($allTrucks as $tId => $truck) {
                $lastInspectionDate    = $truck['last_inspection_date']    ?? null;
                $lastInspectionMileage = $truck['last_inspection_mileage'] ?? 0;
                $currentMileage        = $truck['current_mileage']         ?? 0;

                $timeOverdue = false;
                if (!empty($lastInspectionDate)) {
                    try {
                        $dateNow  = new \DateTime();
                        $dateLast = new \DateTime($lastInspectionDate);
                        $dateLast->add(new \DateInterval('P6M'));
                        if ($dateNow > $dateLast) {
                            $timeOverdue = true;
                        }
                    } catch (\Exception $e) {}
                }

                $mileageOverdue = false;
                if (($currentMileage - $lastInspectionMileage) >= 20000) {
                    $mileageOverdue = true;
                }

                if ($timeOverdue || $mileageOverdue) {
                    $needsMaintenanceCount++;
                } else {
                    $goodConditionCount++;
                }
            }
        }

        // ---------------------
        // D) DRIVERS DATA
        // ---------------------
        $driversRef = $db->getReference('Drivers');
        $driversSnapshot = $driversRef->getSnapshot();
        $allDrivers = $driversSnapshot->getValue() ?? [];

        $driverLocations = [];
        $assignedTruckIds = [];

        if (is_array($allDrivers)) {
            foreach ($allDrivers as $driverId => $driver) {
                if (!empty($driver['last_lat']) && !empty($driver['last_lng'])) {
                    $driverLocations[] = [
                        'name' => ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''),
                        'lat'  => $driver['last_lat'],
                        'lng'  => $driver['last_lng'],
                    ];
                }
                if (!empty($driver['truck_assigned'])) {
                    $assignedTruckIds[$driver['truck_assigned']] = true;
                }
            }
        }

        // Filter trucks with assigned drivers
        $trucksWithDrivers = [];
        foreach ($trucksList as $tid => $truck) {
            if (isset($assignedTruckIds[$tid])) {
                $trucksWithDrivers[$tid] = $truck;
            }
        }

        // Prepare data for the view
        $data = [
            'totalBookings'       => $totalBookings,
            'numRequests'         => $numUsers,  // Updated: Use the number of users
            'pendingBookings'     => $pendingBookings,
            'goodConditionCount'  => $goodConditionCount,
            'needsMaintenanceCount' => $needsMaintenanceCount,
            'trucksWithDrivers'   => $trucksWithDrivers,
            'driverLocations'     => $driverLocations,
            'numUsers'            => $numUsers  // The number of users
        ];

        return view('admin/dashboard', $data);
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
                return redirect()->back();
            }

            // Prepare the user data for the Users node
            $userData = [
                'first_name'      => $this->request->getPost('first_name'),
                'last_name'       => $this->request->getPost('last_name'),
                'email'           => $this->request->getPost('email'),
                'username'        => $this->request->getPost('username'),
                'contact_number'  => $this->request->getPost('contact_number'),
                'address'         => $this->request->getPost('address'),
                'birthday'        => $this->request->getPost('birthday'),
                'gender'          => $this->request->getPost('gender'),
                'user_level'      => $this->request->getPost('user_level'),
                'address_dropoff' => '', // set to blank by default
                // Hash the password before storing
                'password'        => password_hash($plainPassword, PASSWORD_BCRYPT),
            ];

            // Create the user record (assume createUser() returns the new user key, e.g., "User30")
            $userKey = $this->userModel->createUser($userData);

            // If the user is a driver or conductor, create a corresponding driver record
            $userLevel = $this->request->getPost('user_level');
            if ($userLevel === 'driver' || $userLevel === 'conductor') {

                // Get the Firebase DB service
                $firebaseDb = \Config\Services::firebase(false);
                // Retrieve all drivers to determine the next sequential driver number
                $driversSnapshot = $firebaseDb->getReference('Drivers')->getSnapshot();
                $driversArray = $driversSnapshot->getValue();
                $nextDriverNumber = is_array($driversArray) ? count($driversArray) + 1 : 1;
                $driverKey = 'Driver' . $nextDriverNumber; // e.g., "Driver1", "Driver2", etc.

                // Prepare the driver record with the old structure and additional fields
                $driverData = [
                    'birthday'           => $this->request->getPost('birthday'),
                    'contact_number'     => $this->request->getPost('contact_number'),
                    'date_of_employment' => $this->request->getPost('date_of_employment'),
                    'driver_id'          => $driverKey,
                    'employee_id'        => $this->request->getPost('employee_id'),
                    'first_name'         => $this->request->getPost('first_name'),
                    'home_address'       => $this->request->getPost('address'), // using address as home_address
                    'last_name'          => $this->request->getPost('last_name'),
                    'license_expiry'     => $this->request->getPost('license_expiry'),
                    'license_number'     => $this->request->getPost('license_number'),
                    'medical_record'     => $this->request->getPost('medical_record'),
                    'position'           => $userLevel,  // "driver" or "conductor"
                    'trips_completed'    => $this->request->getPost('trips_completed'),
                    'truck_assigned'     => "", // blank since truck assignment is handled separately

                    // Additional fields:
                    'email'              => $this->request->getPost('email'),
                    'last_lng'           => "",
                    'last_lat'           => "",

                    'user_id'            => $userKey, // reference to the Users node key
                ];

                // Create the driver record using the generated driverKey
                $firebaseDb->getReference('Drivers/' . $driverKey)->set($driverData);
            }

            session()->setFlashdata('message', 'User created successfully!');
            return redirect()->to(base_url('admin/users'));
        }

        return redirect()->to(base_url('admin/users'));
    }

    /**
     * Edit user (Process the POST request from the modal).
     */
    public function edit($userKey)
    {
        if ($this->request->getMethod() === 'POST') {
            // Prepare the updated user data
            $userData = [
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

            // Update the user record in the Users node
            $this->userModel->updateUser($userKey, $userData);

            // Determine if the user should have a driver record
            $userLevel = $this->request->getPost('user_level');
            $firebaseDb = \Config\Services::firebase(false);

            if ($userLevel === 'driver' || $userLevel === 'conductor') {

                // Try to find an existing driver record for this user by searching for matching user_id
                $driverRef = $firebaseDb->getReference('Drivers');
                $query = $driverRef->orderByChild('user_id')->equalTo($userKey);
                $snapshot = $query->getSnapshot();
                $driverRecord = $snapshot->getValue();
                if ($driverRecord) {
                    // Retrieve the existing driver key (e.g., "Driver8")
                    $driverKey = array_key_first($driverRecord);
                } else {
                    // No driver record exists, so generate a new driver key
                    $driversSnapshot = $firebaseDb->getReference('Drivers')->getSnapshot();
                    $driversArray = $driversSnapshot->getValue();
                    $nextDriverNumber = is_array($driversArray) ? count($driversArray) + 1 : 1;
                    $driverKey = 'Driver' . $nextDriverNumber;
                }
                
                // Prepare updated driver data following the old structure with additional fields
                $driverData = [
                    'birthday'           => $this->request->getPost('birthday'),
                    'contact_number'     => $this->request->getPost('contact_number'),
                    'date_of_employment' => $this->request->getPost('date_of_employment'),
                    'driver_id'          => $driverKey,
                    'employee_id'        => $this->request->getPost('employee_id'),
                    'first_name'         => $this->request->getPost('first_name'),
                    'home_address'       => $this->request->getPost('address'),
                    'last_name'          => $this->request->getPost('last_name'),
                    'license_expiry'     => $this->request->getPost('license_expiry'),
                    'license_number'     => $this->request->getPost('license_number'),
                    'medical_record'     => $this->request->getPost('medical_record'),
                    'position'           => $userLevel,
                    'trips_completed'    => $this->request->getPost('trips_completed'),
                    'truck_assigned'     => "", // remains blank

                    // Additional fields:
                    'email'              => $this->request->getPost('email'),
                    'last_lng'           => "",
                    'last_lat'           => "",

                    'user_id'            => $userKey,
                ];

                // Update (or create) the driver record using the driverKey
                $firebaseDb->getReference('Drivers/' . $driverKey)->set($driverData);
            } else {
                // If the user is no longer a driver or conductor, remove any corresponding driver record
                $driverRef = $firebaseDb->getReference('Drivers');
                $query = $driverRef->orderByChild('user_id')->equalTo($userKey);
                $snapshot = $query->getSnapshot();
                $driverRecord = $snapshot->getValue();
                if ($driverRecord) {
                    $driverKey = array_key_first($driverRecord);
                    $firebaseDb->getReference('Drivers/' . $driverKey)->remove();
                }
            }

            session()->setFlashdata('message', 'User updated successfully!');
            return redirect()->to(base_url('admin/users'));
        }

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
            'current_mileage'         => $this->request->getPost('current_mileage'),
            // Initialize maintenance items for specific parts
            'maintenance_items' => [
                'engine_oil' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 10000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date')
                ],
                'transmission' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 60000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date')
                ],
                'air_filters' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date')
                ],
                'brake_components' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date')
                ],
                'tires' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 50000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date')
                ],
                'belt_hoses' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date')
                ],
            ],
        ];
        
        $truckModel = new TruckModel();
        $newTruckId = $truckModel->insertTruck($data);
        session()->setFlashdata('success', 'Truck created successfully with ID: ' . $newTruckId);
        return redirect()->to(base_url('admin/trucks'));
    }

    public function updateTruck($truckId)
    {
        // Retrieve common truck update values
        $lastInspectionDate   = $this->request->getPost('last_inspection_date');
        $lastInspectionMileage = $this->request->getPost('last_inspection_mileage');
        $currentMileage        = $this->request->getPost('current_mileage');

        // Retrieve individual maintenance item values, if provided, or default to the common inspection values
        $engineOilMileage = $this->request->getPost('engine_oil_last_service_mileage') ?? $lastInspectionMileage;
        $engineOilDate    = $this->request->getPost('engine_oil_last_service_date') ?? $lastInspectionDate;

        $transmissionMileage = $this->request->getPost('transmission_last_service_mileage') ?? $lastInspectionMileage;
        $transmissionDate    = $this->request->getPost('transmission_last_service_date') ?? $lastInspectionDate;

        $airFiltersMileage = $this->request->getPost('air_filters_last_service_mileage') ?? $lastInspectionMileage;
        $airFiltersDate    = $this->request->getPost('air_filters_last_service_date') ?? $lastInspectionDate;

        $brakeComponentsMileage = $this->request->getPost('brake_components_last_service_mileage') ?? $lastInspectionMileage;
        $brakeComponentsDate    = $this->request->getPost('brake_components_last_service_date') ?? $lastInspectionDate;

        $tiresMileage = $this->request->getPost('tires_last_service_mileage') ?? $lastInspectionMileage;
        $tiresDate    = $this->request->getPost('tires_last_service_date') ?? $lastInspectionDate;

        $beltHosesMileage = $this->request->getPost('belt_hoses_last_service_mileage') ?? $lastInspectionMileage;
        $beltHosesDate    = $this->request->getPost('belt_hoses_last_service_date') ?? $lastInspectionDate;

        // Build maintenance items array with individual values
        $maintenanceItems = [
            'engine_oil' => [
                'last_service_mileage'    => $engineOilMileage,
                'recommended_interval_km' => 10000,
                'last_service_date'       => $engineOilDate,
            ],
            'transmission' => [
                'last_service_mileage'    => $transmissionMileage,
                'recommended_interval_km' => 60000,
                'last_service_date'       => $transmissionDate,
            ],
            'air_filters' => [
                'last_service_mileage'    => $airFiltersMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $airFiltersDate,
            ],
            'brake_components' => [
                'last_service_mileage'    => $brakeComponentsMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $brakeComponentsDate,
            ],
            'tires' => [
                'last_service_mileage'    => $tiresMileage,
                'recommended_interval_km' => 50000,
                'last_service_date'       => $tiresDate,
            ],
            'belt_hoses' => [
                'last_service_mileage'    => $beltHosesMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $beltHosesDate,
            ],
        ];

        // Update common truck data along with the maintenance items
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
            'last_inspection_date'    => $lastInspectionDate,
            'last_inspection_mileage' => $lastInspectionMileage,
            'current_mileage'         => $currentMileage,
            'maintenance_items'       => $maintenanceItems,
        ];

        $truckModel = new \App\Models\TruckModel();
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
     * - Also pass all trucks for display purposes.
     */
    public function driverManagement()
    {
        $driverModel = new \App\Models\DriverModel();
        $data['drivers'] = $driverModel->getDrivers();

        // Get eligible users from the Users collection.
        $userModel = new \App\Models\UserModel();
        $allEligibleUsers = $userModel->getEligibleUsers();
        
        // Remove users already assigned in the Drivers collection (assuming driver record stores 'user_id').
        if (!empty($data['drivers'])) {
            foreach ($data['drivers'] as $driver) {
                // Remove only if a truck has already been assigned
                if (isset($driver['user_id']) && !empty($driver['truck_assigned'])) {
                    unset($allEligibleUsers[$driver['user_id']]);
                }
            }
        }
        $data['eligibleUsers'] = $allEligibleUsers;

        // Get all trucks from the Trucks collection.
        $truckModel = new \App\Models\TruckModel();
        $allTrucks = $truckModel->getTrucks();
        $data['allTrucks'] = $allTrucks; // pass complete trucks list for lookup in the view

        // Compute available trucks arrays for driver and conductor.
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
     * Assign Truck to a Driver/Conductor.
     * Instead of creating a driver record with all fields, we simply
     * create (or update) the record by assigning a truck to an eligible user.
     */
    public function createDriver()
    {
        if ($this->request->getMethod() == 'POST') {
            // Retrieve POST data: selected user and truck
            $user_id        = $this->request->getPost('user_id'); 
            $truck_assigned = $this->request->getPost('truck_assigned');

            if (empty($user_id) || empty($truck_assigned)) {
                return redirect()->to(base_url('admin/driver'))
                                ->with('error', 'Both User and Truck must be selected.');
            }
            
            // Retrieve user details from Users collection.
            $userModel = new \App\Models\UserModel();
            $user = $userModel->getUser($user_id);
            if (!$user) {
                return redirect()->to(base_url('admin/driver'))
                                ->with('error', 'User not found.');
            }
            
            $user_position = strtolower($user['user_level']);
            
            // Check if the selected truck is already assigned for this position.
            $driverModel = new \App\Models\DriverModel();
            $existingDrivers = $driverModel->getDrivers();
            if ($existingDrivers) {
                foreach ($existingDrivers as $driver) {
                    if (isset($driver['truck_assigned']) && 
                        $driver['truck_assigned'] === $truck_assigned &&
                        isset($driver['position']) && strtolower($driver['position']) === $user_position) {
                        return redirect()->to(base_url('admin/driver'))
                                        ->with('error', 'This truck is already assigned to a ' . ucfirst($user_position) . '.');
                    }
                }
            }
            
            // Check if a driver record already exists for this user.
            $existingDriverKey = null;
            if ($existingDrivers) {
                foreach ($existingDrivers as $key => $driver) {
                    if (isset($driver['user_id']) && $driver['user_id'] == $user_id) {
                        $existingDriverKey = $key;
                        break;
                    }
                }
            }
            
            if ($existingDriverKey) {
                // Update the existing driver record with the new truck assignment.
                $driverModel->updateDriver($existingDriverKey, ['truck_assigned' => $truck_assigned]);
                $driverId = $existingDriverKey;
            } else {
                // Merge user details with the truck assignment to create a new record.
                $data = [
                    'user_id'         => $user_id,
                    'first_name'      => $user['first_name'],
                    'last_name'       => $user['last_name'],
                    'contact_number'  => $user['contact_number'],
                    'position'        => $user['user_level'], // driver or conductor
                    'home_address'    => $user['address'],
                    'birthday'        => $user['birthday'],
                    'email'           => $user['email'],
                    'truck_assigned'  => $truck_assigned,
                    // Optionally set defaults for other fields:
                    'employee_id'        => '',
                    'date_of_employment' => date('Y-m-d'),
                    'license_number'     => '',
                    'license_expiry'     => '',
                    'medical_record'     => '',
                    'trips_completed'    => 0,
                ];
                $driverId = $driverModel->insertDriver($data);
            }

            return redirect()->to(base_url('admin/driver'))
                            ->with('success', 'Truck assignment updated successfully. Driver ID: ' . $driverId);
        }
        return redirect()->to(base_url('admin/driver'))->with('error', 'Invalid request.');
    }


    /**
     * Update an existing Driver/Conductor record.
     * Now, only the truck assignment is updated.
     */
    public function updateDriver($driverId)
    {
        if ($this->request->getMethod() == 'POST') {
            $truck_assigned = $this->request->getPost('truck_assigned');
            if (empty($truck_assigned)) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'Truck must be selected.');
            }

            // Optional: enforce uniqueness of truck assignment.
            $driverModel = new \App\Models\DriverModel();
            $existingDrivers = $driverModel->getDrivers();
            // First, retrieve the record being updated to know its position.
            $currentDriver = $driverModel->getDriver($driverId);
            if (!$currentDriver) {
                return redirect()->to(base_url('admin/driver'))->with('error', 'Driver record not found.');
            }
            $user_position = strtolower($currentDriver['position']);
            if ($existingDrivers) {
                foreach ($existingDrivers as $id => $driver) {
                    if ($id == $driverId) continue; // skip current record
                    if (
                        isset($driver['truck_assigned']) && 
                        $driver['truck_assigned'] === $truck_assigned &&
                        isset($driver['position']) && strtolower($driver['position']) === $user_position
                    ) {
                        return redirect()->to(base_url('admin/driver'))->with('error', 'This truck is already assigned to a ' . ucfirst($user_position) . '.');
                    }
                }
            }

            $data = [
                'truck_assigned' => $truck_assigned
            ];

            $driverModel->updateDriver($driverId, $data);
            return redirect()->to(base_url('admin/driver'))->with('success', 'Truck assignment updated successfully.');
        }
        return redirect()->to(base_url('admin/driver'))->with('error', 'Invalid request.');
    }

    /**
     * Delete a Driver/Conductor record.
     */
    public function deleteDriver($driverId)
    {
        $driverModel = new \App\Models\DriverModel();
        $driverModel->deleteDriver($driverId);
        return redirect()->to(base_url('admin/driver'))->with('success', 'Driver/Conductor deleted successfully.');
    }

    /**
     * View details of a specific Driver/Conductor.
     */
    public function viewDriver($driverId)
    {
        $driverModel = new \App\Models\DriverModel();
        $data['driver'] = $driverModel->getDriver($driverId);
        return view('admin/driver_detail', $data);
    }



    // ============== BOOKING MODULE ===================  //

    // List all bookings for admin review
    public function bookings()
    {
        $bookingModel = new BookingModel();
        $data['bookings'] = $bookingModel->getAllBookings() ?? [];

        // Load all drivers from Firebase
        $firebase       = service('firebase');
        $driversRef     = $firebase->getReference('Drivers');
        $allDriversData = $driversRef->getValue() ?? [];

        // We'll separate them into "drivers" and "conductors"
        $driversList = [];
        $conductorsList = [];
        foreach ($allDriversData as $driverKey => $driverInfo) {
            $pos = strtolower($driverInfo['position'] ?? '');
            if ($pos === 'driver') {
                $driversList[$driverKey] = $driverInfo;
            } elseif ($pos === 'conductor') {
                $conductorsList[$driverKey] = $driverInfo;
            }
        }

        $data['driversList']    = $driversList;
        $data['conductorsList'] = $conductorsList;

        return view('admin/bookings', $data);
    }

    // Update booking status (approval/rejection, etc.), or reassign driver/conductor
    public function updateBookingStatus()
    {
        $bookingId   = $this->request->getPost('booking_id');
        $status      = $this->request->getPost('status');     // e.g., "approved", "rejected", etc.
        $distance    = $this->request->getPost('distance');     // new distance value
        $driverId    = $this->request->getPost('driver');       // selected driver key
        $conductorId = $this->request->getPost('conductor');    // (if provided, but now we are using driver-only selection)
        $truckId     = $this->request->getPost('truck_id');     // hidden field in the form

        // Initialize update data with status and distance.
        $updateData = [
            'status'   => $status,
            'distance' => $distance,
        ];

        // Get Firebase service instance
        $firebase = service('firebase');
        $bookingRef = $firebase->getReference('Bookings/' . $bookingId);
        $existingBooking = $bookingRef->getValue();

        // Prevent changes if booking is already completed or rejected.
        if ($existingBooking && in_array($existingBooking['status'], ['completed', 'rejected'])) {
            session()->setFlashdata('error', 'Cannot update a booking that is already completed or rejected.');
            return redirect()->to(base_url('admin/bookings'));
        }

        $bookingModel = new BookingModel();
        $allBookings  = $bookingModel->getAllBookings(); // to detect conflicts
        $driversRef   = $firebase->getReference('Drivers');

        // 1) If a new driver is selected, check for conflict.
        if (!empty($driverId)) {
            if ($allBookings && is_array($allBookings)) {
                foreach ($allBookings as $b) {
                    if (!is_array($b)) continue; // skip invalid
                    // Only flag conflict if the booking is active (approved or in-transit)
                    if (
                        isset($b['driver_id']) &&
                        $b['driver_id'] === $driverId &&
                        in_array($b['status'], ['approved', 'in-transit'])
                    ) {
                        session()->setFlashdata('error', 'Driver is currently assigned to an ongoing booking (#'.$b['booking_id'].').');
                        return redirect()->to(base_url('admin/bookings'));
                    }
                }
            }

            // If no conflict, fetch driver data.
            $driverData = $driversRef->getChild($driverId)->getValue();
            if ($driverData) {
                $fullName = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                $updateData['driver_name'] = $fullName;
                $updateData['driver_id']   = $driverId;

                // Automatically assign a conductor partner based on the driver's truck assignment.
                $partnerConductor = null;
                $allDrivers = $driversRef->getValue() ?? [];
                foreach ($allDrivers as $key => $info) {
                    if (
                        strtolower($info['position'] ?? '') === 'conductor' &&
                        !empty($info['truck_assigned']) &&
                        $info['truck_assigned'] === ($driverData['truck_assigned'] ?? '')
                    ) {
                        $partnerConductor = $info;
                        $updateData['conductor_id']   = $key;
                        $updateData['conductor_name'] = trim(($info['first_name'] ?? '') . ' ' . ($info['last_name'] ?? ''));
                        break;
                    }
                }

                // Update truck details based on the driver's assigned truck.
                if (!empty($driverData['truck_assigned'])) {
                    $newTruckId = $driverData['truck_assigned'];
                    $truckData  = $firebase->getReference('Trucks/'.$newTruckId)->getValue();
                    if ($truckData) {
                        $updateData['truck_id']      = $truckData['truck_id']       ?? $newTruckId;
                        $updateData['truck_model']   = $truckData['truck_model']     ?? '';
                        $updateData['license_plate'] = $truckData['plate_number']    ?? '';
                        $updateData['type_of_truck'] = $truckData['truck_type']      ?? '';
                    }
                }
            }
        }

        // 2) If the admin changed the truck manually (and no driver update occurred), update truck details.
        if (!empty($truckId) && !isset($updateData['truck_id'])) {
            $truckData = $firebase->getReference('Trucks/' . $truckId)->getValue();
            if ($truckData) {
                $updateData['truck_id']      = $truckData['truck_id']       ?? $truckId;
                $updateData['truck_model']   = $truckData['truck_model']     ?? '';
                $updateData['license_plate'] = $truckData['plate_number']    ?? '';
                $updateData['type_of_truck'] = $truckData['truck_type']      ?? '';
            }
        }

        // Update the booking with the final data.
        $bookingModel->updateBooking($bookingId, $updateData);

        // 3) If the booking is being marked as "completed", update the truck's current mileage.
        if ($status === 'completed' && isset($updateData['truck_id'])) {
            $truckRef = $firebase->getReference('Trucks/' . $updateData['truck_id']);
            $truckData = $truckRef->getValue();
            if ($truckData) {
                $currentMileage = isset($truckData['current_mileage']) ? floatval($truckData['current_mileage']) : 0;
                $newMileage = $currentMileage + floatval($distance);
                // Update the truck's current mileage.
                $truckRef->update(['current_mileage' => $newMileage]);
            }
        }

        session()->setFlashdata('success', 'Booking #'.$bookingId.' updated successfully!');
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
        // Get Firebase Realtime Database instance
        $db = Services::firebase();
        
        // Get a reference to the "Reports" node
        $reportsRef = $db->getReference('Reports');
        $snapshot = $reportsRef->getSnapshot();
        
        // Get the reports as an associative array (or an empty array if none)
        $reports = $snapshot->getValue() ?? [];
        
        // Optionally, sort the reports naturally by report number (keys like "R000001")
        uksort($reports, function($a, $b) {
            return strnatcmp($a, $b);
        });
        
        // Pass the reports data to the view
        return view('admin/reports_management', ['reports' => $reports]);
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
            return view('admin/maintenance', [
                'totalTrucks'      => 0,
                'dueTrucks'        => [],
                'chartData'        => [],
                'availableTrucks'  => [],
            ]);
        }
    
        // Convert the snapshot into an associative array and natural sort by truck ID
        $trucksData = $snapshot->getValue();
        uksort($trucksData, 'strnatcmp');
    
        // Initialize arrays for due trucks and component counts.
        $dueTrucks = [];
        $componentCounts = [
            'engine_oil'      => 0,
            'transmission'    => 0,
            'air_filters'     => 0,
            'brake_components'=> 0,
            'tires'           => 0,
            'belt_hoses'      => 0,
        ];
    
        // Loop over each truck and check its maintenance items
        foreach ($trucksData as $truckId => $truck) {
            $dueComponents = [];
            if (isset($truck['maintenance_items'])) {
                foreach ($truck['maintenance_items'] as $component => $item) {
                    $lastServiceMileage    = $item['last_service_mileage'] ?? 0;
                    $recommendedInterval   = $item['recommended_interval_km'] ?? 0;
                    $currentMileage        = $truck['current_mileage'] ?? 0;
                    // Check if the component is due based on mileage
                    if (($currentMileage - $lastServiceMileage) >= $recommendedInterval) {
                        $dueComponents[] = $component;
                        if (isset($componentCounts[$component])) {
                            $componentCounts[$component]++;
                        } else {
                            $componentCounts[$component] = 1;
                        }
                    }
                }
            }
            if (!empty($dueComponents)) {
                $dueTrucks[] = [
                    'truckId'       => $truckId,
                    'details'       => $truck,
                    'dueComponents' => $dueComponents,
                ];
            }
        }
    
        // Determine available trucks (those that do NOT have any due maintenance items)
        $dueTruckIds = array_map(function ($dueTruck) {
            return $dueTruck['truckId'];
        }, $dueTrucks);
        $availableTrucks = [];
        foreach ($trucksData as $truckId => $truck) {
            if (!in_array($truckId, $dueTruckIds)) {
                $availableTrucks[$truckId] = $truck;
            }
        }
        uksort($availableTrucks, 'strnatcmp');
    
        // Build chart data using a mapping for prettier labels
        $labels = [
            'Engine Oil & Filter', 
            'Transmission Fluids & Filter', 
            'Air Filters', 
            'Brake Components', 
            'Tires', 
            'Belt & Hoses'
        ];
        $dataValues = [
            $componentCounts['engine_oil']      ?? 0,
            $componentCounts['transmission']    ?? 0,
            $componentCounts['air_filters']     ?? 0,
            $componentCounts['brake_components']?? 0,
            $componentCounts['tires']           ?? 0,
            $componentCounts['belt_hoses']      ?? 0,
        ];
        $chartData = [
            'labels'   => $labels,
            'datasets' => [[
                'label' => 'Components Due for Inspection',
                'data'  => $dataValues,
                'backgroundColor' => [
                    'rgba(255, 99, 132, 0.6)',   // Engine Oil & Filter
                    'rgba(54, 162, 235, 0.6)',   // Transmission Fluids & Filter
                    'rgba(255, 206, 86, 0.6)',   // Air Filters
                    'rgba(75, 192, 192, 0.6)',   // Brake Components
                    'rgba(153, 102, 255, 0.6)',  // Tires
                    'rgba(255, 159, 64, 0.6)'    // Belt & Hoses
                ],
            ]]
        ];
    
        $totalTrucks = count($trucksData);
    
        // Pass data to the view
        return view('admin/maintenance', [
            'totalTrucks'     => $totalTrucks,
            'dueTrucks'       => $dueTrucks,
            'chartData'       => $chartData,
            'availableTrucks' => $availableTrucks,
        ]);
    }
    

    

}
