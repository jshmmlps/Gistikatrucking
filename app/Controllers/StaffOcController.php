<?php

namespace App\Controllers;

// use App\Models\BookingModel; 
use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\BookingModel;
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
    }

    // ----- Dashboard -----
    public function dashboard()
    {
        // $firebase = Services::firebase();
        // $trucksRef = $firebase->getReference('Trucks');
        // $trucksData = $trucksRef->getValue();
        // $data['trucksCount'] = is_array($trucksData) ? count($trucksData) : 0;

        // For this example, we'll simply load the view.
        return view('operations_coordinator/dashboard');
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
            
            return redirect()->to(base_url('operations_coordinator/bookings'));
        }

    // ================== GEOLOCATION MODULE ===================  //

    public function Geolocation()
    {
        // Here you can fetch and display geolocation data.
        // For now, we simply load the view.
        return view('operations_coordinator/geolocation');
    }

    // ----- Logout -----
    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
