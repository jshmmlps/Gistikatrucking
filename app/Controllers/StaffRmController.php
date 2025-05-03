<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use App\Models\ReportModel;
use CodeIgniter\Controller;
use Config\Services;

class StaffRmController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        // Load session and check resource manager authorization
        $session = session();
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'resource manager') {
            $session->setFlashdata('error', 'No authorization.');
            redirect()->to(base_url('login'))->send();
            exit; // Stop further execution
        }
        $this->userModel = new UserModel();
        $this->driverModel = new DriverModel();
        $this->reportModel = new ReportModel();
    }

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
    


    // public function dashboard()
    // {
    //     return view('resource_manager/dashboard');
    // }

    /**
     * Display the dashboard.
     */
    public function dashboard()
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
    
        return view('resource_manager/dashboard', $data);
    }

    // ----- User Management Methods -----
    /**
     * Display the resource manager's profile page.
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
    
            // Retrieve notification counts
            $notificationCounts = $this->getNotificationCounts();
    
            // Merge the user data with the notification counts
            $data = array_merge(['user' => $userData], $notificationCounts);
    
            return view('resource_manager/profile', $data);
        }
    
        // Optionally handle the case where no user is found (e.g. redirect or show error)
        $session->setFlashdata('error', 'User not found');
        return redirect()->to('/resource/dashboard');
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
        return redirect()->to('resource/profile');
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
            return redirect()->to('resource/profile');
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
            return redirect()->to('resource/profile');
        } else {
            $session->setFlashdata('error', 'Failed to upload image. Please try again.');
            return redirect()->to('resource/profile');
        }
    }

    // ============== TRUCK MANAGEMENT MODULE ===================  //
    public function trucks()
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
        
        return view('resource_manager/truck_management', $data);
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
        return redirect()->to(base_url('resource/trucks'));
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
        return redirect()->to(base_url('resource/trucks'));
    }

    /**
     * Delete an existing truck
     */
    public function deleteTruck($truckId)
    {
        $truckModel = new TruckModel();
        $truckModel->deleteTruck($truckId);
        session()->setFlashdata('success', 'Truck deleted successfully.');
        return redirect()->to(base_url('resource/trucks'));
    }

    /**
     * View a truck's details
     */
    public function viewTruck($truckId)
    {
        $truckModel = new TruckModel();
        $data['truck'] = $truckModel->getTruck($truckId);
        return view('resource_manager/truck_detail', $data);
    }


    // ================== GEOLOCATION MODULE ===================  //

    public function geolocation()
    {
        // Get only drivers with valid geolocation fields
        $drivers = $this->driverModel->getDriversWithLocation();

        $data = [
            'drivers' => $drivers,
        ];

        // Retrieve and merge notification counts.
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);
        
        return view('resource_manager/geolocation', $data);
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
    //         return view('resource_manager/maintenance', [
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
    //     return view('resource_manager/maintenance', $data);

    // }
    
    // ============================================================
    // ADD THIS HELPER METHOD INSIDE THE CLASS
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
            return view('resource_manager/maintenance', $emptyData);
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
            if (!empty($manufacturingDate)) { /* ... keep date calculation logic ... */ }
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
        return view('resource_manager/maintenance', $data);
    }

      // ================== REPORT MANAGEMENT MODULE ===================  //
    
      /**
     * Show the Maintenance Reports page.
     * This includes a form to create a new maintenance report
     * and optionally a list/table of existing reports.
     */
    /**
     * Show the Maintenance Reports page (only "Maintenance Report" type).
     */
    public function report()
    {
        // 1) Fetch all "Maintenance Report" type records from the "Reports" node
        //    We'll do that by retrieving all and filtering, or adding a data structure check.
        $db = Services::firebase();
        $reportsRef = $db->getReference('Reports');
        $snapshot = $reportsRef->getSnapshot();

        $allReports = $snapshot->exists() ? $snapshot->getValue() : [];
        $maintenanceReports = [];

        if (!empty($allReports) && is_array($allReports)) {
            foreach ($allReports as $key => $r) {
                if (isset($r['report_type']) && $r['report_type'] === 'Maintenance Report') {
                    $maintenanceReports[$key] = $r;
                }
            }
        }

        // 2) Also fetch trucks if we need them for the dropdown
        $trucksRef = $db->getReference('Trucks');
        $trucksSnapshot = $trucksRef->getSnapshot();
        $allTrucks = $trucksSnapshot->exists() ? $trucksSnapshot->getValue() : [];

        // 3) The 7 major components
        $majorComponents = [
            'engine_system'               => 'Engine System',
            'transmission_drivetrain'     => 'Transmission & Drivetrain',
            'brake_system'                => 'Brake System',
            'suspension_chassis'          => 'Suspension & Chassis',
            'fuel_cooling_system'         => 'Fuel & Cooling System',
            'steering_system'             => 'Steering System',
            'electrical_auxiliary_system' => 'Electrical & Auxiliary System',
        ];

        // Pass data to view
        return view('resource_manager/reports_management', [
            'reports'         => $maintenanceReports,
            'trucks'          => $allTrucks,
            'majorComponents' => $majorComponents
        ]);
    }

    /**
     * Handle the POST request to create a new Maintenance Report.
     */
    public function storeReport()
    {
        // Gather form fields (same as before)
        $truckId               = $this->request->getPost('truck_id');
        $component             = $this->request->getPost('component');
        $inspectionDate        = $this->request->getPost('inspection_date');
        $actionNeeded          = $this->request->getPost('action_needed');
        $serviceType           = $this->request->getPost('service_type');
        $technicianName        = $this->request->getPost('technician_name');
        $mileageAfterInspection= $this->request->getPost('mileage_after_inspection');
        $estimateNextService   = $this->request->getPost('estimate_next_service_mileage');
        $expectedNextTime      = $this->request->getPost('expected_next_service_time');

        // Basic validation
        if (
            empty($truckId) || empty($component) || empty($inspectionDate) ||
            empty($actionNeeded) || empty($serviceType) || empty($technicianName) ||
            empty($mileageAfterInspection) || empty($estimateNextService) || empty($expectedNextTime)
        ) {
            session()->setFlashdata('error', 'All fields are required.');
            return redirect()->back();
        }

        // Retrieve the truck from Firebase
        $db = Services::firebase();
        $truckRef = $db->getReference('Trucks/' . $truckId);
        $truckData = $truckRef->getValue();
        if (!$truckData) {
            session()->setFlashdata('error', 'Truck not found.');
            return redirect()->back();
        }

        // Validate mileage
        $currentMileage = isset($truckData['current_mileage']) ? (int)$truckData['current_mileage'] : 0;
        $newMileage = (int)$mileageAfterInspection;
        if ($newMileage < $currentMileage) {
            session()->setFlashdata('error', 'Mileage after inspection cannot be below the current mileage.');
            return redirect()->back();
        }

        // Prepare truck updates
        $updates = [
            'current_mileage'         => (string)$newMileage,
            'last_inspection_date'    => $inspectionDate,
            'last_inspection_mileage' => (string)$newMileage,
        ];

        // // Check if maintenance_items exist for the chosen component
        // if (isset($truckData['maintenance_items'][$component])) {
        //     $updates["maintenance_items/$component/last_service_date"]    = $inspectionDate;
        //     $updates["maintenance_items/$component/last_service_mileage"] = (string)$newMileage;
        // } else {
        //     // create new sub-array if not existing
        //     $updates["maintenance_items/$component"] = [
        //         'last_service_date'      => $inspectionDate,
        //         'last_service_mileage'   => (string)$newMileage,
        //         'recommended_interval_km'=> 0
        //     ];
        // }

        // Determine defect status
        $normalizedServiceType = strtolower(trim($serviceType));
        $isDefective = $normalizedServiceType === 'defective';

        // Check if maintenance_items exist for the chosen component
        if (isset($truckData['maintenance_items'][$component])) {
            $updates["maintenance_items/$component/last_service_date"]    = $inspectionDate;
            $updates["maintenance_items/$component/last_service_mileage"] = (string)$newMileage;

            // Step 3: Set or remove 'is_defective'
            if ($isDefective) {
                $updates["maintenance_items/$component/is_defective"] = true;
            } elseif (in_array($normalizedServiceType, ['preventive', 'corrective', 'replacement'])) {
                $updates["maintenance_items/$component/is_defective"] = null; // or false if you prefer
            }
        } else {
            // Create new component if not existing
            $updates["maintenance_items/$component"] = [
                'last_service_date'       => $inspectionDate,
                'last_service_mileage'    => (string)$newMileage,
                'recommended_interval_km' => 0,
            ];

            // Step 3: Add is_defective if applicable
            if ($isDefective) {
                $updates["maintenance_items/$component/is_defective"] = true;
            }
        }


        // Update the truck record
        $truckRef->update($updates);

        // Build the new report data for insertion via ReportModel
        // We'll set "report_type" => "Maintenance Report"
        $reportData = [
            'truck_id'                     => $truckId,
            'report_type'                  => 'Maintenance Report', 
            'component'                    => $component,
            'inspection_date'              => $inspectionDate,
            'action_needed'                => $actionNeeded,
            'service_type'                 => $serviceType,
            'technician_name'              => $technicianName,
            'mileage_after_inspection'     => (string)$newMileage,
            'estimate_next_service_mileage'=> $estimateNextService,
            'expected_next_service_time'   => $expectedNextTime,
        ];

        // Handle image
        $imageFile = $this->request->getFile('report_image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $filename   = $imageFile->getRandomName();
            $localPath  = WRITEPATH . 'uploads/' . $filename;
            $imageFile->move(WRITEPATH . 'uploads', $filename);

            $storage = Services::firebaseStorage();
            $bucketName = env('FIREBASE_STORAGE_BUCKET'); 
            $bucket = $storage->getBucket($bucketName);

            // store under "maintenance/"
            $firebasePath = 'maintenance/' . $filename;
            $bucket->upload(fopen($localPath, 'r'), [
                'name' => $firebasePath
            ]);

            $imgUrl = sprintf(
                'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                $bucketName,
                urlencode($firebasePath)
            );
            $reportData['img_url'] = $imgUrl;

            unlink($localPath);
        }

        // Use the ReportModel to insert with an auto-incremented "Rxxxxx" number
        $reportNumber = $this->reportModel->insertReport($reportData);

        session()->setFlashdata('success', 'Maintenance report created: ' . $reportNumber);
        return redirect()->to(base_url('resource/reports'));
    }
  

}
