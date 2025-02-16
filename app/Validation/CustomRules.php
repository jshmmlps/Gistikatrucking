<?php

namespace App\Validation;

class CustomRules
{
    /**
     * Ensure password is strong:
     * At least 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 symbol
     */
    public function check_password_strength(string $str): bool
    {
        // Regex pattern for:
        // - 1 uppercase:    (?=.*[A-Z])
        // - 1 lowercase:    (?=.*[a-z])
        // - 1 digit:        (?=.*\d)
        // - 1 special char: (?=.*[@$!%*?&])
        // - min length 8:   .{8,}
        $pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[^\\s]{8,}$/";
        return (bool) preg_match($pattern, $str);
    }
}
