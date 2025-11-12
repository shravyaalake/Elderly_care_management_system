<?php
session_start();
require_once '../includes/auth.php';
require_once '../includes/DatabaseHandler.php';

class AuthAPI {
    private $dbHandler;
    private $auth;

    public function __construct() {
        $this->dbHandler = new DatabaseHandler();
        $this->auth = new Auth();
    }

    public function login($email, $password) {
        // Validate input
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        // Check user credentials
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $conn = $this->dbHandler->getConnection();
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'patient':
                        $redirectUrl = '../views/patient/dashboard.php';
                        break;
                    case 'doctor':
                        $redirectUrl = '../views/doctor/dashboard.php';
                        break;
                    default:
                        $redirectUrl = '../public/index.php';
                }

                return [
                    'status' => 'success',
                    'message' => 'Login successful',
                    'role' => $user['role'],
                    'redirect' => $redirectUrl
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Invalid credentials'
        ];
    }

    public function logout() {
        // Destroy session
        session_unset();
        session_destroy();

        return [
            'status' => 'success',
            'message' => 'Logged out successfully',
            'redirect' => '../public/index.php'
        ];
    }
}

// Handle Login Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authAPI = new AuthAPI();
    
    // Check if it's a login or logout request
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $result = $authAPI->login($_POST['email'], $_POST['password']);
                
                if ($result['status'] === 'success') {
                    // Redirect to appropriate dashboard
                    header("Location: " . $result['redirect']);
                    exit();
                } else {
                    // Store error in session and redirect back to login
                    session_start();
                    $_SESSION['login_error'] = $result['message'];
                    header("Location: login_selection.php");
                    exit();
                }
                break;

            case 'logout':
                $result = $authAPI->logout();
                header("Location: " . $result['redirect']);
                exit();
                break;
        }
    }
}
?>
