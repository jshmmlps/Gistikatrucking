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
$routes->get('clients/view/(:num)', 'ClientController::view/$1');
$routes->post('clients/view/(:num)', 'ClientController::view/$1');
//Driver Routes
$routes->get('/drivers', 'DriverController::drivers');
$routes->post('/drivers', 'DriverController::drivers');
$routes->get('/drivers/getDetails/(:any)', 'DriverController::getDetails/$1');
//Booking Routes
$routes->get('/booking', 'BookingController::booking');
$routes->post('/booking', 'BookingController::booking');


