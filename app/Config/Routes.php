<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Dashboard routes
$routes->get('/dashboard', 'DashboardController::dashboard');

// Profile routes (FOR REMOVAL)
// $routes->get('/user', 'UserController::user');
// $routes->get('/user/getUserDetails/(:num)', 'UserController::getUserDetails/$1');

// Client Routes
$routes->get('/clients', 'ClientController::clients');
$routes->post('/clients', 'ClientController::clients');

//Driver Routes
$routes->get('/drivers', 'DriverController::drivers');
$routes->post('/drivers', 'DriverController::drivers');

//Booking Routes
$routes->get('/bookings', 'BookingController::bookings');
$routes->post('/bookings', 'BookingController::bookings');

// Truck Monitoring Routes (FOR REMOVAL)
// $routes->get('/trucks', 'TruckController::trucks');
// $routes->post('/trucks', 'TruckController::trucks');
// $routes->get('trucks/view/(:num)', 'TruckController::view/$1');

//Maintenance Routes
$routes->get('maintenance', 'MaintenanceController::maintenance');
$routes->post('maintenance', 'MaintenanceController::maintenance');
$routes->get('maintenance/view/(:num)', 'MaintenanceController::view/$1');

//Test
$routes->get('firebase-test', 'FirebaseTestController::index');
$routes->get('firebase-test-read', 'FirebaseTestController::read');

// Registration
$routes->get('register', 'RegistrationController::createForm');
$routes->post('register/create', 'RegistrationController::createAccount');
$routes->get('register/verifyOTP', 'RegistrationController::showOTPForm');
$routes->post('register/verifyOTP', 'RegistrationController::verifyOTP');
$routes->get('register/resendOTP', 'RegistrationController::resendOTP');

// Forgot password
$routes->get('password/forgot', 'PasswordController::forgotPassword');
$routes->post('password/forgot', 'PasswordController::sendResetLink');
$routes->get('password/reset/(:any)', 'PasswordController::resetPassword/$1');
$routes->post('password/reset', 'PasswordController::updatePassword');

// Unified login routes
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('login/process', 'AuthController::processLogin');
$routes->get('logout', 'AuthController::logout');

// Client Routes
$routes->group('client', function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'ClientController::dashboard');

    // Profile
    $routes->get('profile', 'ClientController::profile');
    $routes->post('updateProfile', 'ClientController::updateProfile');
    $routes->post('uploadProfilePicture', 'ClientController::uploadProfilePicture');
    $routes->post('editProfilePicture', 'ClientController::uploadProfilePicture');

    // Geolocation
    $routes->get('geolocation', 'ClientController::geolocation');

    // Report
    $routes->get('reports', 'ClientController::report');

    // Logout
    $routes->get('logout', 'ClientController::logout');

    $routes->get('faq', 'ClientController::Faq');
});

// Client routes
$routes->group('client', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('bookings', 'ClientController::bookings');
    $routes->post('store-booking', 'ClientController::storeBooking');
});

$routes->get('client/report', 'ClientController::report');
$routes->post('client/report/store', 'ClientController::storeReport');


// Admin routes
$routes->group('admin', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('bookings', 'AdminController::bookings');
    $routes->post('update-booking-status', 'AdminController::updateBookingStatus');
});

// Operations Coordinator routes
$routes->group('operations', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('bookings', 'StaffOcController::bookings');
    $routes->post('update-booking-status', 'StaffOcController::updateBookingStatus');
});


// Admin Routes

$routes->group('admin', function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'AdminController::index');

    // Profile Management
    $routes->get('profile', 'AdminController::profile');
    $routes->post('updateProfile', 'AdminController::updateProfile');

    // User Management
    $routes->get('users', 'AdminController::users');
    $routes->get('users/create', 'AdminController::create');
    $routes->post('users/create', 'AdminController::create');
    $routes->get('users/(:segment)/edit', 'AdminController::edit/$1');
    $routes->post('users/(:segment)/edit', 'AdminController::edit/$1');
    $routes->get('users/(:segment)/delete', 'AdminController::delete/$1');
    $routes->post('users/(:segment)/delete', 'AdminController::delete/$1');
    $routes->post('uploadProfilePicture', 'AdminController::uploadProfilePicture');
    $routes->post('editProfilePicture', 'AdminController::uploadProfilePicture');

    // Truck Management
    $routes->get('trucks', 'AdminController::truck');
    $routes->get('trucks/view/(:segment)', 'AdminController::viewTruck/$1');
    $routes->post('trucks/create', 'AdminController::storeTruck');
    $routes->post('trucks/update/(:segment)', 'AdminController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'AdminController::deleteTruck/$1');

    // Driver/Conductor Management
    $routes->get('driver', 'AdminController::driverManagement');
    $routes->post('driver/create', 'AdminController::createDriver');
    $routes->post('driver/update/(:segment)', 'AdminController::updateDriver/$1');
    $routes->get('driver/delete/(:segment)', 'AdminController::deleteDriver/$1');
    $routes->get('driver/view/(:segment)', 'AdminController::viewDriver/$1');

    // Client Management
    $routes->get('clients', 'AdminController::clientManagement');
    $routes->get('clientView/(:any)', 'AdminController::clientView/$1');
    $routes->match(['get', 'post'], 'clientEdit/(:any)', 'AdminController::clientEdit/$1');
    
    // Maintenance
    $routes->get('maintenance', 'AdminController::Maintenance');

    // Geolocation
    $routes->get('geolocation', 'AdminController::geolocation');

    // Report Management
    $routes->get('reports', 'AdminController::Report');

    // Logout
    $routes->get('logout', 'AdminController::logout');
});




// Operations Coordinator

$routes->group('operations', function($routes) {
    // Dashboard and profile routes
    $routes->get('dashboard', 'StaffOcController::dashboard');
    $routes->get('dashboard/trucks-count', 'StaffOcController::trucksCount');
    $routes->get('profile', 'StaffOcController::profile');
    $routes->post('updateProfile', 'StaffOcController::updateProfile');
    $routes->post('uploadProfilePicture', 'StaffOcController::uploadProfilePicture');
    $routes->post('editProfilePicture', 'StaffOcController::uploadProfilePicture');

    // Truck management routes
    $routes->get('trucks', 'StaffOcController::trucks');
    $routes->post('trucks/create', 'StaffOcController::createTruck');
    $routes->post('trucks/update/(:segment)', 'StaffOcController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'StaffOcController::deleteTruck/$1');
    $routes->get('trucks/view/(:segment)', 'StaffOcController::viewTruck/$1');

    // Geolocation routes
    $routes->get('geolocation', 'StaffOcController::geolocation');
    
    // Booking management routes
    $routes->get('bookings', 'StaffOcController::bookings');
    $routes->get('bookings/view/(:segment)', 'StaffOcController::viewBooking/$1');


    // Report management routes
    $routes->get('reports', 'StaffOcController::Report');
});

// Resource Manager

$routes->group('resource', function($routes) {
    // Dashboard and user account routes
    $routes->get('dashboard', 'StaffRmController::dashboard');
    $routes->get('profile', 'StaffRmController::profile');
    $routes->post('updateProfile', 'StaffRmController::updateProfile');
    $routes->post('uploadProfilePicture', 'StaffRmController::uploadProfilePicture');
    $routes->post('editProfilePicture', 'StaffRmController::uploadProfilePicture');
    
    // Truck management routes
    $routes->get('trucks', 'StaffRmController::trucks');
    $routes->post('trucks/create', 'StaffRmController::createTruck');
    $routes->post('trucks/update/(:segment)', 'StaffRmController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'StaffRmController::deleteTruck/$1');
    $routes->get('trucks/view/(:segment)', 'StaffRmController::viewTruck/$1');

    // Maintenance management routes
    $routes->get('maintenance', 'StaffRmController::maintenance');

    // Geolocation routes
    $routes->get('geolocation', 'StaffRmController::geolocation');

    // Report management routes
    $routes->get('reports', 'StaffRmController::Report');
});








