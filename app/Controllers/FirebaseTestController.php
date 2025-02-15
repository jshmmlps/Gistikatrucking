<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class FirebaseTestController extends Controller
{
    public function index()
    {
        // Retrieve Firebase Database from service container
        $db = service('firebase'); 
        // or: $db = \Config\Services::firebase();

        // Let's create or update a simple "test" node in the Realtime Database
        $reference = $db->getReference('testNode'); // testNode is a top-level key

        // Set some test data
        $reference->set([
            'timestamp' => date('Y-m-d H:i:s'),
            'message'   => 'Hello from CodeIgniter 4!'
        ]);

        return 'Firebase test node created/updated successfully!';
    }

    public function read()
    {
        $db = service('firebase');
        $reference = $db->getReference('testNode');

        $snapshot = $reference->getSnapshot();
        if ($snapshot->exists()) {
            $data = $snapshot->getValue();
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        } else {
            echo 'testNode does not exist in Firebase.';
        }
    }
}
