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
     * @return string The assigned key, e.g. "user7".
     */
    public function createUser(array $data)
    {
        // 1) Find the first free "userX"
        $i = 1;
        $newKey = '';

        while (true) {
            $attemptKey = 'User' . $i;
            // Check if /Users/userX exists
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

        // 3) Write the data to "/Users/userX"
        $this->db->getReference('Users/' . $newKey)->set($data);

        // Return the new key, e.g. "user7"
        return $newKey;
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
            if (isset($userData[$field]) && $userData[$field] === $value) {
                // Include the Firebase key in the returned data
                $userData['firebaseKey'] = $key;
                return $userData;
            }
        }

        return null;
    }

    /**
     * Verify user credentials (username & plain password).
     *
     * @param string $username
     * @param string $plainPassword
     * @return array|null user data if successful, null if fail
     */
    public function verifyCredentials($username, $plainPassword)
    {
        $user = $this->getUserByField('username', $username);
        if ($user === null) {
            return null; // no such username
        }

        // Check hashed password
        if (!password_verify($plainPassword, $user['password'])) {
            return null; // incorrect password
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

}
