<?php
header('Content-Type: application/json');
require_once '../includes/security.php';
require_once '../includes/functions.php';

class DoctorAPI {
    private $db;

    public function __construct() {
        $this->db = new DatabaseConnection();
    }

    public function getPatients() {
        $query = "SELECT * FROM Users WHERE role = 'user'";
        $result = $this->db->query($query);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function addMedicalRecord($data) {
        $query = "INSERT INTO MedicalRecords 
                  (user_id, diagnosis, treatment_plan, record_date) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "iss", 
            $data['user_id'], 
            $data['diagnosis'], 
            $data['treatment_plan']
        );
        
        return $stmt->execute();
    }

    public function updateAppointmentStatus($appointmentId, $status) {
        $query = "UPDATE Appointments SET status = ? WHERE appointment_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $status, $appointmentId);
        
        return $stmt->execute();
    }
}

// Handle API Requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorAPI = new DoctorAPI();
    $data = json_decode(file_get_contents('php://input'), true);

    switch ($data['action']) {
        case 'get_patients':
            echo json_encode($doctorAPI->getPatients());
            break;
        case 'add_medical_record':
            echo json_encode($doctorAPI->addMedicalRecord($data));
            break;
        case 'update_appointment':
            echo json_encode($doctorAPI->updateAppointmentStatus(
                $data['appointment_id'], 
                $data['status']
            ));
            break;
    }
}
