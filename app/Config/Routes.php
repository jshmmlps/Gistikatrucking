<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Dashboard routes
$routes->get('/dashboard', 'DashboardController::dashboard');

// Profile routes
$routes->get('/user', 'UserController::user');
$routes->get('/user/getUserDetails/(:num)', 'UserController::getUserDetails/$1');

// Client Routes
$routes->get('/clients', 'ClientController::clients');
$routes->post('/clients', 'ClientController::clients');
//Driver Routes
$routes->get('/drivers', 'DriverController::drivers');
$routes->post('/drivers', 'DriverController::drivers');

//Booking Routes
$routes->get('/bookings', 'BookingController::bookings');
$routes->post('/bookings', 'BookingController::bookings');

// Truck Monitoring Routes
$routes->get('/trucks', 'TruckController::trucks');
$routes->post('/trucks', 'TruckController::trucks');
$routes->get('trucks/view/(:num)', 'TruckController::view/$1');

//Maintenance Routes
$routes->get('maintenance', 'MaintenanceController::maintenance');
$routes->post('maintenance', 'MaintenanceController::maintenance');
$routes->get('maintenance/view/(:num)', 'MaintenanceController::view/$1');


//Test
$routes->get('firebase-test', 'FirebaseTestController::index');
$routes->get('firebase-test-read', 'FirebaseTestController::read');

// // Admin login
// $routes->get('admin/login', 'AdminAuthController::login');
// $routes->post('admin/login/process', 'AdminAuthController::processLogin');
// $routes->get('admin/logout', 'AdminAuthController::logout');

// // Staff login
// $routes->get('staff/login', 'StaffAuthController::login');
// $routes->post('staff/login/process', 'StaffAuthController::processLogin');
// $routes->get('staff/logout', 'StaffAuthController::logout');

// // Client login
// $routes->get('client/login', 'ClientAuthController::login');
// $routes->post('client/login/process', 'ClientAuthController::processLogin');
// $routes->get('client/logout', 'ClientAuthController::logout');

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
$routes->get('login', 'AuthController::login');
$routes->post('login/process', 'AuthController::processLogin');
$routes->get('logout', 'AuthController::logout');

// Example dashboards (for demonstration)
$routes->get('admin/dashboard', 'AdminController::index');
$routes->get('staff_operation/dashboard', 'StaffOperationController::index');
$routes->get('staff_resource/dashboard', 'StaffResourceController::index');
$routes->get('dashboard', 'ClientController::test');


