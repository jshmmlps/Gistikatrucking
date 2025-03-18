<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\TruckModel;
use App\Models\DriverModel;
use CodeIgniter\Controller;

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
    }

    public function dashboard()
    {
        return view('resource_manager/dashboard');
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
    
            return view('resource_manager/profile', ['user' => $userData]);
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
        return view('resource_manager/truck_management', $data);
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
            return redirect()->to(base_url('resource/trucks'))
                            ->with('success', 'Truck created successfully with ID: ' . $newTruckId);
        }
        return redirect()->to(base_url('resource/trucks'))
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
            return redirect()->to(base_url('resource/trucks'))
                            ->with('success', 'Truck updated successfully.');
        }
        return redirect()->to(base_url('resource/trucks'))
                        ->with('error', 'Invalid request.');
    }

    // Delete a truck record
    public function deleteTruck($truckId)
    {
        $truckModel = new TruckModel();
        $truckModel->deleteTruck($truckId);
        return redirect()->to(base_url('resource/trucks'))
                        ->with('success', 'Truck deleted successfully.');
    }

    // View details of a specific truck
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

        return view('resource_manager/geolocation', [
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
        return view('resource_manager/maintenance', [
            'totalTrucks'     => $totalTrucks,
            'dueTrucks'       => $dueTrucks,
            'chartData'       => $chartData,
            'availableTrucks' => $availableTrucks,
        ]);
    }

}
