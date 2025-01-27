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
//Driver Routes
$routes->get('/driver', 'DriverController::driver');
$routes->post('/driver', 'DriverController::driver');
$routes->get('/driver/getDetails/(:any)', 'DriverController::getDetails/$1');
