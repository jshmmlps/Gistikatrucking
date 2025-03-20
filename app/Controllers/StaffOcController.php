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
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'operations coordinator') {
            $session->setFlashdata('error', 'No authorization.');
            redirect()->to(base_url('login'))->send();
            exit; // Stop further execution
        }
        $this->userModel = new UserModel();
        $this->driverModel = new DriverModel();
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
            // Check if profile_picture exists or use default
            if (empty($userData['profile_picture'])) {
                $userData['profile_picture'] = base_url('public/images/default.jpg');
            }

            return view('operations_coordinator/profile', ['user' => $userData]);
        }

        // Optionally handle the case where no user is found (e.g. redirect or show error)
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


    // ----- Truck Management Methods -----

    // List all trucks
    public function trucks()
    {
        $truckModel = new TruckModel();
        $trucks = $truckModel->getTrucks();
        
        // Re-index the array in case it is associative
        $trucks = array_values($trucks);
        
        // Sort trucks naturally by truck_id (e.g., Truck1, Truck2, Truck3, ...)
        usort($trucks, function($a, $b) {
            return strnatcmp($a['truck_id'], $b['truck_id']);
        });
        
        $data['trucks'] = $trucks;
        return view('operations_coordinator/truck_management', $data);
    }

    // Create a new truck record
    public function createTruck()
    {
        if ($this->request->getMethod() == 'post') {
            $data = [
                'truck_model'             => $this->request->getPost('truck_model'),
                'plate_number'            => $this->request->getPost('plate_number'),
                'engine_number'           => $this->request->getPost('engine_number'),
                'chassis_number'          => $this->request->getPost('chassis_number'),
                'color'                   => $this->request->getPost('color'),
                'last_inspection_date'    => $this->request->getPost('last_inspection_date'),
                'last_inspection_mileage' => $this->request->getPost('last_inspection_mileage'),
                'cor_number'              => $this->request->getPost('cor_number'),
                'insurance_details'       => $this->request->getPost('insurance_details'),
                'license_plate_expiry'    => $this->request->getPost('license_plate_expiry'),
                'registration_expiry'     => $this->request->getPost('registration_expiry'),
                'truck_type'              => $this->request->getPost('truck_type'),
                'fuel_type'               => $this->request->getPost('fuel_type'),
                'truck_length'            => $this->request->getPost('truck_length'),
                'load_capacity'           => $this->request->getPost('load_capacity'),
                'maintenance_technician'  => $this->request->getPost('maintenance_technician'),
            ];
            $truckModel = new TruckModel();
            $newTruckId = $truckModel->insertTruck($data);
            return redirect()->to(base_url('operations/trucks'))
                            ->with('success', 'Truck created successfully with ID: ' . $newTruckId);
        }
        return redirect()->to(base_url('operations/trucks'))
                        ->with('error', 'Invalid request.');
    }

    // Update an existing truck record
    public function updateTruck($truckId)
    {
        if ($this->request->getMethod() == 'post') {
            $data = [
                'truck_model'             => $this->request->getPost('truck_model'),
                'plate_number'            => $this->request->getPost('plate_number'),
                'engine_number'           => $this->request->getPost('engine_number'),
                'chassis_number'          => $this->request->getPost('chassis_number'),
                'color'                   => $this->request->getPost('color'),
                'last_inspection_date'    => $this->request->getPost('last_inspection_date'),
                'last_inspection_mileage' => $this->request->getPost('last_inspection_mileage'),
                'cor_number'              => $this->request->getPost('cor_number'),
                'insurance_details'       => $this->request->getPost('insurance_details'),
                'license_plate_expiry'    => $this->request->getPost('license_plate_expiry'),
                'registration_expiry'     => $this->request->getPost('registration_expiry'),
                'truck_type'              => $this->request->getPost('truck_type'),
                'fuel_type'               => $this->request->getPost('fuel_type'),
                'truck_length'            => $this->request->getPost('truck_length'),
                'load_capacity'           => $this->request->getPost('load_capacity'),
                'maintenance_technician'  => $this->request->getPost('maintenance_technician'),
            ];
            $truckModel = new TruckModel();
            $truckModel->updateTruck($truckId, $data);
            return redirect()->to(base_url('operations/trucks'))
                            ->with('success', 'Truck updated successfully.');
        }
        return redirect()->to(base_url('operations/trucks'))
                        ->with('error', 'Invalid request.');
    }

    // Delete a truck record
    public function deleteTruck($truckId)
    {
        $truckModel = new TruckModel();
        $truckModel->deleteTruck($truckId);
        return redirect()->to(base_url('operations/trucks'))
                        ->with('success', 'Truck deleted successfully.');
    }

    // View details of a specific truck
    public function viewTruck($truckId)
    {
        $truckModel = new TruckModel();
        $data['truck'] = $truckModel->getTruck($truckId);
        return view('operations_coordinator/truck_detail', $data);
    }


    // ============== BOOKING MODULE ===================  //
    // List all bookings for review
    public function bookings()
    {
        $bookingModel = new BookingModel();
        $data['bookings'] = $bookingModel->getAllBookings();
        return view('operations_coordinator/bookings', $data);
    }

    public function updateBookingStatus()
    {
        $bookingId  = $this->request->getPost('booking_id');
        $status     = $this->request->getPost('status');     // e.g., "approved", "rejected", etc.
        $distance   = $this->request->getPost('distance');   // New distance value
        $driverId   = $this->request->getPost('driver');     // Selected driver id (if any)
        $conductorId= $this->request->getPost('conductor');  // Selected conductor id (if any)
        $truckId    = $this->request->getPost('truck_id');   // Hidden field updated via JS

        // Prepare the update data array
        $updateData = [
            'status'   => $status,
            'distance' => $distance
        ];

        try {
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

            // Perform the update
            $bookingModel = new BookingModel();
            if ($bookingModel->updateBooking($bookingId, $updateData)) {
                // Set success flash data
                session()->setFlashdata('success', 'Booking updated successfully.');
            } else {
                // Set error flash data
                session()->setFlashdata('error', 'Failed to update the booking.');
            }
        } catch (\Exception $e) {
            // Catch any errors and set error flash data
            session()->setFlashdata('error', 'An error occurred: ' . $e->getMessage());
        }

        // Redirect back to the bookings page
        return redirect()->to(base_url('operations/bookings'));
    }


    // ================== GEOLOCATION MODULE ===================  //

    public function geolocation()
    {
        // Get only drivers with valid geolocation fields
        $drivers = $this->driverModel->getDriversWithLocation();

        return view('operations_coordinator/geolocation', [
            'drivers' => $drivers
        ]);
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
          return view('operations_coordinator/reports_management', ['reports' => $reports]);
      }

      
    // ----- Logout -----
    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
