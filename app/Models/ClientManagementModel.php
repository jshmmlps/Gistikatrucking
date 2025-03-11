<?php
namespace App\Models;

use Config\Services;

class ClientManagementModel {

    protected $db;

    public function __construct() {
        // Get the Firebase Realtime Database instance from your Services config
        $this->db = Services::firebase(false);
    }

    // Retrieve all clients from the Users node
    public function getAllClients() {
        $usersRef = $this->db->getReference('Users');
        return $usersRef->getValue();
    }

    // Get a specific client using its key/ID
    public function getClient($clientId) {
        $userRef = $this->db->getReference('Users/' . $clientId);
        return $userRef->getValue();
    }

    // Retrieve the latest booking for a given client
    public function getLastBooking($clientId) {
        $bookingsRef = $this->db->getReference('Bookings');
        // Query bookings that match the client_id and limit to the last record
        $query = $bookingsRef->orderByChild('client_id')->equalTo($clientId)->limitToLast(1);
        $bookings = $query->getSnapshot()->getValue();
        if ($bookings) {
            return array_values($bookings)[0];
        }
        return null;
    }

    // Update client details (e.g. Business Type and Payment Mode)
    public function updateClient($clientId, $data) {
        $userRef = $this->db->getReference('Users/' . $clientId);
        return $userRef->update($data);
    }
}
