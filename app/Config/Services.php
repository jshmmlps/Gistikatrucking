<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

/**
 * Services Configuration file.
 */
class Services extends BaseService
{
    /**
     * Firebase Realtime Database Service
     *
     * Usage:
     *   $db = service('firebase');
     */
    public static function firebase($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('firebase');
        }

        $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH');
        $databaseUri        = env('FIREBASE_DATABASE_URI');

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath)
            ->withDatabaseUri($databaseUri);

        return $factory->createDatabase();
    }

    /**
     * Firebase Storage Service
     *
     * Usage:
     *   $storage = service('firebaseStorage');
     */
    public static function firebaseStorage($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('firebaseStorage');
        }

        $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH');
        // We won't set the bucket name here because .withDefaultBucket() isn't supported
        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath);

        // Return the Storage object
        return $factory->createStorage();
    }
}
