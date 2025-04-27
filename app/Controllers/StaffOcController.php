<?php

namespace App\Controllers;

// use App\Models\BookingModel; 
use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\BookingModel;
use App\Models\DriverModel;
use CodeIgniter\Controller;
use Config\Services;

class StaffOcController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        // Load session and check operations coordinator authorization
        $session = session();
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'operation manager') {
            $session->setFlashdata('error', 'No authorization.');
            redirect()->to(base_url('login'))->send();
            exit; // Stop further execution
        }
        $this->userModel = new UserModel();
        $this->driverModel = new DriverModel();
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

    // ----- Dashboard -----
    // public function dashboard()
    // {
    //     // $firebase = Services::firebase();
    //     // $trucksRef = $firebase->getReference('Trucks');
    //     // $trucksData = $trucksRef->getValue();
    //     // $data['trucksCount'] = is_array($trucksData) ? count($trucksData) : 0;

    //     // For this example, we'll simply load the view.
    //     return view('operations_coordinator/dashboard');
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
    
        return view('operations_coordinator/dashboard', $data);
    }


    // ----- User Profile Management -----
    // Display the operations coordinator's profile page.
    public function profile()
    {
        $session = session();
        $firebaseKey = $session->get('firebaseKey');

        // Fetch user data from the UserModel (including the profile_picture field)
        $userData = $this->userModel->getUser($firebaseKey);

        // If the user exists, pass the data to the view
        if ($userData) {
            // Check if profile_picture exists; if not, use a default image.
            if (empty($userData['profile_picture'])) {
                $userData['profile_picture'] = base_url('public/images/default.jpg');
            }

            // Retrieve notification counts.
            $notificationCounts = $this->getNotificationCounts();

            // Merge the user data with the notification counts.
            $data = array_merge(['user' => $userData], $notificationCounts);

            return view('operations_coordinator/profile', $data);
        }

        // Optionally handle the case where no user is found.
        $session->setFlashdata('error', 'User not found');
        return redirect()->to('/');
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
        return redirect()->to('operations/profile');
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
            return redirect()->to('operations/profile');
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

            // Get Firebase Storage instance
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

            // Construct the public URL for the image
            $imageUrl = sprintf(
                'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
                $bucket->name(),
                urlencode($object->name())
            );

            // Update the user's profile with the new image URL
            $this->userModel->updateUser($firebaseKey, ['profile_picture' => $imageUrl]);

            // Cleanup the temporary uploaded file
            unlink($localPath);

            $session->setFlashdata('success', 'Profile picture updated successfully.');
            return redirect()->to('operations/profile');
        } else {
            $session->setFlashdata('error', 'Failed to upload image. Please try again.');
            return redirect()->to('operations/profile');
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

        return view('operations_coordinator/truck_management', $data);
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
        return redirect()->to(base_url('operations/trucks'));
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
        return redirect()->to(base_url('operations/trucks'));
    }

    /**
     * Delete an existing truck
     */
    public function deleteTruck($truckId)
    {
        $truckModel = new TruckModel();
        $truckModel->deleteTruck($truckId);
        session()->setFlashdata('success', 'Truck deleted successfully.');
        return redirect()->to(base_url('operations/trucks'));
    }

    /**
     * View a truck's details
     */
    public function viewTruck($truckId)
    {
        $truckModel = new TruckModel();
        $data['truck'] = $truckModel->getTruck($truckId);
        return view('operations_coordinator/truck_detail', $data);
    }


    // ============== BOOKING MODULE ===================  //

    // List all bookings for operations to review
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

        // Retrieve and merge notification counts.
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);

        return view('operations_coordinator/bookings', $data);
    }

    // Update booking status (approval/rejection, etc.), or reassign driver/conductor
   // Update booking status (approval/rejection, etc.), or reassign driver/conductor
   public function updateBookingStatus()
   {
       $bookingId   = $this->request->getPost('booking_id');
       $status      = $this->request->getPost('status');  // e.g., "approved", "rejected", "in-transit", etc.
       $distance    = $this->request->getPost('distance');
       $driverId    = $this->request->getPost('driver');
       $conductorId = $this->request->getPost('conductor'); // Currently not used for conflict, but can be extended
       $truckId     = $this->request->getPost('truck_id');

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
           return redirect()->to(base_url('operations/bookings'));
       }

       $bookingModel = new BookingModel();
       $allBookings  = $bookingModel->getAllBookings(); // to detect conflicts
       $driversRef   = $firebase->getReference('Drivers');

       // ─────────────────────────────────────────────────────────────────────────────
       // 1) CONFLICT CHECK: If we are trying to change the booking's status to an
       //    "active" status (approved/in-transit) and a driver is selected, ensure
       //    the same driver is not actively assigned elsewhere.
       // ─────────────────────────────────────────────────────────────────────────────
       // Here, "active" can be extended to include whatever statuses you consider
       // to conflict. For example, let's treat "approved" and "in-transit" as active.
       $activeStatuses = ['approved', 'in-transit'];

       // If the new status is active...
       if (in_array($status, $activeStatuses)) {
           // And a new driver is selected (or remains the same driver)...
           if (!empty($driverId)) {
               if ($allBookings && is_array($allBookings)) {
                   foreach ($allBookings as $b) {
                       if (!is_array($b)) continue; // skip invalid
                       // If same driver is found in a different booking that is active,
                       // we block the status update for this booking.
                       if (
                           isset($b['driver_id']) &&
                           $b['driver_id'] === $driverId &&
                           in_array($b['status'], $activeStatuses) &&
                           ($b['booking_id'] != $bookingId) // skip the same booking
                       ) {
                           session()->setFlashdata('error', 'Driver is currently assigned to an ongoing booking (#'.$b['booking_id'].').');
                           return redirect()->to(base_url('operations/bookings'));
                       }
                   }
               }
           } else {
               // If no driver is selected at all, you may choose to block or allow that.
               // For example:
               session()->setFlashdata('error', 'Driver is currently taking another booking.');
               return redirect()->to(base_url('operations/bookings'));
           }
       }

       // ─────────────────────────────────────────────────────────────────────────────
       // 2) If a new driver is selected, fetch the driver data and reassign
       // ─────────────────────────────────────────────────────────────────────────────
       if (!empty($driverId)) {
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

       // ─────────────────────────────────────────────────────────────────────────────
       // 3) If operator changed the truck manually (no driver update occurred)
       // ─────────────────────────────────────────────────────────────────────────────
       if (!empty($truckId) && !isset($updateData['truck_id'])) {
           $truckData = $firebase->getReference('Trucks/' . $truckId)->getValue();
           if ($truckData) {
               $updateData['truck_id']      = $truckData['truck_id']       ?? $truckId;
               $updateData['truck_model']   = $truckData['truck_model']     ?? '';
               $updateData['license_plate'] = $truckData['plate_number']    ?? '';
               $updateData['type_of_truck'] = $truckData['truck_type']      ?? '';
           }
       }

       // ─────────────────────────────────────────────────────────────────────────────
       // 4) Update the booking with final data
       // ─────────────────────────────────────────────────────────────────────────────
       $bookingModel->updateBooking($bookingId, $updateData);

       // If booking is being marked as "completed", update the truck's current mileage.
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

        // 6) Persistent Notification:
        // Instead of using FCM, store a notification record in Firebase that the client can view upon login.
        // Assume the booking contains a "client_id" field.
        if (!empty($existingBooking['client_id'])) {
            $clientId = $existingBooking['client_id'];
            $notificationData = [
                'message'    => "Your booking #{$bookingId} status has been updated to " . ucfirst($status) . ".",
                'booking_id' => $bookingId,
                'status'     => $status,
                'timestamp'  => date('c'),
                'read'       => false  // This flag indicates whether the user has dismissed the notification.
            ];
            // Save the notification in Firebase under Notifications/{clientId}
            $firebase->getReference("Notifications/{$clientId}")->push($notificationData);
        }

       session()->setFlashdata('success', 'Booking #'.$bookingId.' updated successfully!');
       return redirect()->to(base_url('operations/bookings'));
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
    
        return view('operations_coordinator/geolocation', $data);
    }
    
    // ================== REPORT MANAGEMENT MODULE ===================  //
    
    public function Report()
    {
        // 1) Call the StorageScan logic
        $storageScan = new \App\Controllers\StorageScan();
        $storageScan->index();
        // That will create any missing "Reports" records in Realtime DB
    
        // 2) Get Firebase Realtime Database instance
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
    
        // Build the initial data array
        $data = [
            'reports' => $reports
        ];
    
        // Retrieve and merge notification counts
        $notificationCounts = $this->getNotificationCounts();
        $data = array_merge($data, $notificationCounts);
    
        // 3) Pass the merged data to your view
        return view('operations_coordinator/reports_management', $data);
    }
    
    public function saveRemark() 
    {
        // Get the POST data
        $reportNumber = $this->request->getPost('report_number');
        $remark = $this->request->getPost('remark');
        $remarkStatus = $this->request->getPost('remark_status'); // new field: "Pending", "Approved", or "Rejected"
        $action = $this->request->getPost('action'); // "save" or "delete"
    
        // Default remark status to "Pending" if empty
        if ($action !== 'delete' && empty($remarkStatus)) {
            $remarkStatus = 'Pending';
        }
    
        // Get Firebase instance and reference to Reports node
        $db = \Config\Services::firebase();
        $reportRef = $db->getReference('Reports/' . $reportNumber);
    
        if ($action === 'delete') {
            // Remove remark field (or set them to empty strings)
            $reportRef->update([
                'remark' => '',
                'remark_status' => ''
            ]);
            session()->setFlashdata('success', 'Remark deleted successfully.');
        } else {
            // Save or update the remark and remark_status
            $reportRef->update([
                'remark' => $remark,
                'remark_status' => $remarkStatus
            ]);
            session()->setFlashdata('success', 'Remark saved successfully.');
        }
        
        return redirect()->to(base_url('operations/reports'));
    }
    

      
    // ----- Logout -----
    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
