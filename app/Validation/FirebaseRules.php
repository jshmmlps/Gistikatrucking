<?php

namespace App\Validation;

use Config\Services;

class FirebaseRules
{
    /**
     * Check if a value is unique in Firebase for the given node/field.
     * E.g. "is_unique_in_firebase[Users.email]"
     */
    public function is_unique_in_firebase(string $str, string $fieldAndPath, array $data): bool
    {
        // $fieldAndPath might be "Users.email"
        // so we'll parse it to get node = "Users" and subField = "email"
        [$node, $field] = explode('.', $fieldAndPath);

        $db = Services::firebase();
        $snapshot = $db->getReference($node)->getSnapshot();

        if (!$snapshot->exists()) {
            return true; // no data => definitely unique
        }

        $allUsers = $snapshot->getValue();
        foreach ($allUsers as $key => $user) {
            if (isset($user[$field]) && $user[$field] === $str) {
                return false;
            }
        }

        return true;
    }
}
