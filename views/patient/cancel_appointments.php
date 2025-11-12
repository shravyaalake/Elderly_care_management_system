<?php
session_start();
require_once '../../includes/DatabaseHandler.php';

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'patient') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$dbHandler = new DatabaseHandler();

try {
    // Get patient_id from patient_details
    $patientDetails = $dbHandler->getPatientDetailsByUserId($_SESSION['user_id']);
    
    if (!$patientDetails) {
        throw new Exception("Patient details not found");
    }

    $appointmentId = $_POST['appointment_id'];
    $patientId = $patientDetails['patient_id'];

    // Cancel the appointment
    $result = $dbHandler->cancelAppointment($appointmentId, $patientId);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Appointment cancelled successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Unable to cancel appointment: ' . $e->getMessage()
    ]);
}
exit();
?>
