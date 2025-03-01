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

// Example dashboards (for demonstration)
$routes->get('staff_operation/dashboard', 'StaffOperationController::index');
$routes->get('staff_resource/dashboard', 'StaffResourceController::index');
$routes->get('dashboard', 'ClientController::test');

// Admin

$routes->get('admin/dashboard', 'AdminController::index');
$routes->get('admin/profile', 'AdminController::profile');
$routes->post('admin/updateProfile', 'AdminController::updateProfile');
$routes->get('admin/users', 'AdminController::users');
$routes->post('admin/users/create', 'AdminController::create');
$routes->post('admin/users/(:segment)/edit', 'AdminController::edit/$1');
$routes->post('admin/users/(:segment)/delete', 'AdminController::delete/$1');
$routes->get('admin/users/create', 'AdminController::create');
$routes->get('admin/users/(:segment)/edit', 'AdminController::edit/$1');
$routes->get('admin/users/(:segment)/delete', 'AdminController::delete/$1');
$routes->get('admin/logout', 'AdminController::logout');

$routes->group('admin', function($routes) {
    $routes->get('trucks', 'AdminController::truck');
    $routes->post('trucks/create', 'AdminController::storeTruck');
    $routes->post('trucks/update/(:segment)', 'AdminController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'AdminController::deleteTruck/$1');
    $routes->get('trucks/view/(:segment)', 'AdminController::viewTruck/$1');
});


// Operations Coordinator

$routes->group('operations', function($routes) {
    // Dashboard and profile routes
    $routes->get('dashboard', 'StaffOcController::dashboard');
    $routes->get('profile', 'StaffOcController::profile');
    $routes->post('updateProfile', 'StaffOcController::updateProfile');
    
    // Truck management routes
    $routes->get('trucks', 'StaffOcController::trucks');
    $routes->post('trucks/create', 'StaffOcController::createTruck');
    $routes->post('trucks/update/(:segment)', 'StaffOcController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'StaffOcController::deleteTruck/$1');
    $routes->get('trucks/view/(:segment)', 'StaffOcController::viewTruck/$1');
    
    // Booking management routes
    $routes->get('bookings', 'StaffOcController::bookings');
    $routes->get('bookings/view/(:segment)', 'StaffOcController::viewBooking/$1');
});

// Resource Manager

$routes->group('resource', function($routes) {
    // Dashboard and user account routes
    $routes->get('dashboard', 'StaffRmController::dashboard');
    $routes->get('profile', 'StaffRmController::profile');
    $routes->post('updateProfile', 'StaffRmController::updateProfile');
    
    // Truck management routes
    $routes->get('trucks', 'StaffRmController::trucks');
    $routes->post('trucks/create', 'StaffRmController::createTruck');
    $routes->post('trucks/update/(:segment)', 'StaffRmController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'StaffRmController::deleteTruck/$1');
    $routes->get('trucks/view/(:segment)', 'StaffRmController::viewTruck/$1');
});








