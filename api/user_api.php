<?php
header('Content-Type: application/json');
require_once '../includes/DatabaseHandler.php';

class UserAPI {
    private $db;

    public function __construct() {
        $this->db = new DatabaseHandler();
    }

    public function getAppointments($patientId) {
        $query = "SELECT * FROM appointments WHERE patient_id = ?";
        return $this->db->executeQuery($query, [$patientId]);
    }

    public function bookAppointment($data) {
        $query = "INSERT INTO appointments 
                  (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit) 
                  VALUES (?, ?, ?, ?, ?)";
        return $this->db->executeQuery($query, [
            $data['patient_id'], 
            $data['doctor_id'], 
            $data['appointment_date'],
            $data['appointment_time'],
            $data['reason_for_visit'] ?? null
        ]);
    }

    public function updateEmergencyContact($data) {
        $query = "INSERT INTO emergency_contacts 
                  (patient_id, contact_name, contact_relationship, contact_phone, contact_email) 
                  VALUES (?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE 
                  contact_name = ?, contact_relationship = ?, 
                  contact_phone = ?, contact_email = ?";
        
        return $this->db->executeQuery($query, [
            $data['patient_id'], 
            $data['contact_name'], 
            $data['contact_relationship'], 
            $data['contact_phone'], 
            $data['contact_email'],
            // Duplicate values for UPDATE
            $data['contact_name'], 
            $data['contact_relationship'], 
            $data['contact_phone'], 
            $data['contact_email']
        ]);
    }
}

// API Request Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userAPI = new UserAPI();
    $data = json_decode(file_get_contents('php://input'), true);

    $response = ['success' => false, 'message' => 'Invalid action'];

    switch ($data['action']) {
        case 'get_appointments':
            $response = $userAPI->getAppointments($data['patient_id']);
            break;
        case 'book_appointment':
            $response = $userAPI->bookAppointment($data);
            break;
        case 'update_emergency_contact':
            $response = $userAPI->updateEmergencyContact($data);
            break;
    }

    echo json_encode($response);
    exit();
}
?>
