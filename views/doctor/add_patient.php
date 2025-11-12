<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/DatabaseHandler.php';

// Authentication Check
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../public/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $dbHandler = new DatabaseHandler();
        
        // Validate input data
        $errors = [];
        
        if (empty($_POST['name'])) {
            $errors[] = "Patient name is required";
        }
        
        if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address";
        }
        
        if (empty($_POST['age']) || $_POST['age'] < 18 || $_POST['age'] > 120) {
            $errors[] = "Invalid age. Must be between 18 and 120";
        }
        
        if (!empty($errors)) {
            throw new Exception(implode(", ", $errors));
        }

        // Prepare patient data
        $patientData = [
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'age' => $_POST['age'],
            'contact_info' => $_POST['contact_info'],
            'doctor_id' => $_SESSION['user_id'], // Associate patient with current doctor
            'role' => 'patient' // Set role explicitly
        ];

        // Add patient through Auth class
        $auth = new Auth();
        $newPatientId = $auth->register(
            $patientData['name'], 
            $patientData['email'], 
            // Generate temporary password
            password_hash(uniqid(), PASSWORD_BCRYPT), 
            'patient', 
            $patientData
        );

        if ($newPatientId) {
            // Redirect with success message
            $_SESSION['message'] = "Patient added successfully!";
            header("Location: patients.php");
            exit();
        } else {
            throw new Exception("Failed to add patient");
        }

    } catch (Exception $e) {
        // Redirect with error message
        $_SESSION['error'] = $e->getMessage();
        header("Location: patients.php");
        exit();
    }
} else {
    // Direct access prevention
    header("Location: patients.php");
    exit();
}
?>
