<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Dashboard routes
$routes->get('/dashboard', 'DashboardController::dashboard');

// Profile routes
$routes->get('/profile', 'ProfileController::profile');
$routes->post('/profile',  'ProfileController::profile');
$routes->post('/profile/update', 'ProfileController::update');
// Client Routes
$routes->get('/clients', 'ClientController::clients');
$routes->post('/clients', 'ClientController::clients');
$routes->get('clients/view/(:num)', 'ClientController::view/$1');
