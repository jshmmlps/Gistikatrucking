<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Services;

class UserModel extends Model
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        // Initialize Firebase Realtime Database from your Services
        $this->db = Services::firebase();
    }

    /**
     * Create a user with a manual "userX" key in Firebase (no transaction).
     *
     * Steps:
     *   1) Start from user1, check if it exists at "/Users/user1".
     *   2) If it exists, move to user2, user3, etc., until we find one that doesn't exist.
     *   3) Store the user data under "/Users/userX".
     *
     * @param array $data Associative array of user data (already validated).
     * @return string The assigned key, e.g. "User7".
     */
    public function createUser(array $data)
    {
        // 1) Find the first free "UserX"
        $i = 1;
        $newKey = '';

        while (true) {
            $attemptKey = 'User' . $i;
            // Check if /Users/UserX exists
            $snapshot = $this->db->getReference('Users/' . $attemptKey)->getSnapshot();

            if (!$snapshot->exists()) {
                // Found a free ID (does not exist yet)
                $newKey = $attemptKey;
                break;
            }
            $i++;
        }

        // 2) Fill in additional data
        $data['user_id']   = $i; // numeric user ID
        $data['createdAt'] = date('Y-m-d H:i:s');

        // 3) Write the data to "/Users/UserX"
        $this->db->getReference('Users/' . $newKey)->set($data);

        // Return the new key, e.g. "User7"
        return $newKey;
    }

    /**
     * Fetch all users (returns an associative array).
     */
    public function getAllUsers()
    {
        $snapshot = $this->db->getReference('Users')->getSnapshot();
        $users = $snapshot->getValue() ?? []; // array or null
        return $users;
    }

    /**
     * Fetch a single user by the 'UserX' key.
     */
    public function getUser($userKey)
    {
        $snapshot = $this->db->getReference('Users/' . $userKey)->getSnapshot();
        return $snapshot->getValue(); // or null if not exists
    }

    /**
     * Update an existing user.
     */
    public function updateUser($userKey, array $data)
    {
        // Update with a 'merge' approach
        $this->db->getReference('Users/' . $userKey)->update($data);
        return true;
    }

    /**
     * Delete a user.
     */
    public function deleteUser($userKey)
    {
        $this->db->getReference('Users/' . $userKey)->remove();
        return true;
    }

    /**
     * Retrieve a user by a given field & value (e.g. "username" => "john").
     * Scans all records in /Users to find a match.
     *
     * @param string $field
     * @param string $value
     * @return array|null user data if found, otherwise null
     */
    public function getUserByField($field, $value)
    {
        $reference = $this->db->getReference('Users');
        $snapshot = $reference->getSnapshot();
    
        if (!$snapshot->exists()) {
            return null;
        }
    
        $users = $snapshot->getValue();  // all /Users
        foreach ($users as $key => $userData) {
            if ($field === 'username') {
                // Compare usernames in a case-insensitive way
                if (isset($userData[$field]) && strtolower($userData[$field]) === strtolower($value)) {
                    $userData['firebaseKey'] = $key;
                    return $userData;
                }
            } else {
                // For email and other fields, use strict comparison
                if (isset($userData[$field]) && $userData[$field] === $value) {
                    $userData['firebaseKey'] = $key;
                    return $userData;
                }
            }
        }
        return null;
    }
    
    /**
     * Verify user credentials (username & plain password).
     *
     * @param string $identifier
     * @param string $plainPassword
     * @return array|null user data if successful, null if fail
     */
    public function verifyCredentials($identifier, $plainPassword)
    {
        // Check if the identifier is an email address
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user = $this->getUserByField('email', $identifier);
        } else {
            // Otherwise, treat it as a username (case-insensitive)
            $user = $this->getUserByField('username', $identifier);
        }

        if ($user === null) {
            return null; // No such user found
        }

        // Verify the password using the hashed value stored in Firebase
        if (!password_verify($plainPassword, $user['password'])) {
            return null; // Incorrect password
        }

        return $user;
    }

    /**
     * Set the reset token and its expiration for the given email.
     *
     * @param string $email
     * @param string $token
     * @param int    $expiration Timestamp when token expires
     * @return bool
     */
    public function setResetToken(string $email, string $token, int $expiration): bool
    {
        $user = $this->getUserByField('email', $email);
        if (!$user) {
            return false;
        }
        $firebaseKey = $user['firebaseKey'];
        $data = [
            'reset_token' => $token,
            'reset_token_expiration' => $expiration
        ];
        $this->db->getReference('Users/' . $firebaseKey)->update($data);
        return true;
    }

    /**
     * Verify a reset token and check if it is not expired.
     *
     * @param string $token
     * @return array|null The user data if valid, or null if invalid.
     */
    public function verifyResetToken(string $token)
    {
        $user = $this->getUserByField('reset_token', $token);
        if (!$user) {
            return null;
        }
        if (time() > $user['reset_token_expiration']) {
            return null;
        }
        return $user;
    }

    /**
     * Update the user's password and remove the reset token.
     *
     * @param string $firebaseKey
     * @param string $newPassword Plain text password
     * @return bool
     */
    public function updatePassword(string $firebaseKey, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->db->getReference('Users/' . $firebaseKey)->update([
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expiration' => null,
        ]);
        return true;
    }
    
    /**
     * Retrieve eligible users who have a user_level of "driver" or "conductor".
     *
     * This method scans all user records and returns only those users.
     * It also attaches the Firebase key to each returned record.
     *
     * @return array Eligible users.
     */
    public function getEligibleUsers()
    {
        $users = $this->getAllUsers();
        $eligible = [];
        if ($users) {
            foreach ($users as $key => $user) {
                if (isset($user['user_level']) && in_array(strtolower($user['user_level']), ['driver', 'conductor'])) {
                    // Attach the Firebase key so you can reference it later
                    $user['firebaseKey'] = $key;
                    $eligible[$key] = $user;
                }
            }
        }
        return $eligible;
    }

    /**
     * Get a user (and its Firebase key) by email.
     * Returns an array: ['firebase_key' => '...', 'userData' => [...]] or null if not found.
     */
    public function getUserByEmail($email)
    {
        // Reference the entire /Users node
        $usersRef = $this->db->getReference('Users');
        $users = $usersRef->getValue();

        if (!$users || !is_array($users)) {
            return null; // No users found
        }

        // Loop through each child (e.g. User1, User2, etc.)
        foreach ($users as $firebaseKey => $userData) {
            // Safety check: skip non-arrays
            if (!is_array($userData)) {
                continue;
            }
            // Compare emails (case-sensitive or insensitive as you prefer)
            if (isset($userData['email']) && strtolower($userData['email']) === strtolower($email)) {
                // Return both the user data and the Firebase key
                return [
                    'firebase_key' => $firebaseKey,
                    'userData'     => $userData
                ];
            }
        }

        return null; // No match
    }

    /**
     * Optionally, if you want to look up a user by the numeric user_id instead of the email:
     */
    public function getUserByNumericId($numericId)
    {
        $usersRef = $this->db->getReference('Users');
        $users    = $usersRef->getValue();

        if (!$users || !is_array($users)) {
            return null;
        }

        foreach ($users as $firebaseKey => $userData) {
            if (isset($userData['user_id']) && (int)$userData['user_id'] === (int)$numericId) {
                // We found the user
                return [
                    'firebase_key' => $firebaseKey,
                    'userData'     => $userData
                ];
            }
        }
        return null;
    }
}
