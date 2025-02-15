<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Firebase Realtime Database Service
     *
     * Usage:
     *   1) $db = service('firebase');
     *   2) $db = Services::firebase();
     *
     * @param bool $getShared If true, returns a shared instance of the database.
     */
    public static function firebase($getShared = true)
    {
        if ($getShared) {
            // Return shared instance if it already exists
            return static::getSharedInstance('firebase');
        }

        // Load service account credentials from .env
        $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH');
        $databaseUri        = env('FIREBASE_DATABASE_URI');

        // Create a new Firebase Factory
        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri($databaseUri);

        // Return the Realtime Database instance
        return $factory->createDatabase();
    }
}

