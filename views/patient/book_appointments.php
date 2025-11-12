<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/DatabaseHandler.php';

// Authentication Check
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'patient') {
    header("Location: ../../public/index.php");
    exit();
}

// Initialize DatabaseHandler
$dbHandler = new DatabaseHandler();

// Handle Appointment Booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get patient_id from patient_details
        $patientDetails = $dbHandler->getPatientDetailsByUserId($_SESSION['user_id']);
        
        if (!$patientDetails) {
            throw new Exception("Patient details not found");
        }

        $appointmentData = [
            'patient_id' => $patientDetails['patient_id'],
            'doctor_id' => $_POST['doctor_id'],
            'appointment_date' => $_POST['appointment_date'],
            'appointment_time' => $_POST['appointment_time'],
            'reason_for_visit' => $_POST['reason_for_visit'],
            'status' => 'scheduled'
        ];

        $result = $dbHandler->bookAppointment($appointmentData);
        
        header("Location: appointments.php?success=1");
        exit();
    } catch (Exception $e) {
        $error = "Booking failed: " . $e->getMessage();
        // Log the error
        error_log($error);
    }
}

// Redirect back to appointments page if something goes wrong
header("Location: appointments.php");
exit();
?>
