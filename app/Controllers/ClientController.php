<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use App\Models\BookingModel;
use CodeIgniter\Controller;
use Config\Services;

class ClientController extends BaseController
{
    protected $userModel;
    protected $bookingModel;

    public function __construct()
    {
        // Load session and verify client authorization
        $session = session();
        if (!$session->get('loggedIn') || $session->get('user_level') !== 'client') {
            $session->setFlashdata('error', 'No authorization.');
            redirect()->to(base_url('login'))->send();
            exit; // Stop further execution
        }
        
        $this->userModel    = new UserModel();
        $this->bookingModel = new BookingModel();
    }

    public function dashboard()
    {
        return view('client/dashboard');
    }
    
    // =================== PROFILE =================== 
    
     /**
     * Display the client profile page.
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
    
            return view('client/profile', ['user' => $userData]);
        }
    
        // Optionally handle the case where no user is found (e.g. redirect or show error)
        $session->setFlashdata('error', 'User not found');
        return redirect()->to('/client');
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
        return redirect()->to('client/profile');
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
            return redirect()->to('client/profile');
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
            return redirect()->to('client/profile');
        } else {
            $session->setFlashdata('error', 'Failed to upload image. Please try again.');
            return redirect()->to('client/profile');
        }
    }

    public function bookings()
    {
        $session  = session();
        $clientId = $session->get('user_id');    // Must match what was stored when user logged in

        $bookingModel = new BookingModel();
        $data['bookings'] = $bookingModel->getBookingsByClient($clientId);

        return view('client/bookings', $data);
    }

    // =================== BOOKINGS  =================== 

    // Show the create booking form
    public function createBooking()
    {
        return view('client/create_booking');
    }

    public function storeBooking()
    {
        $session  = session();
        $clientId = $session->get('user_id');

        // Grab all POST data, including pick_up_lat, pick_up_lng, drop_off_lat, drop_off_lng
        $data = $this->request->getPost();

        // Tag your booking with the client ID
        $data['client_id'] = $clientId;

        try {
            // Pass data to the model and attempt to create a booking.
            $bookingId = $this->bookingModel->createBooking($data);
            $session->setFlashdata('success', 'Booking created with ID: ' . $bookingId);
            return redirect()->to(base_url('client/bookings'));
        } catch (\RuntimeException $e) {
            // If no available driver or conductor, show an error message.
            $session->setFlashdata('error', $e->getMessage());
            return redirect()->back();
        }
    }



    // Process the create booking form submission
    // public function storeBooking()
    // {
    //     $session  = session();
    //     $clientId = $session->get('user_id'); // get user_id here in the controller
    
    //     $data = $this->request->getPost();
    //     // Tag your booking with the client ID
    //     $data['client_id'] = $clientId;
    
    //     // Now pass $data to the model
    //     $bookingId = $this->bookingModel->createBooking($data);

    //     $session->setFlashdata('success', 'Booking created with ID: ' . $bookingId);
    //     return redirect()->to(base_url('client/bookings'));
    // }

    // public function profile()
    // {
    //     return view('client/profile');
    // }

   /**
     * Display the geolocation page showing the drivers (with bookings)
     * that are assigned to approved/in-transit bookings for the current client.
     */
    public function geolocation()
    {
        $session = session();
        // Assuming the client's Firebase key is stored in session (e.g., "User6")
        $clientKey = $session->get('firebaseKey');
        
        // Get Firebase Realtime Database instance
        $db = Services::firebase();
        $bookingsRef = $db->getReference('Bookings');
        $snapshot = $bookingsRef->getSnapshot();
        $allBookings = $snapshot->getValue() ?? [];

        // Define allowed statuses (adjust as needed)
        $allowedStatuses = ['approved', 'in-transit', 'accepted'];

        // Filter bookings for this client that have an allowed status
        $clientBookings = [];
        foreach ($allBookings as $booking) {
            if (!is_array($booking)) {
                continue;
            }
            if (
                isset($booking['client_id'], $booking['status']) &&
                $booking['client_id'] === $clientKey &&
                in_array(strtolower($booking['status']), $allowedStatuses)
            ) {
                $clientBookings[] = $booking;
            }
        }

        // Group bookings by driver name (normalized)
        $driverBookings = [];
        foreach ($clientBookings as $booking) {
            $driverName = strtolower(trim($booking['driver_name'] ?? ''));
            if (!empty($driverName)) {
                // If multiple bookings exist for the same driver,
                // you may decide to keep the latest or first; here, we keep the first occurrence.
                if (!isset($driverBookings[$driverName])) {
                    $driverBookings[$driverName] = $booking;
                }
            }
        }

        // Fetch all drivers from Firebase using DriverModel
        $driverModel = new DriverModel();
        $allDrivers = $driverModel->getDrivers();

        // Filter drivers to include only those that appear in our driverBookings
        $clientDrivers = [];
        if ($allDrivers && is_array($allDrivers)) {
            foreach ($allDrivers as $driverId => $driver) {
                // Build full name from the driver's record and normalize it
                $fullName = strtolower(trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')));
                if (isset($driverBookings[$fullName])) {
                    // Attach booking details for later use in the view
                    $driver['booking'] = $driverBookings[$fullName];
                    $clientDrivers[$driverId] = $driver;
                }
            }
        }

        return view('client/geolocation', ['drivers' => $clientDrivers]);
    }

    public function report()
    {
        return view('client/report');
    }

    public function Faq()
    {
        return view('client/faq');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('login'))->send();
    }
    
}
