<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use App\Models\BookingModel;
use App\Models\ReportModel;
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
        $this->reportModel = new ReportModel();
    }

    public function dashboard()
    {
        $session  = session();
        $clientId = $session->get('user_id'); // e.g. "User5"
    
        // 1) Fetch all of this client's bookings from Firebase
        $allBookings = $this->bookingModel->getBookingsByClient($clientId) ?? [];

        // Retrieve notifications from Firebase for this client.
        $firebase = service('firebase');
        $notificationsRef = $firebase->getReference("Notifications/{$clientId}");
        $notificationsSnapshot = $notificationsRef->getSnapshot();
        $notifications = $notificationsSnapshot->getValue() ?? [];
    
        // 2) Prepare counters
        $pendingCount   = 0;
        $ongoingCount   = 0;  // e.g. "approved", "in-transit"
        $completedCount = 0;  // e.g. "complete"
        $rejectedCount  = 0;
    
        // We'll store "monthlyBookings" = the bookings for the current month
        $monthlyBookings = [];
    
        // 3) Identify the current month (and year).
        //    For example, "2023-09" if this code runs in September 2023.
        $currentYearMonth = date('Y-m'); // "YYYY-mm"
    
        // 4) Loop through the bookings
        foreach ($allBookings as $booking) {
            if (!is_array($booking)) {
                continue; 
            }
            $status      = strtolower($booking['status'] ?? '');
            $bookingDate = $booking['booking_date'] ?? '';  
            $dispatchDate= $booking['dispatch_date'] ?? '';
    
            // Count by status
            if ($status === 'pending') {
                $pendingCount++;
            } elseif (in_array($status, ['approved', 'in-transit'])) {
                $ongoingCount++;
            } elseif (in_array($status, ['completed','complete'])) {
                $completedCount++;
            } elseif ($status === 'rejected') {
                $rejectedCount++;
            }
    
            // Check if this booking belongs to the current year-month
            // We'll parse $bookingDate and compare
            if (!empty($bookingDate)) {
                $ts = strtotime($bookingDate); // e.g. 1693473948
                if ($ts !== false) {
                    $yearMonth = date('Y-m', $ts); // e.g. "2023-09"
                    if ($yearMonth === $currentYearMonth) {
                        $monthlyBookings[] = $booking;
                    }
                }
            }
        }
    
        // 5) "History" = bookings with statuses like completed or rejected
        //    or you can define "history" however you want. 
        $historyBookings = [];
        foreach ($allBookings as $b) {
            if (!is_array($b)) {
                continue;
            }
            $st = strtolower($b['status'] ?? '');
            // We'll define "history" = completed or rejected
            if (in_array($st, ['completed','complete','rejected'])) {
                $historyBookings[] = $b;
            }
        }
    
        // 6) Prepare data for the view
        $data = [
            'pendingCount'    => $pendingCount,
            'ongoingCount'    => $ongoingCount,
            'completedCount'  => $completedCount,
            'rejectedCount'   => $rejectedCount,
            'monthlyBookings' => $monthlyBookings,
            'historyBookings' => $historyBookings,
            'currentYearMonth'=> $currentYearMonth, // for display in the view
            'notifications'   => $notifications, 
        ];
    
        return view('client/dashboard', $data);
    }
    

    public function dismissNotification($notificationId)
    {
        $clientId = session()->get('user_id');
        $firebase = service('firebase');
        $notifRef = $firebase->getReference("Notifications/{$clientId}/{$notificationId}");
        // Either remove the notification or update the "read" flag.
        // Remove notification:
        $notifRef->remove();
        // Alternatively, mark it as read:
        // $notifRef->update(['read' => true]);

        session()->setFlashdata('success', 'Notification dismissed.');
        return redirect()->to(base_url('client/dashboard'));
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

    // =================== BOOKINGS  =================== 

   public function bookings()
    {
        $session  = session();
        $clientId = $session->get('user_id');

        $bookingModel = new BookingModel();
        
        // 1) Get the client's bookings
        $data['bookings'] = $bookingModel->getBookingsByClient($clientId);

        // 2) Check driver availability (now returns true/false/null)
        $data['driverAvailability'] = $bookingModel->isAnyDriverAvailable();
         $data['availableDrivers'] = $bookingModel->getAvailableDrivers();

        // 3) Load the view with both booking data and availability info
        return view('client/bookings', $data);
    }

    public function storeBooking()
    {
        $session  = session();
        $clientId = $session->get('user_id');
    
        // Grab all POST data, including pick_up_lat, pick_up_lng, drop_off_lat, drop_off_lng
        $data = $this->request->getPost();
    
        // Tag your booking with the client ID
        $data['client_id'] = $clientId;
    
        // 1) OPTIONAL: Check driver availability before attempting to create the booking
        // if (!$this->bookingModel->isAnyDriverAvailable()) {
        //     $session->setFlashdata('error', 'Currently, no driver is available.');
        //     return redirect()->back();
        // }
    
        // 2) If a driver is available, proceed with booking creation
        try {
            $bookingId = $this->bookingModel->createBooking($data);
            $session->setFlashdata('success', 'Booking created with ID: ' . $bookingId);
            return redirect()->to(base_url('client/bookings'));
        } catch (\RuntimeException $e) {
            // If no available driver or any other issue arises in createBooking(), catch & display the error
            $session->setFlashdata('error', $e->getMessage());
            return redirect()->back();
        }
    }
    


   /**
     * Display the geolocation page showing the drivers (with bookings)
     * that are assigned to approved/in-transit bookings for the current client.
     */
    public function geolocation()
    {
        $session = session();
        // e.g., the session might store "User6" for the client
        $clientKey = $session->get('firebaseKey');
        
        // 1. Get Firebase DB data
        $db = Services::firebase();
        $bookingsRef = $db->getReference('Bookings');
        $snapshot = $bookingsRef->getSnapshot();
        $allBookings = $snapshot->getValue() ?? [];

        // 2. Filter bookings by this client + allowed statuses
        $allowedStatuses = ['approved', 'in-transit', 'accepted'];
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

        // 3. Group those bookings by normalized driver name
        $driverBookings = [];
        foreach ($clientBookings as $booking) {
            // Remove extra spaces, convert to lowercase
            $driverName = strtolower(preg_replace('/\s+/', ' ', trim($booking['driver_name'] ?? '')));
            if (!empty($driverName)) {
                // If multiple bookings exist for the same driver, keep the first
                if (!isset($driverBookings[$driverName])) {
                    $driverBookings[$driverName] = $booking;
                }
            }
        }

        // 4. Load all drivers; match them by normalized "first_name last_name"
        $driverModel = new DriverModel();
        $allDrivers = $driverModel->getDrivers();

        $clientDrivers = [];
        if ($allDrivers && is_array($allDrivers)) {
            foreach ($allDrivers as $driverId => $driver) {
                // Also remove extra spaces here
                $fullName = strtolower(preg_replace('/\s+/', ' ', trim(
                    ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')
                )));

                // If the driver's full name matches the booking's driver_name, attach the booking
                if (isset($driverBookings[$fullName])) {
                    $driver['booking'] = $driverBookings[$fullName];
                    $clientDrivers[$driverId] = $driver;
                }
            }
        }

        // 5. Send to the view
        return view('client/geolocation', ['drivers' => $clientDrivers]);
    }

    // =================== REPORTS ===================
    /**
     * Display the report form.
     * Also fetch the client's bookings from Firebase to populate the Booking ID dropdown.
     */
    public function report()
    {
        $session = session();
        $clientKey = $session->get('firebaseKey');
        
        // Get Firebase Realtime Database instance
        $db = Services::firebase();
        $bookingsRef = $db->getReference('Bookings');
        $snapshot = $bookingsRef->getSnapshot();
        $allBookings = $snapshot->getValue() ?? [];

        // Filter bookings for this client (assuming 'client_id' matches the session key)
        $clientBookings = [];
        if (is_array($allBookings)) {
            foreach ($allBookings as $booking) {
                if (is_array($booking) && isset($booking['client_id']) && $booking['client_id'] === $clientKey) {
                    $clientBookings[] = $booking;
                }
            }
        }

        // Pass bookings to the view for the dropdown
        return view('client/report', ['bookings' => $clientBookings]);
    }

    /**
     * Process the report form submission.
     * Upload the report image to Firebase Storage and store report data in Firebase Realtime Database.
     */
    public function storeReport()
    {
        $session = session();
        $clientKey = $session->get('firebaseKey');

        // Retrieve form inputs
        $bookingId    = $this->request->getPost('booking_id');
        $reportType   = $this->request->getPost('report_type'); // e.g. "Trip Ticket"
        $file         = $this->request->getFile('report_image');

        // Retrieve additional fields
        $tripDate     = $this->request->getPost('trip_date');
        $tripTime     = $this->request->getPost('trip_time');
        $cargoDetails = $this->request->getPost('cargo_details');

        // Validate required inputs
        if (empty($bookingId) || empty($reportType) || !$file || !$file->isValid()) {
            $session->setFlashdata('error', 'All fields are required and a valid image must be uploaded.');
            return redirect()->to(base_url('client/report'));
        }

        // Get Firebase Realtime Database instance to look up the booking record
        $db = \Config\Services::firebase();
        $bookingRef = $db->getReference('Bookings/' . $bookingId);
        $bookingData = $bookingRef->getValue();

        // If booking is not found, return error
        if (!$bookingData) {
            $session->setFlashdata('error', 'Booking not found.');
            return redirect()->to(base_url('client/report'));
        }

        // Use booking record data for these fields
        // Use license_plate for Plate Number, driver_name for Driver Name,
        // pick_up_address for Origin, and drop_off_address for Destination.
        $plateNumber  = $bookingData['license_plate'] ?? '';
        $driverName   = $bookingData['driver_name'] ?? '';
        $origin       = $bookingData['pick_up_address'] ?? '';
        $destination  = $bookingData['drop_off_address'] ?? '';

        // Prepare initial report data (without img_url)
        $data = [
            'report_type'   => $reportType,
            'booking_id'    => $bookingId,
            'user_id'       => $clientKey,
            // Fields from booking
            'plate_number'  => $plateNumber,
            'driver_name'   => $driverName,
            'origin'        => $origin,
            'destination'   => $destination,
            // Additional fields from the form
            'trip_date'     => $tripDate,
            'trip_time'     => $tripTime,
            'cargo_details' => $cargoDetails,
        ];

        // Insert the report record to generate a new report number and date
        $reportNumber = $this->reportModel->insertReport($data);

        // Build the file name using the report number and report type.
        $extension = $file->getClientExtension();
        $normalizedType = str_replace(' ', '_', $reportType);
        $newName = $reportNumber . '_' . $normalizedType . '.' . $extension;

        // Move the file temporarily to a local directory
        $file->move(WRITEPATH . 'uploads/', $newName);
        $localPath = WRITEPATH . 'uploads/' . $newName;

        // Get Firebase Storage instance and bucket
        $storage = \Config\Services::firebaseStorage();
        $bucketName = env('FIREBASE_STORAGE_BUCKET'); // e.g., "your-project-id.appspot.com"
        $bucket = $storage->getBucket($bucketName);

        // Upload the file to the "report_images" folder in Firebase Storage
        $object = $bucket->upload(
            fopen($localPath, 'r'),
            ['name' => 'report_images/' . $newName]
        );

        // Build the public URL for the uploaded image
        $imgUrl = sprintf(
            'https://firebasestorage.googleapis.com/v0/b/%s/o/%s?alt=media',
            $bucket->name(),
            urlencode($object->name())
        );

        // Update the report record with the image URL
        $this->reportModel->updateReport($reportNumber, ['img_url' => $imgUrl]);

        // Delete the temporary file
        unlink($localPath);

        $session->setFlashdata('success', 'Report ' . $reportNumber . ' created successfully.');
        return redirect()->to(base_url('client/report'));
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
