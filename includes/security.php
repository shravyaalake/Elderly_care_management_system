
// class Security {
//     public static function sanitizeInput($input) {
//         $input = trim($input);
//         $input = stripslashes($input);
//         $input = htmlspecialchars($input);
//         return $input;
//     }

//     public static function generateCSRFToken() {
//         if (empty($_SESSION['csrf_token'])) {
//             $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
//         }
//         return $_SESSION['csrf_token'];
//     }

//     public static function validateCSRFToken($token) {
//         return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
//     }
// }

<?php
class Security {
    public static function sanitizeInput($input) {
        return htmlspecialchars(strip_tags(trim($input)));
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    // Add the verifyPassword method to resolve the undefined method error
    public static function verifyPassword($inputPassword, $storedHash) {
        return password_verify($inputPassword, $storedHash);
    }

    public static function generateToken() {
        return bin2hex(random_bytes(32));
    }
}
