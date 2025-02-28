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

// Operations Coordinator

$routes->get('operations/dashboard', 'StaffOcController::dashboard');
$routes->get('operations/user_account', 'StaffOcController::userAccount');
$routes->post('operations/upload_profile', 'StaffOcController::uploadProfile');
$routes->get('operations/booking_management', 'StaffOcController::bookingManagement');
$routes->get('operations/view_booking/(:num)', 'StaffOcController::viewBooking/$1');
$routes->get('operations/truck_monitoring', 'StaffOcController::truckMonitoring');
$routes->get('operations/report_management', 'StaffOcController::reportManagement');


// Resource Manager

$routes->get('resource/dashboard', 'StaffRmController::dashboard');
$routes->get('resource/user_account', 'StaffRmController::userAccount');
$routes->post('resource/upload_profile', 'StaffRmController::uploadProfile');
$routes->get('resource/truck_monitoring', 'StaffRmController::truckMonitoring');
$routes->get('resource/report_management', 'StaffRmController::reportManagement');








