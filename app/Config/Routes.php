<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

//---------------------------
// Public / Authentication
//---------------------------
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('login/process', 'AuthController::processLogin');
$routes->get('logout', 'AuthController::logout');

//---------------------------
// Dashboard
//---------------------------
// $routes->get('dashboard', 'DashboardController::dashboard');

//---------------------------
// Client Routes
// All client-related controllers are assumed to be in App\Controllers
//---------------------------
$routes->group('client', ['namespace' => 'App\Controllers'], function($routes) {
    // Dashboard, Profile & Booking
    $routes->get('dashboard', 'ClientController::dashboard');
    $routes->get('profile', 'ClientController::profile');
    $routes->post('updateProfile', 'ClientController::updateProfile');
    $routes->post('uploadProfilePicture', 'ClientController::uploadProfilePicture');
    // In this example, editProfilePicture uses the same method as uploadProfilePicture
    $routes->post('editProfilePicture', 'ClientController::uploadProfilePicture');
    
    // Geolocation & Reports
    $routes->get('geolocation', 'ClientController::geolocation');
    $routes->get('reports', 'ClientController::report');
    
    // Bookings
    $routes->get('bookings', 'ClientController::bookings');
    $routes->post('store-booking', 'ClientController::storeBooking');
    
    // FAQ & Logout
    $routes->get('faq', 'ClientController::Faq');
    $routes->get('logout', 'ClientController::logout');

    // Alternatively, if you want report routes with a “client/” prefix:
    $routes->get('report', 'ClientController::report');
    $routes->post('report/store', 'ClientController::storeReport');
});

$routes->get('client/notifications/dismiss/(:any)', 'ClientController::dismissNotification/$1');


//---------------------------
// Test Routes
//---------------------------
$routes->get('firebase-test', 'FirebaseTestController::index');
$routes->get('firebase-test-read', 'FirebaseTestController::read');

//---------------------------
// Registration & Password
//---------------------------
$routes->group('register', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('/', 'RegistrationController::createForm');
    $routes->post('create', 'RegistrationController::createAccount');
    $routes->get('verifyOTP', 'RegistrationController::showOTPForm');
    $routes->post('verifyOTP', 'RegistrationController::verifyOTP');
    $routes->get('resendOTP', 'RegistrationController::resendOTP');
});

$routes->group('password', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('forgot', 'PasswordController::forgotPassword');
    $routes->post('forgot', 'PasswordController::sendResetLink');
    $routes->get('reset/(:any)', 'PasswordController::resetPassword/$1');
    $routes->post('reset', 'PasswordController::updatePassword');
});

//---------------------------
// Admin Routes
//---------------------------
$routes->group('admin', ['namespace' => 'App\Controllers'], function($routes) {
    // Dashboard & Profile
    $routes->get('dashboard', 'AdminController::index');
    $routes->get('profile', 'AdminController::profile');
    $routes->post('updateProfile', 'AdminController::updateProfile');
    $routes->post('uploadProfilePicture', 'AdminController::uploadProfilePicture');
    $routes->post('editProfilePicture', 'AdminController::uploadProfilePicture');

    // User Management
    $routes->get('users', 'AdminController::users');
    $routes->get('users/create', 'AdminController::create');
    $routes->post('users/create', 'AdminController::create');
    $routes->get('users/(:segment)/edit', 'AdminController::edit/$1');
    $routes->post('users/(:segment)/edit', 'AdminController::edit/$1');
    $routes->get('users/(:segment)/delete', 'AdminController::delete/$1');
    $routes->post('users/(:segment)/delete', 'AdminController::delete/$1');

    // Truck Management
    $routes->get('trucks', 'AdminController::truck');
    $routes->get('trucks/view/(:segment)', 'AdminController::viewTruck/$1');
    $routes->post('trucks/create', 'AdminController::storeTruck');
    $routes->post('trucks/update/(:segment)', 'AdminController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'AdminController::deleteTruck/$1');

    // Driver/Conductor Management
    $routes->get('driver', 'AdminController::driverManagement');
    $routes->post('driver/create', 'AdminController::createDriver');
    $routes->get('driver/update/(:segment)', 'AdminController::updateDriver/$1');
    $routes->post('driver/update/(:segment)', 'AdminController::updateDriver/$1');
    $routes->get('driver/delete/(:segment)', 'AdminController::deleteDriver/$1');
    $routes->post('driver/delete/(:segment)', 'AdminController::deleteDriver/$1');
    $routes->get('driver/view/(:segment)', 'AdminController::viewDriver/$1');

    // Client Management
    $routes->get('clients', 'AdminController::clientManagement');
    $routes->get('clientView/(:any)', 'AdminController::clientView/$1');
    $routes->match(['get', 'post'], 'clientEdit/(:any)', 'AdminController::clientEdit/$1');

    // Maintenance & Geolocation
    $routes->get('maintenance', 'AdminController::Maintenance');
    $routes->get('geolocation', 'AdminController::geolocation');

    // Report Management
    $routes->get('reports', 'AdminController::Report');

    // Bookings Management
    $routes->get('bookings', 'AdminController::bookings');
    $routes->post('update-booking-status', 'AdminController::updateBookingStatus');

    // Logout
    $routes->get('logout', 'AdminController::logout');
});

//---------------------------
// Operations Coordinator Routes
//---------------------------
$routes->group('operations', ['namespace' => 'App\Controllers'], function($routes) {
    // Dashboard & Profile
    $routes->get('dashboard', 'StaffOcController::dashboard');
    $routes->get('dashboard/trucks-count', 'StaffOcController::trucksCount');
    $routes->get('profile', 'StaffOcController::profile');
    $routes->post('updateProfile', 'StaffOcController::updateProfile');
    $routes->post('uploadProfilePicture', 'StaffOcController::uploadProfilePicture');
    $routes->post('editProfilePicture', 'StaffOcController::uploadProfilePicture');

    // Truck Management
    $routes->get('trucks', 'StaffOcController::trucks');
    $routes->post('trucks/create', 'StaffOcController::createTruck');
    $routes->post('trucks/update/(:segment)', 'StaffOcController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'StaffOcController::deleteTruck/$1');
    $routes->get('trucks/view/(:segment)', 'StaffOcController::viewTruck/$1');

    // Geolocation
    $routes->get('geolocation', 'StaffOcController::geolocation');
    
    // Booking Management
    $routes->get('bookings', 'StaffOcController::bookings');
    $routes->get('bookings/view/(:segment)', 'StaffOcController::viewBooking/$1');
    $routes->post('update-booking-status', 'StaffOcController::updateBookingStatus');

    // Report Management
    $routes->get('reports', 'StaffOcController::Report');
    $routes->post('reports/saveRemark', 'StaffOcController::saveRemark');
});

//---------------------------
// Resource Manager Routes
//---------------------------
$routes->group('resource', ['namespace' => 'App\Controllers'], function($routes) {
    // Dashboard & Profile
    $routes->get('dashboard', 'StaffRmController::dashboard');
    $routes->get('profile', 'StaffRmController::profile');
    $routes->post('updateProfile', 'StaffRmController::updateProfile');
    $routes->post('uploadProfilePicture', 'StaffRmController::uploadProfilePicture');
    $routes->post('editProfilePicture', 'StaffRmController::uploadProfilePicture');
    
    // Truck Management
    $routes->get('trucks', 'StaffRmController::trucks');
    $routes->post('trucks/create', 'StaffRmController::storeTruck');
    $routes->post('trucks/update/(:segment)', 'StaffRmController::updateTruck/$1');
    $routes->get('trucks/delete/(:segment)', 'StaffRmController::deleteTruck/$1');
    $routes->get('trucks/view/(:segment)', 'StaffRmController::viewTruck/$1');

    // Maintenance & Geolocation
    $routes->get('maintenance', 'StaffRmController::maintenance');
    $routes->get('geolocation', 'StaffRmController::geolocation');

    // Report Management
    $routes->get('reports', 'StaffRmController::Report');
    $routes->post('reports/store', 'StaffRmController::storeReport');
});
