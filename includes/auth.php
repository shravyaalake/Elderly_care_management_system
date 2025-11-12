<?php
require_once dirname(__DIR__) . '/config/database.php';
// require_once 'C:/xampp/htdocs/elderly_healthcare/includes/auth.php';

// require_once dirname(__DIR__) . '/config/database.php';

class Auth {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register($name, $email, $password, $role, $additionalDetails = []) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Insert into users table
            $userQuery = "INSERT INTO users (name, email, password, role, age, phone_number) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->execute([
                $name, 
                $email, 
                $hashedPassword, 
                $role,
                $additionalDetails['age'] ?? null,
                $additionalDetails['phone_number'] ?? null
            ]);

            // Get the last inserted user_id
            $user_id = $this->conn->lastInsertId();

            // Insert role-specific details
            if ($role === 'patient') {
                $patientQuery = "INSERT INTO patient_details (user_id) VALUES (?)";
                $patientStmt = $this->conn->prepare($patientQuery);
                $patientStmt->execute([$user_id]);
            } elseif ($role === 'doctor') {
                $doctorQuery = "INSERT INTO doctor_details (user_id, specialization, license_number, consultation_fee) 
                                VALUES (?, ?, ?, ?)";
                $doctorStmt = $this->conn->prepare($doctorQuery);
                $doctorStmt->execute([
                    $user_id,
                    $additionalDetails['specialization'] ?? null,
                    $additionalDetails['license_number'] ?? null,
                    $additionalDetails['consultation_fee'] ?? null
                ]);
            }

            // Commit transaction
            $this->conn->commit();
            return $user_id;
        } catch(PDOException $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Registration Error: " . $e->getMessage());
            return false;
        }
    }
    public function verifyPassword($email, $password) {
        try {
            // Prepare query to fetch user's hashed password
            $dbHandler = new DatabaseHandler();
            $conn = $dbHandler->getConnection();
            
            $query = "SELECT password FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Verify password using password_verify
                return password_verify($password, $row['password']);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Password Verification Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = ?";
        $db = new DatabaseHandler();
        $stmt = $db->getConnection()->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        
        return false;
    }
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_destroy();
    }
}
?>
