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
     * Get counts for pending notifications in:
     * - Bookings: where status === 'pending'
     * - Maintenance: trucks that need inspection (one or more components are due)
     * - Reports: where remark_status is missing or equals 'Pending'
     *
     * @return array
     */
    private function getNotificationCounts()
    {
        // Initialize firebase locally.
        $firebase = service('firebase');

        $counts = [
            'pendingBookingsCount'   => 0,
            'maintenanceAlertsCount' => 0,
            'pendingReportsCount'    => 0,
        ];

        // --- Bookings Notification Count ---
        $bookingsSnapshot = $firebase->getReference('Bookings')->getSnapshot();
        if ($bookingsSnapshot->exists()) {
            $bookingsData = $bookingsSnapshot->getValue();
            foreach ($bookingsData as $booking) {
                if (isset($booking['status']) && strtolower(trim($booking['status'])) === 'pending') {
                    $counts['pendingBookingsCount']++;
                }
            }
        }

        // --- Maintenance Notification Count ---
        $trucksSnapshot = $firebase->getReference('Trucks')->getSnapshot();
        if ($trucksSnapshot->exists()) {
            $trucksData = $trucksSnapshot->getValue();
            uksort($trucksData, 'strnatcmp');

            // Define maintenance components and interval thresholds.
            $allComponents = [
                'engine_system'               => 'Engine System',
                'transmission_drivetrain'     => 'Transmission & Drivetrain',
                'brake_system'                => 'Brake System',
                'suspension_chassis'          => 'Suspension & Chassis',
                'fuel_cooling_system'         => 'Fuel & Cooling System',
                'steering_system'             => 'Steering System',
                'electrical_auxiliary_system' => 'Electrical & Auxiliary System',
            ];

            $intervals = [
                'engine_system'               => ['new' => 5000,  'old' => 4000],
                'transmission_drivetrain'     => ['new' => 20000, 'old' => 15000],
                'brake_system'                => ['new' => 10000, 'old' => 4000],
                'suspension_chassis'          => ['new' => 5000,  'old' => 4000],
                'fuel_cooling_system'         => ['new' => 20000, 'old' => 15000],
                'steering_system'             => ['new' => 20000, 'old' => 10000],
                'electrical_auxiliary_system' => ['new' => 10000, 'old' => 7000],
            ];

            $dueTrucksCount = 0;
            foreach ($trucksData as $truckId => $truck) {
                // Determine if truck is New or Old based on manufacturing date and current mileage.
                $manufacturingDate = $truck['manufacturing_date'] ?? '';
                $yearsOld = !empty($manufacturingDate) ? date('Y') - date('Y', strtotime($manufacturingDate)) : 0;
                $currentMileage = $truck['current_mileage'] ?? 0;
                $isNew = ($yearsOld <= 5 && $currentMileage < 100000);

                // Gather overdue components for this truck.
                $dueComponents = [];
                foreach ($allComponents as $componentKey => $label) {
                    $lastServiceMileage = isset($truck['maintenance_items'][$componentKey]['last_service_mileage'])
                        ? $truck['maintenance_items'][$componentKey]['last_service_mileage']
                        : 0;

                    // Choose the correct interval based on new/old truck.
                    $mileageInterval = $isNew ? $intervals[$componentKey]['new'] : $intervals[$componentKey]['old'];
                    // Check mileage condition.
                    $mileageDue = (($currentMileage - $lastServiceMileage) >= $mileageInterval);

                    // Check date condition (if available).
                    $dateDue = false;
                    if (isset($truck['maintenance_items'][$componentKey]['last_service_date'])) {
                        try {
                            $lastServiceDate = new \DateTime($truck['maintenance_items'][$componentKey]['last_service_date']);
                            // Define a 6-month service interval.
                            $lastServiceDate->add(new \DateInterval('P6M'));
                            $now = new \DateTime();
                            if ($now >= $lastServiceDate) {
                                $dateDue = true;
                            }
                        } catch (\Exception $e) {
                            // Handle date parsing errors if necessary.
                        }
                    }

                    // Consider the component due if either condition is met.
                    if ($mileageDue || $dateDue) {
                        $dueComponents[] = $componentKey;
                    }
                }
                if (!empty($dueComponents)) {
                    $dueTrucksCount++;
                }
            }
            $counts['maintenanceAlertsCount'] = $dueTrucksCount;
        }

        // --- Reports Notification Count ---
        $reportsSnapshot = $firebase->getReference('Reports')->getSnapshot();
        if ($reportsSnapshot->exists()) {
            $reportsData = $reportsSnapshot->getValue();
            foreach ($reportsData as $report) {
                // Count as pending if remark_status exists and equals "pending" (ignoring case and extra spaces).
                if (isset($report['remark_status']) && strtolower(trim($report['remark_status'])) === 'pending') {
                    $counts['pendingReportsCount']++;
                }
            }
        }

        return $counts;
    }

    // Add this function to get booking counts (requires Firebase access)
    private function getTruckBookingCount($truckId, $db) {
        try {
            // Access the Bookings node
            $bookingsRef = $db->getReference('Bookings');
            
            // Get all bookings data
            $allBookings = $bookingsRef->getValue();
    
            // Initialize count
            $count = 0;
    
            // Loop through all bookings and count matches
            if (!empty($allBookings)) {
                foreach ($allBookings as $booking) {
                    if (isset($booking['truck_id']) && $booking['truck_id'] === $truckId) {
                        $count++;
                    }
                }
            }
    
            return $count;
        } catch (\Throwable $e) {
            log_message('error', 'Firebase Booking Count Error for truck ' . $truckId . ': ' . $e->getMessage());
            return 0;
        }
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
                    if (strtolower(trim($bk['status'])) === 'pending') {
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
        $numUsers = is_array($allUsers) ? count($allUsers) : 0;
    
        // ---------------------
        // C) TRUCKS DATA (Maintenance Calculation using per-component logic)
        // ---------------------
        $trucksRef = $db->getReference('Trucks');
        $trucksSnapshot = $trucksRef->getSnapshot();
        $allTrucks = $trucksSnapshot->getValue() ?? [];
    
        $goodConditionCount = 0;
        $needsMaintenanceCount = 0;
        $totalTrucks = 0;  // Ensure this counter is initialized
        $trucksList = $allTrucks;
        
        // Define the 7 major components and their labels.
        $allComponents = [
            'engine_system'               => 'Engine System',
            'transmission_drivetrain'     => 'Transmission & Drivetrain',
            'brake_system'                => 'Brake System',
            'suspension_chassis'          => 'Suspension & Chassis',
            'fuel_cooling_system'         => 'Fuel & Cooling System',
            'steering_system'             => 'Steering System',
            'electrical_auxiliary_system' => 'Electrical & Auxiliary System',
        ];
    
        // Define intervals for New vs. Old trucks.
        // These intervals apply per-component.
        $intervals = [
            'engine_system'               => ['new' => 5000,  'old' => 4000],
            'transmission_drivetrain'     => ['new' => 20000, 'old' => 15000],
            'brake_system'                => ['new' => 10000, 'old' => 4000],
            'suspension_chassis'          => ['new' => 5000,  'old' => 4000],
            'fuel_cooling_system'         => ['new' => 20000, 'old' => 15000],
            'steering_system'             => ['new' => 20000, 'old' => 10000],
            'electrical_auxiliary_system' => ['new' => 10000, 'old' => 7000],
        ];
    
        if (is_array($allTrucks)) {
            foreach ($allTrucks as $tId => $truck) {
                $totalTrucks++;
    
                // Determine truck age based on manufacturing_date.
                $manufacturingDate = $truck['manufacturing_date'] ?? '';
                $yearsOld = !empty($manufacturingDate) ? date('Y') - date('Y', strtotime($manufacturingDate)) : 0;
                $currentMileage = $truck['current_mileage'] ?? 0;
                
                // Classify truck as "New" if 5 years or younger and below 100,000 km.
                $isNew = ($yearsOld <= 5 && $currentMileage < 100000);
                
                // Gather due components for this truck.
                $dueComponents = [];
                foreach ($allComponents as $componentKey => $label) {
                    // Get last service mileage (if available).
                    $lastServiceMileage = isset($truck['maintenance_items'][$componentKey]['last_service_mileage']) 
                        ? $truck['maintenance_items'][$componentKey]['last_service_mileage'] 
                        : 0;
                    // Choose the correct mileage interval based on new/old status.
                    $mileageInterval = $isNew ? $intervals[$componentKey]['new'] : $intervals[$componentKey]['old'];
                    // Check if mileage condition is met.
                    $mileageDue = (($currentMileage - $lastServiceMileage) >= $mileageInterval);
    
                    // Check if time condition is met (6 months overdue).
                    $dateDue = false;
                    if (isset($truck['maintenance_items'][$componentKey]['last_service_date'])) {
                        try {
                            $lastServiceDate = new \DateTime($truck['maintenance_items'][$componentKey]['last_service_date']);
                            $lastServiceDate->add(new \DateInterval('P6M'));
                            $now = new \DateTime();
                            if ($now >= $lastServiceDate) {
                                $dateDue = true;
                            }
                        } catch (\Exception $e) {
                            // Optionally handle date parsing errors.
                        }
                    }
                    // If either mileage or date condition is met, mark this component as due.
                    if ($mileageDue || $dateDue) {
                        $dueComponents[] = $componentKey;
                    }
                }
                // If any component is due, count this truck as needing maintenance.
                if (!empty($dueComponents)) {
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
    
        // Filter trucks with assigned drivers.
        $trucksWithDrivers = [];
        foreach ($trucksList as $tid => $truck) {
            if (isset($assignedTruckIds[$tid])) {
                $trucksWithDrivers[$tid] = $truck;
            }
        }
    
        // ---------------------
        // Prepare Data for the View
        // ---------------------
        $data = [
            'totalBookings'         => $totalBookings,
            'numRequests'           => $numUsers,  // Using number of users as numRequests
            'pendingBookings'       => $pendingBookings,
            'goodConditionCount'    => $goodConditionCount,
            'needsMaintenanceCount' => $needsMaintenanceCount,
            'trucksWithDrivers'     => $trucksWithDrivers,
            'driverLocations'       => $driverLocations,
            'numUsers'              => $numUsers,
        ];
    
        // Retrieve and merge notification counts.
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);
    
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

            // Retrieve notification counts (bookings, maintenance, reports)
            $notificationCounts = $this->getNotificationCounts();

            // Merge user data with notification counts
            $data = array_merge(['user' => $userData], $notificationCounts);

            return view('admin/users/profile', $data);
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
        // Retrieve all users.
        $users = $this->userModel->getAllUsers();

        // Retrieve notification counts.
        $notificationCounts = $this->getNotificationCounts();

        // Build a separate data array that includes both sections.
        $data = [
            'users' => $users,
        ];

        // Merge notification counts into the data array.
        $data = array_merge($data, $notificationCounts);

        return view('admin/users/manage', $data);
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
    public function truck()
    {
        $truckModel = new TruckModel();
        $trucks = $truckModel->getTrucks();

        // Re-index the array in case it is associative
        $trucks = $trucks ? array_values($trucks) : [];

        // Sort the trucks naturally by truck_id (e.g., Truck1, Truck2, ..., Truck10)
        usort($trucks, function($a, $b) {
            return strnatcmp($a['truck_id'], $b['truck_id']);
        });

        $data['trucks'] = $trucks;

        // Retrieve and merge notification counts.
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);

        
        return view('admin/truck_management', $data);
    }

    /**
     * Create (store) a new truck in Firebase
     */
    public function storeTruck()
    {
        // Get manufacturing date
        $manufacturingDate = $this->request->getPost('manufacturing_date');

        // Prepare data from POST
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

            // NEW FIELD
            'manufacturing_date'      => $manufacturingDate,

            // Updated maintenance items with 7 major components
            'maintenance_items' => [
                'engine_system' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 5000,  // example
                    'last_service_date'       => $this->request->getPost('last_inspection_date'),
                ],
                'transmission_drivetrain' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 60000, // example
                    'last_service_date'       => $this->request->getPost('last_inspection_date'),
                ],
                'brake_system' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date'),
                ],
                'suspension_chassis' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date'),
                ],
                'fuel_cooling_system' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date'),
                ],
                'steering_system' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date'),
                ],
                'electrical_auxiliary_system' => [
                    'last_service_mileage'    => $this->request->getPost('last_inspection_mileage'),
                    'recommended_interval_km' => 20000,
                    'last_service_date'       => $this->request->getPost('last_inspection_date'),
                ],
            ],
        ];

        // 2) Check if a file was uploaded (Truck Image)
        $imageFile = $this->request->getFile('truck_image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            // a) Get firebaseStorage service
            $storage = service('firebaseStorage');

            // b) Get bucket from .env
            $bucketName = env('FIREBASE_STORAGE_BUCKET');
            $bucket     = $storage->getBucket($bucketName);

            // c) Move the file locally first
            $filename  = $imageFile->getRandomName();
            $localPath = WRITEPATH . 'uploads/' . $filename;
            $imageFile->move(WRITEPATH . 'uploads', $filename);

            // d) Upload to folder "truck_images/"
            $firebasePath = 'truck_images/' . $filename;
            $bucket->upload(fopen($localPath, 'r'), [
                'name' => $firebasePath
            ]);

            // e) Generate a public download URL (assuming your bucket is publicly readable)
            $storageRef = $bucket->object($firebasePath);
            if ($storageRef->exists()) {
                $info = $bucket->info(); 
                $bucketRealName = $info['name'];
                $imageUrl = 'https://firebasestorage.googleapis.com/v0/b/'
                          . $bucketRealName
                          . '/o/' . urlencode($firebasePath)
                          . '?alt=media';
                $data['image_url'] = $imageUrl;
            }
        }

        // 3) Insert record in Firebase Realtime Database
        $truckModel = new TruckModel();
        $newTruckId = $truckModel->insertTruck($data);

        session()->setFlashdata('success', 'Truck created successfully with ID: ' . $newTruckId);
        return redirect()->to(base_url('admin/trucks'));
    }

    /**
     * Update an existing truck
     */
    public function updateTruck($truckId)
    {
        // Gather inspection data
        $lastInspectionDate    = $this->request->getPost('last_inspection_date');
        $lastInspectionMileage = $this->request->getPost('last_inspection_mileage');
        $currentMileage        = $this->request->getPost('current_mileage');

        // NEW: Get the manufacturing date
        $manufacturingDate     = $this->request->getPost('manufacturing_date');

        // Retrieve individual maintenance values for each component
        $engineSystemMileage         = $this->request->getPost('engine_system_last_service_mileage') ?? $lastInspectionMileage;
        $engineSystemDate            = $this->request->getPost('engine_system_last_service_date')     ?? $lastInspectionDate;
        $transmissionDrivetrainMileage = $this->request->getPost('transmission_drivetrain_last_service_mileage') ?? $lastInspectionMileage;
        $transmissionDrivetrainDate    = $this->request->getPost('transmission_drivetrain_last_service_date')     ?? $lastInspectionDate;
        $brakeSystemMileage          = $this->request->getPost('brake_system_last_service_mileage') ?? $lastInspectionMileage;
        $brakeSystemDate             = $this->request->getPost('brake_system_last_service_date')     ?? $lastInspectionDate;
        $suspensionChassisMileage    = $this->request->getPost('suspension_chassis_last_service_mileage') ?? $lastInspectionMileage;
        $suspensionChassisDate       = $this->request->getPost('suspension_chassis_last_service_date')     ?? $lastInspectionDate;
        $fuelCoolingSystemMileage    = $this->request->getPost('fuel_cooling_system_last_service_mileage') ?? $lastInspectionMileage;
        $fuelCoolingSystemDate       = $this->request->getPost('fuel_cooling_system_last_service_date')     ?? $lastInspectionDate;
        $steeringSystemMileage       = $this->request->getPost('steering_system_last_service_mileage') ?? $lastInspectionMileage;
        $steeringSystemDate          = $this->request->getPost('steering_system_last_service_date')     ?? $lastInspectionDate;
        $electricalAuxSysMileage     = $this->request->getPost('electrical_auxiliary_system_last_service_mileage') ?? $lastInspectionMileage;
        $electricalAuxSysDate        = $this->request->getPost('electrical_auxiliary_system_last_service_date')     ?? $lastInspectionDate;

        // Build maintenance items array for the 7 major components
        $maintenanceItems = [
            'engine_system' => [
                'last_service_mileage'    => $engineSystemMileage,
                'recommended_interval_km' => 5000, // example
                'last_service_date'       => $engineSystemDate,
            ],
            'transmission_drivetrain' => [
                'last_service_mileage'    => $transmissionDrivetrainMileage,
                'recommended_interval_km' => 60000, // example
                'last_service_date'       => $transmissionDrivetrainDate,
            ],
            'brake_system' => [
                'last_service_mileage'    => $brakeSystemMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $brakeSystemDate,
            ],
            'suspension_chassis' => [
                'last_service_mileage'    => $suspensionChassisMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $suspensionChassisDate,
            ],
            'fuel_cooling_system' => [
                'last_service_mileage'    => $fuelCoolingSystemMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $fuelCoolingSystemDate,
            ],
            'steering_system' => [
                'last_service_mileage'    => $steeringSystemMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $steeringSystemDate,
            ],
            'electrical_auxiliary_system' => [
                'last_service_mileage'    => $electricalAuxSysMileage,
                'recommended_interval_km' => 20000,
                'last_service_date'       => $electricalAuxSysDate,
            ],
        ];

        // Prepare main data array
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

            // NEW manufacturing date
            'manufacturing_date'      => $manufacturingDate,

            // Updated maintenance items
            'maintenance_items' => $maintenanceItems,
        ];

        // 2) Check if a file was uploaded
        $imageFile = $this->request->getFile('truck_image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $storage = service('firebaseStorage');
            $bucketName = env('FIREBASE_STORAGE_BUCKET');
            $bucket = $storage->getBucket($bucketName);

            $filename  = $imageFile->getRandomName();
            $localPath = WRITEPATH . 'uploads/' . $filename;
            $imageFile->move(WRITEPATH . 'uploads', $filename);

            $firebasePath = 'truck_images/' . $filename;
            $bucket->upload(fopen($localPath, 'r'), [
                'name' => $firebasePath
            ]);

            $storageRef = $bucket->object($firebasePath);
            if ($storageRef->exists()) {
                $info = $bucket->info();
                $bucketRealName = $info['name'];
                $imageUrl = 'https://firebasestorage.googleapis.com/v0/b/'
                          . $bucketRealName
                          . '/o/' . urlencode($firebasePath)
                          . '?alt=media';
                $data['image_url'] = $imageUrl;
            }
        }

        // 3) Update the record in Realtime Database
        $truckModel = new TruckModel();
        $truckModel->updateTruck($truckId, $data);

        session()->setFlashdata('success', 'Truck updated successfully.');
        return redirect()->to(base_url('admin/trucks'));
    }

    /**
     * Delete an existing truck
     */
    public function deleteTruck($truckId)
    {
        $truckModel = new TruckModel();
        $truckModel->deleteTruck($truckId);
        session()->setFlashdata('success', 'Truck deleted successfully.');
        return redirect()->to(base_url('admin/trucks'));
    }

    /**
     * View a truck's details
     */
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

        // Retrieve and merge notification counts.
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);


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
    // public function bookings()
    // {
    //     $bookingModel = new BookingModel();
    //     $data['bookings'] = $bookingModel->getAllBookings() ?? [];

    //     // Load all drivers from Firebase
    //     $firebase       = service('firebase');
    //     $driversRef     = $firebase->getReference('Drivers');
    //     $allDriversData = $driversRef->getValue() ?? [];

    //     // We'll separate them into "drivers" and "conductors"
    //     $driversList = [];
    //     $conductorsList = [];
    //     foreach ($allDriversData as $driverKey => $driverInfo) {
    //         $pos = strtolower($driverInfo['position'] ?? '');
    //         if ($pos === 'driver') {
    //             $driversList[$driverKey] = $driverInfo;
    //         } elseif ($pos === 'conductor') {
    //             $conductorsList[$driverKey] = $driverInfo;
    //         }
    //     }

    //     $data['driversList']    = $driversList;
    //     $data['conductorsList'] = $conductorsList;

    //     // Retrieve and merge notification counts.
    //     $notificationCounts = $this->getNotificationCounts();
    //     $data = array_merge($data, $notificationCounts);
        
    //     return view('admin/bookings', $data);
    // }

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

        // --- Logic copied/adapted from the Maintenance function ---

        // 1) Define the 7 major components and their labels
        $allComponents = [
            'engine_system'             => 'Engine System',
            'transmission_drivetrain'   => 'Transmission & Drivetrain',
            'brake_system'              => 'Brake System',
            'suspension_chassis'        => 'Suspension & Chassis',
            'fuel_cooling_system'       => 'Fuel & Cooling System',
            'steering_system'           => 'Steering System',
            'electrical_auxiliary_system' => 'Electrical & Auxiliary System',
        ];

        // 2) Define intervals for New vs. Old Trucks
        $intervals = [
            'engine_system' => [
                'new' => 5000,
                'old' => 4000,
            ],
            'transmission_drivetrain' => [
                'new' => 20000,
                'old' => 15000,
            ],
            'brake_system' => [
                'new' => 10000,
                'old' => 4000, 
            ],
            'suspension_chassis' => [
                'new' => 5000,
                'old' => 4000, 
            ],
            'fuel_cooling_system' => [
                'new' => 20000,
                'old' => 15000, 
            ],
            'steering_system' => [
                'new' => 20000,
                'old' => 10000, 
            ],
            'electrical_auxiliary_system' => [
                'new' => 10000,
                'old' => 7000,
            ],
        ];

        // ========= Maintenance Alert Logic ========== //
        $firebase = service('firebase');
        $truckRef = $firebase->getReference('Trucks');
        $trucks = $truckRef->getValue() ?? [];
        // var_dump($trucks); // <- TEMPORARY DEBUGGING
        // die();

        $maintenanceAlerts = [];

        // Assuming $trucksData is available and populated correctly
        if (!empty($trucks)) {
            foreach ($trucks as $truckId => $truck) {        
                // Use the array key as the Truck ID if 'id' field isn't present/reliable
                $truckIdDisplay = $truck['id'] ?? $truckId; // Prefer 'id' field if exists, else use key

                // 1) Determine if the truck is New or Old
                $manufacturingDate = $truck['manufacturing_date'] ?? '';
                $yearsOld = 0;
                if (!empty($manufacturingDate)) {
                    try {
                        // Use date_diff for potentially more accurate year difference calculation
                        $today = new \DateTimeImmutable(); // More efficient than date_create('today') inside loop
                        $manuDate = new \DateTimeImmutable($manufacturingDate);
                        $yearsOld = $today->diff($manuDate)->y;
                    } catch (\Exception $e) {
                        // Handle invalid date format if necessary
                        $yearsOld = 0; // Default to 0 if date is invalid
                        // Optionally log the error: error_log("Invalid date format for truck {$truckIdDisplay}: {$manufacturingDate}");
                    }
                }

                $currentMileage = (int)($truck['current_mileage'] ?? 0);

                // "New" if ≤5 years AND mileage < 100k, else "Old"
                // Matches the logic in the Maintenance function
                $isNew = ($yearsOld <= 5 && $currentMileage < 100000);

                // Flag to track if *any* component needs maintenance for the current truck
                $isAnyComponentDue = false;

                // 2) Check each major component for this truck
                foreach ($allComponents as $componentKey => $label) {
                    // Skip if interval data is missing for this component
                    if (!isset($intervals[$componentKey])) {
                        continue;
                    }

                    // Get last service mileage and defect status for *this component*
                    $lastServiceMileage = 0;
                    $isDefective = false;
                    if (isset($truck['maintenance_items'][$componentKey])) {
                        $lastServiceMileage = (int)($truck['maintenance_items'][$componentKey]['last_service_mileage'] ?? 0);
                        $isDefective = !empty($truck['maintenance_items'][$componentKey]['is_defective']);
                    }

                    // Choose the correct mileage interval based on whether the truck is new or old
                    $mileageInterval = $isNew
                        ? $intervals[$componentKey]['new']
                        : $intervals[$componentKey]['old'];

                    // Check if maintenance is due for THIS component:
                    // Either the component is marked as defective OR the mileage since last service exceeds the interval
                    if ($isDefective || (($currentMileage - $lastServiceMileage) >= $mileageInterval)) {
                        // If any component requires maintenance, set the flag...
                        $isAnyComponentDue = true;
                        // ...and we can stop checking other components for this truck (optimization)
                        break; // Exit the inner foreach loop for components
                    }
                } // --- End of the inner loop checking components for the current truck ---

                // After checking all components (or breaking early), add ONE generic alert if the flag was set
                if ($isAnyComponentDue) {
                    // Add a single alert for the truck without listing specific components
                    $maintenanceAlerts[] = "$truckIdDisplay needs maintenance before booking.";
                }
            }
        }

        // Assign the generated alerts to the $data array (if used in a controller context)
        $data['maintenanceAlerts'] = $maintenanceAlerts;
        
        // echo '<pre>';
        // var_dump($maintenanceAlerts);
        // echo '</pre>';
        // die();

        // Retrieve and merge notification counts
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);
        return view('admin/bookings', $data);
    }

    // Update booking status (approval/rejection, etc.), or reassign driver/conductor
    // Update booking status (approval/rejection, etc.), or reassign driver/conductor
    // public function updateBookingStatus()
    // {
    //     $bookingId   = $this->request->getPost('booking_id');
    //     $status      = $this->request->getPost('status');  // e.g., "approved", "rejected", "in-transit", etc.
    //     $distance    = $this->request->getPost('distance');
    //     $driverId    = $this->request->getPost('driver');
    //     $conductorId = $this->request->getPost('conductor'); // Currently not used for conflict, but can be extended
    //     $truckId     = $this->request->getPost('truck_id');

    //     // Initialize update data with status and distance.
    //     $updateData = [
    //         'status'   => $status,
    //         'distance' => $distance,
    //     ];

    //     // Get Firebase service instance
    //     $firebase = service('firebase');
    //     $bookingRef = $firebase->getReference('Bookings/' . $bookingId);
    //     $existingBooking = $bookingRef->getValue();

    //     // Prevent changes if booking is already completed or rejected.
    //     if ($existingBooking && in_array($existingBooking['status'], ['completed', 'rejected'])) {
    //         session()->setFlashdata('error', 'Cannot update a booking that is already completed or rejected.');
    //         return redirect()->to(base_url('admin/bookings'));
    //     }

    //     $bookingModel = new BookingModel();
    //     $allBookings  = $bookingModel->getAllBookings(); // to detect conflicts
    //     $driversRef   = $firebase->getReference('Drivers');

    //     // Define active statuses for conflict checking.
    //     $activeStatuses = ['approved', 'in-transit'];

    //     // If the new status is active...
    //     if (in_array($status, $activeStatuses)) {
    //         // And a new driver is selected (or remains the same driver)...
    //         if (!empty($driverId)) {
    //             if ($allBookings && is_array($allBookings)) {
    //                 foreach ($allBookings as $b) {
    //                     if (!is_array($b)) continue; // skip invalid
    //                     // If same driver is found in a different booking that is active,
    //                     // we block the status update for this booking.
    //                     if (
    //                         isset($b['driver_id']) &&
    //                         $b['driver_id'] === $driverId &&
    //                         in_array($b['status'], $activeStatuses) &&
    //                         ($b['booking_id'] != $bookingId) // skip the same booking
    //                     ) {
    //                         session()->setFlashdata('error', 'Driver is currently assigned to an ongoing booking (#'.$b['booking_id'].').');
    //                         return redirect()->to(base_url('admin/bookings'));
    //                     }
    //                 }
    //             }
    //         } else {
    //             // If no driver is selected at all, you may choose to block or allow that.
    //             // For example:
    //             session()->setFlashdata('error', 'Driver is currently taking another booking.');
    //             return redirect()->to(base_url('admin/bookings'));
    //         }
    //     }

    //     // 2) If a new driver is selected, fetch the driver data and reassign details.
    //     if (!empty($driverId)) {
    //         $driverData = $driversRef->getChild($driverId)->getValue();
    //         if ($driverData) {
    //             $fullName = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
    //             $updateData['driver_name'] = $fullName;
    //             $updateData['driver_id']   = $driverId;

    //             // Automatically assign a conductor partner based on the driver's truck assignment.
    //             $allDrivers = $driversRef->getValue() ?? [];
    //             foreach ($allDrivers as $key => $info) {
    //                 if (
    //                     strtolower(trim($info['position'] ?? '')) === 'conductor' &&
    //                     !empty($info['truck_assigned']) &&
    //                     $info['truck_assigned'] === ($driverData['truck_assigned'] ?? '')
    //                 ) {
    //                     $updateData['conductor_id']   = $key;
    //                     $updateData['conductor_name'] = trim(($info['first_name'] ?? '') . ' ' . ($info['last_name'] ?? ''));
    //                     break;
    //                 }
    //             }

    //             // Update truck details based on the driver's assigned truck.
    //             if (!empty($driverData['truck_assigned'])) {
    //                 $newTruckId = $driverData['truck_assigned'];
    //                 $truckData  = $firebase->getReference('Trucks/'.$newTruckId)->getValue();
    //                 if ($truckData) {
    //                     $updateData['truck_id']      = $truckData['truck_id'] ?? $newTruckId;
    //                     $updateData['truck_model']   = $truckData['truck_model'] ?? '';
    //                     $updateData['license_plate'] = $truckData['plate_number'] ?? '';
    //                     $updateData['type_of_truck'] = $truckData['truck_type'] ?? '';
    //                 }
    //             }
    //         }
    //     }

    //     // 3) If admin changed the truck manually (and no driver update occurred), update truck details.
    //     if (!empty($truckId) && !isset($updateData['truck_id'])) {
    //         $truckData = $firebase->getReference('Trucks/' . $truckId)->getValue();
    //         if ($truckData) {
    //             $updateData['truck_id']      = $truckData['truck_id'] ?? $truckId;
    //             $updateData['truck_model']   = $truckData['truck_model'] ?? '';
    //             $updateData['license_plate'] = $truckData['plate_number'] ?? '';
    //             $updateData['type_of_truck'] = $truckData['truck_type'] ?? '';
    //         }
    //     }

    //     // 4) Update the booking with the final data.
    //     $bookingModel->updateBooking($bookingId, $updateData);

    //     // 5) If the booking is marked as "completed", update the truck's current mileage.
    //     if ($status === 'completed' && isset($updateData['truck_id'])) {
    //         $truckRef = $firebase->getReference('Trucks/' . $updateData['truck_id']);
    //         $truckData = $truckRef->getValue();
    //         if ($truckData) {
    //             $currentMileage = isset($truckData['current_mileage']) ? floatval($truckData['current_mileage']) : 0;
    //             $newMileage = $currentMileage + floatval($distance);
    //             // Update the truck's current mileage.
    //             $truckRef->update(['current_mileage' => $newMileage]);
    //         }
    //     }

    //     // 6) Persistent Notification:
    //     // Instead of using FCM, store a notification record in Firebase that the client can view upon login.
    //     // Assume the booking contains a "client_id" field.
    //     if (!empty($existingBooking['client_id'])) {
    //         $clientId = $existingBooking['client_id'];
    //         $notificationData = [
    //             'message'    => "Your booking #{$bookingId} status has been updated to " . ucfirst($status) . ".",
    //             'booking_id' => $bookingId,
    //             'status'     => $status,
    //             'timestamp'  => date('c'),
    //             'read'       => false  // This flag indicates whether the user has dismissed the notification.
    //         ];
    //         // Save the notification in Firebase under Notifications/{clientId}
    //         $firebase->getReference("Notifications/{$clientId}")->push($notificationData);
    //     }

    //     session()->setFlashdata('success', 'Booking #' . $bookingId . ' updated successfully!');
    //     return redirect()->to(base_url('admin/bookings'));
    // }

    public function updateBookingStatus()
    {
        // Get all POST data
        $postData = $this->request->getPost();
        
        // Required fields
        $bookingId   = $postData['booking_id'] ?? null;
        $status      = $postData['status'] ?? null;
        $distance    = $postData['distance'] ?? 0;
        $driverId    = $postData['driver'] ?? null;
        $truckId     = $postData['truck_id'] ?? null;
        $remarks     = $postData['remarks'] ?? '';
        
        // Validate required fields
        if (empty($bookingId)) {
            session()->setFlashdata('error', 'Booking ID is required.');
            return redirect()->to(base_url('admin/bookings'));
        }

        // Initialize Firebase
        $firebase = service('firebase');
        $bookingRef = $firebase->getReference('Bookings/' . $bookingId);
        $existingBooking = $bookingRef->getValue();

        // Check if booking exists
        if (!$existingBooking) {
            session()->setFlashdata('error', 'Booking not found.');
            return redirect()->to(base_url('admin/bookings'));
        }

        // Prevent changes if booking is already completed or rejected
        if (in_array($existingBooking['status'] ?? '', ['completed', 'rejected'])) {
            session()->setFlashdata('error', 'Cannot update a booking that is already completed or rejected.');
            return redirect()->to(base_url('admin/bookings'));
        }

        // Initialize update data
        $updateData = [
            'status'   => $status,
            'distance' => (float)$distance,
            'remarks'  => $remarks,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Initialize models
        $bookingModel = new BookingModel();
        $allBookings  = $bookingModel->getAllBookings();
        $driversRef   = $firebase->getReference('Drivers');

                // Define active statuses for conflict checking
        $activeStatuses = ['approved', 'in-transit'];

        // Check for driver conflicts if status is active
        if (in_array($status, $activeStatuses)) {
            if (!empty($driverId)) {
                if ($allBookings && is_array($allBookings)) {
                    foreach ($allBookings as $b) {
                        if (!is_array($b)) continue;
                        
                        if (isset($b['driver_id']) &&
                            $b['driver_id'] === $driverId &&
                            in_array($b['status'], $activeStatuses) &&
                            ($b['booking_id'] != $bookingId)
                        ) {
                            session()->setFlashdata('error', 'Driver is currently assigned to an ongoing booking (#'.$b['booking_id'].').');
                            return redirect()->to(base_url('admin/bookings'));
                        }
                    }
                }
            } else {
                session()->setFlashdata('error', 'No driver selected for this booking.');
                return redirect()->to(base_url('admin/bookings'));
            }
        }

        // Handle driver reassignment
        if (!empty($driverId)) {
            $driverData = $driversRef->getChild($driverId)->getValue();
            if ($driverData) {
                // Update driver info
                $fullName = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                $updateData['driver_name'] = $fullName;
                $updateData['driver_id']   = $driverId;

                // Find and assign conductor partner
                $allDrivers = $driversRef->getValue() ?? [];
                foreach ($allDrivers as $key => $info) {
                    if (
                        strtolower(trim($info['position'] ?? '')) === 'conductor' &&
                        !empty($info['truck_assigned']) &&
                        $info['truck_assigned'] === ($driverData['truck_assigned'] ?? '')
                    ) {
                        $updateData['conductor_id']   = $key;
                        $updateData['conductor_name'] = trim(($info['first_name'] ?? '') . ' ' . ($info['last_name'] ?? ''));
                        break;
                    }
                }

                // Update truck details based on driver's assignment
                if (!empty($driverData['truck_assigned'])) {
                    $newTruckId = $driverData['truck_assigned'];
                    $truckData  = $firebase->getReference('Trucks/'.$newTruckId)->getValue();
                    if ($truckData) {
                        $updateData['truck_id']      = $truckData['truck_id'] ?? $newTruckId;
                        $updateData['truck_model']   = $truckData['truck_model'] ?? '';
                        $updateData['license_plate'] = $truckData['plate_number'] ?? '';
                        $updateData['type_of_truck'] = $truckData['truck_type'] ?? '';
                    }
                }
            }
        }

        // Handle manual truck assignment
        if (!empty($truckId) && !isset($updateData['truck_id'])) {
            $truckData = $firebase->getReference('Trucks/' . $truckId)->getValue();
            if ($truckData) {
                $updateData['truck_id']      = $truckData['truck_id'] ?? $truckId;
                $updateData['truck_model']   = $truckData['truck_model'] ?? '';
                $updateData['license_plate'] = $truckData['plate_number'] ?? '';
                $updateData['type_of_truck'] = $truckData['truck_type'] ?? '';
            }
        }

        // Initialize remarks history
        if (!isset($existingBooking['remarks_history'])) {
            $existingBooking['remarks_history'] = [];
        }

        // Add to remarks history if remarks changed
        if (!empty($remarks) && $remarks !== ($existingBooking['remarks'] ?? '')) {
            $updateData['remarks_history'] = array_merge(
                $existingBooking['remarks_history'],
                [
                    [
                        'remarks' => $remarks,
                        'updated_by' => session()->get('username') ?? 'Admin',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]
                ]
            );
        }

        // Update the booking
        $bookingModel->updateBooking($bookingId, $updateData);

        // Update truck mileage if completed
        if ($status === 'completed' && isset($updateData['truck_id'])) {
            $truckRef = $firebase->getReference('Trucks/' . $updateData['truck_id']);
            $truckData = $truckRef->getValue();
            if ($truckData) {
                $currentMileage = isset($truckData['current_mileage']) ? floatval($truckData['current_mileage']) : 0;
                $newMileage = $currentMileage + floatval($distance);
                $truckRef->update(['current_mileage' => $newMileage]);
            }
        }

        // Create notification for client
        if (!empty($existingBooking['client_id'])) {
            $clientId = $existingBooking['client_id'];
            $notificationData = [
                'message'    => "Your booking #{$bookingId} status has been updated to " . ucfirst($status) . ".",
                'booking_id' => $bookingId,
                'status'     => $status,
                'timestamp'  => date('c'),
                'read'       => false
            ];
            $firebase->getReference("Notifications/{$clientId}")->push($notificationData);
        }

        session()->setFlashdata('success', 'Booking #' . $bookingId . ' updated successfully!');
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

        // Retrieve and merge notification counts.
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);
 
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
        // 1) Call the StorageScan logic.
        $storageScan = new \App\Controllers\StorageScan();
        $storageScan->index();
        // This will create any missing "Reports" records in Realtime DB.

        // 2) Now fetch all "Reports" to display.
        $db = Services::firebase();
        $reportsRef = $db->getReference('Reports');
        $snapshot = $reportsRef->getSnapshot();
        $reports = $snapshot->getValue() ?? [];

        // Optionally sort them by their "R000001" style keys.
        uksort($reports, function($a, $b) {
            return strnatcmp($a, $b);
        });

        // Build the initial data array.
        $data = [
            'reports' => $reports,
        ];

        // Retrieve and merge notification counts.
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);

        // 3) Pass everything to your view.
        return view('admin/reports_management', $data);
    }


    // ================== GEOLOCATION MODULE ===================  //

    public function geolocation()
    {
        // Get only drivers with valid geolocation fields
        $drivers = $this->driverModel->getDriversWithLocation();

        // Build the initial data array
        $data = [
            'drivers' => $drivers
        ];

        // Retrieve and merge notification counts
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);

        return view('admin/geolocation', $data);
    }

    // ================== MAINTENANCE MODULE ===================  //
    // public function Maintenance()
    // {
    //     // 1) Get Firebase Realtime Database instance
    //     $db = service('firebase');

    //     // 2) Fetch all trucks from your "Trucks" node
    //     $trucksRef = $db->getReference('Trucks');
    //     $snapshot = $trucksRef->getSnapshot();

    //     if (!$snapshot->exists()) {
    //         // If no data in 'Trucks' node, pass empty arrays
    //         return view('admin/maintenance', [
    //             'totalTrucks'      => 0,
    //             'dueTrucks'        => [],
    //             'chartData'        => [],
    //             'componentTrucks'  => [],
    //             'allComponents'    => [],
    //         ]);
    //     }

    //     // Convert the snapshot into an associative array and natural sort by truck ID
    //     $trucksData = $snapshot->getValue();
    //     uksort($trucksData, 'strnatcmp');

    //     // ----------------------------------
    //     // 7 major components and their labels
    //     // ----------------------------------
    //     $allComponents = [
    //         'engine_system'               => 'Engine System',
    //         'transmission_drivetrain'     => 'Transmission & Drivetrain',
    //         'brake_system'                => 'Brake System',
    //         'suspension_chassis'          => 'Suspension & Chassis',
    //         'fuel_cooling_system'         => 'Fuel & Cooling System',
    //         'steering_system'             => 'Steering System',
    //         'electrical_auxiliary_system' => 'Electrical & Auxiliary System',
    //     ];

    //     // --------------------------------------
    //     // Define intervals for New vs. Old Trucks
    //     // (Feel free to adjust as needed)
    //     // --------------------------------------
    //     $intervals = [
    //         'engine_system' => [
    //             'new' => 5000, // e.g. every 5,000 km
    //             'old' => 4000, // e.g. every 4,000 km
    //         ],
    //         'transmission_drivetrain' => [
    //             'new' => 20000, 
    //             'old' => 15000,
    //         ],
    //         'brake_system' => [
    //             'new' => 10000,
    //             'old' => 4000,
    //         ],
    //         'suspension_chassis' => [
    //             'new' => 5000,
    //             'old' => 4000,
    //         ],
    //         'fuel_cooling_system' => [
    //             'new' => 20000,
    //             'old' => 15000,
    //         ],
    //         'steering_system' => [
    //             'new' => 20000,
    //             'old' => 10000,
    //         ],
    //         'electrical_auxiliary_system' => [
    //             'new' => 10000,
    //             'old' => 7000,
    //         ],
    //     ];

    //     // We'll count how many trucks are overdue for each component
    //     $componentCounts = array_fill_keys(array_keys($allComponents), 0);

    //     // We'll also track which trucks need each component (for the chart modal)
    //     $componentTrucks = array_fill_keys(array_keys($allComponents), []);

    //     // Array for final output
    //     $dueTrucks = [];
    //     $totalTrucks = 0;

    //     foreach ($trucksData as $truckId => $truck) {
    //         $totalTrucks++;

    //         // 1) Determine if the truck is Old or New
    //         $manufacturingDate = $truck['manufacturing_date'] ?? '';
    //         $yearsOld = 0;
    //         if (!empty($manufacturingDate)) {
    //             try {
    //                 // Ensure the date is valid before calculating
    //                 $mfgTimestamp = strtotime($manufacturingDate);
    //                 if ($mfgTimestamp !== false) {
    //                     $yearsOld = date('Y') - date('Y', $mfgTimestamp);
    //                 }
    //             } catch (\Exception $e) {
    //                 $yearsOld = 0; // Handle potential date format issues
    //                 log_message('error', 'Invalid manufacturing date format for truck ' . $truckId . ': ' . $manufacturingDate);
    //             }
    //         }

    //         $currentMileage = (int)($truck['current_mileage'] ?? 0);
    //         $isNew = ($yearsOld <= 5 && $currentMileage < 100000);
    //         $condition = $isNew ? 'New' : 'Old';

    //         $dueComponents = [];
    //         $truckComponentDetails = []; // Store details for *all* components for this truck

    //         // 2) Check each of the major maintenance items
    //         foreach ($allComponents as $componentKey => $label) {
    //             $lastServiceMileage = 0;
    //             $lastServiceDate = 'N/A';
    //             $isDefective = false;
    //             $componentData = $truck['maintenance_items'][$componentKey] ?? []; // Get component data or empty array

    //             $lastServiceMileage = (int)($componentData['last_service_mileage'] ?? 0);
    //             $lastServiceDate = $componentData['last_service_date'] ?? 'N/A'; // Get last service date
    //             $isDefective = !empty($componentData['is_defective']); // Check if defective flag is set and not empty/false

    //             // Choose the correct interval
    //             $mileageInterval = $isNew ? $intervals[$componentKey]['new'] : $intervals[$componentKey]['old'];

    //             // Determine if component is currently due
    //             $isDueByMileage = ($currentMileage - $lastServiceMileage) >= $mileageInterval;
    //             $isCurrentlyDue = $isDefective || $isDueByMileage;

    //              // Store details for this component regardless of due status
    //              $truckComponentDetails[$componentKey] = [
    //                  'label' => $label,
    //                  'last_service_mileage' => $lastServiceMileage,
    //                  'last_service_date' => $lastServiceDate,
    //                  'is_defective' => $isDefective,
    //                  'is_due_by_mileage' => $isDueByMileage,
    //                  'is_currently_due' => $isCurrentlyDue,
    //                  'required_interval' => $mileageInterval // Store the interval used
    //                  // 'historical_due_count' => $componentData['historical_due_count'] ?? 0 // Placeholder: Requires data structure change
    //              ];


    //             if ($isCurrentlyDue) {
    //                 $dueComponents[] = $componentKey;
    //                 $componentCounts[$componentKey]++;
    //                 $componentTrucks[$componentKey][] = [
    //                     'truck_id'    => $truckId,
    //                     'truck_model' => $truck['truck_model'] ?? 'N/A',
    //                 ];
    //             }
    //         }

    //          // Fetch booking count for this truck
    //          $bookingCount = $this->getTruckBookingCount($truckId, $db);


    //          // Store all necessary data for the JS Modal
    //         $condition = $isNew ? 'New' : 'Old';
    //         $allTrucksDataForJs[$truckId] = [
    //             'truckId'             => $truckId,
    //             'truckModel'          => $truck['truck_model'] ?? 'N/A',
    //             'manufacturingDate'   => $manufacturingDate,
    //             'currentMileage'      => $currentMileage,
    //             'lastInspectionDate'  => $truck['last_inspection_date'] ?? 'N/A', // Assuming this is overall inspection
    //             'lastInspectionMileage' => $truck['last_inspection_mileage'] ?? 'N/A', // Assuming this is overall inspection
    //             'condition'           => $condition,
    //             'yearsOld'            => $yearsOld,
    //             'componentDetails'    => $truckComponentDetails, // Pass details for ALL components
    //             'bookingCount'        => $bookingCount,
    //              //'rawTruckData'       => $truck // Optionally pass all raw data if needed
    //         ];

    //         // If truck has any due components, add it to $dueTrucks
    //         if (!empty($dueComponents)) {
    //             $condition = $isNew ? 'New' : 'Old';
    //             $dueTrucks[] = [
    //                 'truckId'          => $truckId,
    //                 'truckModel'       => $truck['truck_model'] ?? '',
    //                 'manufacturingDate'=> $manufacturingDate,
    //                 'details'          => $truck,
    //                 'dueComponents'    => $dueComponents,
    //                 'yearsOld'         => $yearsOld,
    //                 'lastServiceMileage'=> $truck['last_inspection_mileage'] ?? 'N/A',
    //                 'lastInspectionDate'  => $truck['last_inspection_date'] ?? 'N/A',
    //                 'currentMileage'      => $currentMileage,
    //                 'condition'        => $condition,
    //             ];
    //         }
    //     }

    //     // Build chart data for Chart.js
    //     $labels     = array_values($allComponents);
    //     $dataValues = [];
    //     foreach ($allComponents as $key => $label) {
    //         $dataValues[] = $componentCounts[$key];
    //     }

    //     $chartData = [
    //         'labels'   => $labels,
    //         'datasets' => [[
    //             'label' => 'Components Due for Inspection',
    //             'data'  => $dataValues,
    //         ]]
    //     ];

    //     // Build the data array for the view
    //     $data = [
    //         'totalTrucks'     => $totalTrucks,
    //         'dueTrucks'       => $dueTrucks,
    //         'chartData'       => $chartData,
    //         'componentTrucks' => $componentTrucks,
    //         'allComponents'   => $allComponents,
    //         'allTrucksDataForJs' => $allTrucksDataForJs,
    //     ];

    //     // Retrieve and merge notification counts.
    //     $notificationCounts = $this->getNotificationCounts();
    //     $data = array_merge($data, $notificationCounts);

    //     // Pass everything to the view.
    //     return view('admin/maintenance', $data);

    // }

    // ============================================================
    // ADD THIS HELPER METHOD INSIDE THE AdminController CLASS
    // ============================================================
    private function getTruckBookingData($db)
    {
        // IMPORTANT: Adjust 'Bookings' if your Firebase node has a different name!
        $bookingsRef = $db->getReference('Bookings');
        try {
            $snapshot = $bookingsRef->getSnapshot();
            if ($snapshot->exists()) {
                return $snapshot->getValue();
            }
        } catch (\Exception $e) {
            // Log the error or handle it appropriately
            log_message('error', 'Firebase Error fetching Bookings: ' . $e->getMessage());
            return []; // Return empty on error
        }
        return []; // Return empty if node doesn't exist
    }
    // ============================================================

    public function Maintenance()
    {
        // 1) Get Firebase Realtime Database instance
        $db = service('firebase');

        // 2) Fetch all trucks from your "Trucks" node
        $trucksRef = $db->getReference('Trucks');
        $snapshot = $trucksRef->getSnapshot();

        // --- Default empty data for view in case of no trucks ---
        $emptyData = [
            'totalTrucks'           => 0,
            'dueTrucks'             => [],
            'componentChartData'    => ['labels' => [], 'datasets' => []], // Renamed
            'distanceChartData'     => ['labels' => [], 'datasets' => []], // New
            'componentTrucks'       => [],
            'allComponents'         => [],
            'allTrucksDataForJs'    => [],
            'trucksWithBookings'    => [], // List of trucks that actually have bookings
        ];
        // Merge default notification counts
        $notificationCounts = $this->getNotificationCounts();
        $emptyData = array_merge($emptyData, $notificationCounts);

        if (!$snapshot->exists()) {
            // If no data in 'Trucks' node, pass empty arrays/defaults
            return view('admin/maintenance', $emptyData);
        }

        // Convert the snapshot into an associative array and natural sort by truck ID
        $trucksData = $snapshot->getValue();
        uksort($trucksData, 'strnatcmp');

        // Fetch all booking data once
        $allBookings = $this->getTruckBookingData($db);

        // ----------------------------------
        // Component definitions (Keep as is)
        // ----------------------------------
        $allComponents = [
            'engine_system'               => 'Engine System',
            'transmission_drivetrain'     => 'Transmission & Drivetrain',
            'brake_system'                => 'Brake System',
            'suspension_chassis'          => 'Suspension & Chassis',
            'fuel_cooling_system'         => 'Fuel & Cooling System',
            'steering_system'             => 'Steering System',
            'electrical_auxiliary_system' => 'Electrical & Auxiliary System',
        ];
        $intervals = [
            'engine_system'               => ['new' => 5000,  'old' => 4000],
            'transmission_drivetrain'     => ['new' => 20000, 'old' => 15000],
            'brake_system'                => ['new' => 10000, 'old' => 4000],
            'suspension_chassis'          => ['new' => 5000,  'old' => 4000],
            'fuel_cooling_system'         => ['new' => 20000, 'old' => 15000],
            'steering_system'             => ['new' => 20000, 'old' => 10000],
            'electrical_auxiliary_system' => ['new' => 10000, 'old' => 7000],
        ];

        // --- Initialize data structures ---
        $componentCounts = array_fill_keys(array_keys($allComponents), 0);
        $componentTrucks = array_fill_keys(array_keys($allComponents), []);
        $dueTrucks = [];
        $totalTrucks = 0;
        $allTrucksDataForJs = []; // For the truck detail modal

        // --- Initialize structures for Distance Chart ---
        $truckDistanceCounts = []; // Structure: ['truckId' => ['short' => 0, 'medium' => 0, 'long' => 0]]

        // --- Process Bookings FIRST to get distance counts per truck ---
        foreach ($allBookings as $bookingId => $booking) {
            if (isset($booking['truck_id']) && isset($booking['distance'])) {
                $truckId = $booking['truck_id'];
                $distance = (float) $booking['distance']; // Ensure numeric

                // Initialize truck if not seen before
                if (!isset($truckDistanceCounts[$truckId])) {
                    $truckDistanceCounts[$truckId] = ['short' => 0, 'medium' => 0, 'long' => 0];
                }

                // Categorize distance
                if ($distance < 40) {
                    $truckDistanceCounts[$truckId]['short']++;
                } elseif ($distance <= 200) {
                    $truckDistanceCounts[$truckId]['medium']++;
                } else { // > 200
                    $truckDistanceCounts[$truckId]['long']++;
                }
            }
        }
        // Optional: Sort trucks with bookings by Truck ID (natural sort)
        uksort($truckDistanceCounts, 'strnatcmp');
        $trucksWithBookings = array_keys($truckDistanceCounts); // Get the naturally sorted list


        // --- Process Each Truck for Maintenance AND Modal Data ---
        foreach ($trucksData as $truckId => $truck) {
            $totalTrucks++;

            // 1) Determine if the truck is Old or New (Keep as is)
            $manufacturingDate = $truck['manufacturing_date'] ?? '';
            $yearsOld = 0;
            if (!empty($manufacturingDate)) {  $yearsOld = date('Y') - date('Y', strtotime($manufacturingDate));}
            $currentMileage = (int)($truck['current_mileage'] ?? 0);
            $isNew = ($yearsOld <= 5 && $currentMileage < 100000);
            $condition = $isNew ? 'New' : 'Old';

            $dueComponents = [];
            $truckComponentDetails = [];

            // 2) Check each major maintenance item (Keep as is)
            foreach ($allComponents as $componentKey => $label) {
                $lastServiceMileage = 0;
                $lastServiceDate = 'N/A';
                $isDefective = false;
                $componentData = $truck['maintenance_items'][$componentKey] ?? [];

                $lastServiceMileage = (int)($componentData['last_service_mileage'] ?? 0);
                $lastServiceDate = $componentData['last_service_date'] ?? 'N/A';
                $isDefective = !empty($componentData['is_defective']);

                $mileageInterval = $isNew ? $intervals[$componentKey]['new'] : $intervals[$componentKey]['old'];
                $isDueByMileage = ($currentMileage - $lastServiceMileage) >= $mileageInterval;
                $isCurrentlyDue = $isDefective || $isDueByMileage;

                // Store details for this component regardless of due status (Keep as is)
                $truckComponentDetails[$componentKey] = [
                    'label'                => $label,
                    'last_service_mileage' => $lastServiceMileage,
                    'last_service_date'    => $lastServiceDate,
                    'is_defective'         => $isDefective,
                    'is_due_by_mileage'    => $isDueByMileage,
                    'is_currently_due'     => $isCurrentlyDue,
                    'required_interval'    => $mileageInterval
                ];

                if ($isCurrentlyDue) {
                    $dueComponents[] = $componentKey;
                    $componentCounts[$componentKey]++;
                    $componentTrucks[$componentKey][] = [
                        'truck_id'    => $truckId,
                        'truck_model' => $truck['truck_model'] ?? 'N/A',
                    ];
                }
            }

            // Fetch booking count (individual truck) - Reuse existing if needed, or get from processed data
            // If getTruckBookingCount queries DB again, it's less efficient. Better to use $truckDistanceCounts
            $bookingCount = 0;
            if(isset($truckDistanceCounts[$truckId])) {
                $bookingCount = array_sum($truckDistanceCounts[$truckId]);
            }
            // $bookingCount = $this->getTruckBookingCount($truckId, $db); // Less efficient if it queries again


            // Store all necessary data for the JS Modal (Keep structure, update booking count source)
            $allTrucksDataForJs[$truckId] = [
                'truckId'               => $truckId,
                'truckModel'            => $truck['truck_model'] ?? 'N/A',
                'manufacturingDate'     => $manufacturingDate,
                'currentMileage'        => $currentMileage,
                'lastInspectionDate'    => $truck['last_inspection_date'] ?? 'N/A',
                'lastInspectionMileage' => $truck['last_inspection_mileage'] ?? 'N/A',
                'condition'             => $condition,
                'yearsOld'              => $yearsOld,
                'componentDetails'      => $truckComponentDetails,
                'bookingCount'          => $bookingCount, // Use calculated count
            ];

            // If truck has any due components, add it to $dueTrucks (Keep as is)
            if (!empty($dueComponents)) {
                $dueTrucks[] = [
                    'truckId'            => $truckId,
                    'truckModel'         => $truck['truck_model'] ?? '',
                    'manufacturingDate'  => $manufacturingDate,
                    'details'            => $truck, // Be mindful of passing too much data if not needed
                    'dueComponents'      => $dueComponents,
                    'yearsOld'           => $yearsOld,
                    'lastServiceMileage' => $truck['last_inspection_mileage'] ?? 'N/A',
                    'lastInspectionDate' => $truck['last_inspection_date'] ?? 'N/A',
                    'currentMileage'     => $currentMileage,
                    'condition'          => $condition,
                ];
            }
        } // End foreach ($trucksData)

        // --- Build Component Chart data ---
        $componentLabels = array_values($allComponents);
        $componentDataValues = [];
        foreach ($allComponents as $key => $label) {
            $componentDataValues[] = $componentCounts[$key];
        }
        $componentChartData = [ // Renamed variable
            'labels'   => $componentLabels,
            'datasets' => [[
                'label' => 'Components Due for Inspection',
                'data'  => $componentDataValues,
                // Colors will be added in JS
            ]]
        ];


        // --- Build Distance Chart data ---
        $distanceLabels = $trucksWithBookings; // Use the sorted list of trucks with bookings
        $shortDistanceData = [];
        $mediumDistanceData = [];
        $longDistanceData = [];

        foreach($distanceLabels as $truckId) {
            $counts = $truckDistanceCounts[$truckId];
            $shortDistanceData[] = $counts['short'];
            $mediumDistanceData[] = $counts['medium'];
            $longDistanceData[] = $counts['long'];
        }

        $distanceChartData = [ // New variable
            'labels' => $distanceLabels, // Truck IDs
            'datasets' => [
                [
                    'label' => 'Short Distance (< 40km)',
                    'data' => $shortDistanceData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.7)', // Blue
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Medium Distance (40-200km)',
                    'data' => $mediumDistanceData,
                    'backgroundColor' => 'rgba(255, 206, 86, 0.7)', // Yellow
                    'borderColor' => 'rgba(255, 206, 86, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Long Distance (> 200km)',
                    'data' => $longDistanceData,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.7)', // Red
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];

        // --- Build the final data array for the view ---
        $data = [
            'totalTrucks'           => $totalTrucks,
            'dueTrucks'             => $dueTrucks,
            'componentChartData'    => $componentChartData,    // Pass component chart data
            'distanceChartData'     => $distanceChartData,     // Pass distance chart data
            'componentTrucks'       => $componentTrucks,     // For component modal (chart click)
            'allComponents'         => $allComponents,
            'allTrucksDataForJs'    => $allTrucksDataForJs,  // For truck detail modal
            'trucksWithBookings'    => $trucksWithBookings,  // Pass list of trucks with bookings
        ];

        // Retrieve and merge notification counts.
        // $notificationCounts = $this->getNotificationCounts(); // Already done at the beginning
        $data = array_merge($data, $notificationCounts);

        // Pass everything to the view.
        return view('admin/maintenance', $data);
    }

    

    

}
