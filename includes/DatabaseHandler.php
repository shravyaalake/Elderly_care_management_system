<?php
class DatabaseHandler {
    public $conn; // mysqli connection
    private $pdo; // PDO connection

    public function __construct() {
        try {
            // MySQL Connection using mysqli
            $this->conn = new mysqli('localhost', '', '', 'dbms');
            if ($this->conn->connect_error) {
                throw new Exception("Mysqli Connection failed: " . $this->conn->connect_error);
            }

            // PDO Connection
            $host = 'localhost';
            $dbname = '';
            $username = '';
            $password = '';

            // Create PDO connection
            $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
            
            // Connection options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // Initialize PDO connection
            $this->pdo = new PDO($dsn, $username, $password, $options);

        } catch (Exception $e) {
            // Log detailed error
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    // Method to get mysqli connection
    public function getConnection() {
        return $this->conn;
    }

    // Method to get PDO connection
    public function getPdo() {
        return $this->pdo;
    }
    public function executeQuery($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters dynamically
            if (!empty($params)) {
                $types = str_repeat('s', count($params)); // Default to string
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Return different result based on query type
            if (strpos(strtoupper($query), 'SELECT') === 0) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                return $stmt->affected_rows > 0;
            }
        } catch (Exception $e) {
            error_log("Query Execution Error: " . $e->getMessage());
            return false;
        }
    }

    public function getHealthVitals($patientId) {
        $query = "SELECT * FROM health_vitals WHERE patient_id = ? ORDER BY recorded_date DESC, recorded_time DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $patientId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

// Fetch Appointments for Doctor
public function getAppointments($doctorId) {
    $query = "SELECT a.*, u.name AS patient_name 
              FROM appointments a
              JOIN patient_details pd ON pd.patient_id=a.patient_id
              JOIN users u ON pd.user_id = u.user_id
              WHERE a.doctor_id = ? 
              AND a.status = 'scheduled'
              ORDER BY a.appointment_date 
              LIMIT 5";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// Fetch Medical Records
public function fetchMedicalRecords($doctorId) {
    $query = "SELECT mr.*, u.name AS patient_name 
              FROM medical_records mr
              JOIN users u ON mr.patient_id = u.user_id
              WHERE mr.doctor_id = ?
              ORDER BY mr.date_of_record DESC 
              LIMIT 5";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// }public function getUserDetailsByEmail($email) {
//     $query = "SELECT user_id, name, email, role FROM users WHERE email = ?";
//     $stmt = $this->conn->prepare($query);
//     $stmt->bind_param("s", $email);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     return $result->fetch_assoc();
// }
// public function getAppointments($doctorId) {
//     $query = "SELECT a.*, u.name AS patient_name 
//               FROM appointments a
//               JOIN users u ON a.patient_id = u.user_id
//               WHERE a.doctor_id = ?
//               ORDER BY a.appointment_date DESC";
//     $stmt = $this->conn->prepare($query);
//     $stmt->bind_param("i", $doctorId);
//     $stmt->execute();
//     return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
// }

public function getAllPatients() {
    $query = "SELECT user_id, name FROM users WHERE role = 'patient'";
    $result = $this->conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

public function scheduleAppointment($data) {
    $query = "INSERT INTO appointments 
              (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "iissss", 
        $data['patient_id'], 
        $data['doctor_id'], 
        $data['appointment_date'], 
        $data['appointment_time'], 
        $data['reason_for_visit'], 
        $data['status']
    );
    return $stmt->execute();
}






public function updateHealthVital($data) {
    $query = "UPDATE health_vitals 
              SET value = ?, recorded_date = NOW() 
              WHERE vital_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("di", $data['value'], $data['vital_id']);
    return $stmt->execute();
}


// Method to delete medication
public function deleteMedication($medicationId) {
    $query = "DELETE FROM Medications WHERE medication_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $medicationId);
    return $stmt->execute();
}
public function updateDoctorProfile($data) {
    try {
        // Start transaction
        $this->conn->begin_transaction();

        // Update users table
        $userQuery = "UPDATE users SET 
                      name = ?, 
                      phone_number = ?, 
                      email = ? 
                      WHERE user_id = ?";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->bind_param(
            "sssi", 
            $data['name'], 
            $data['phone_number'], 
            $data['email'], 
            $data['user_id']
        );
        $userStmt->execute();

        // Update doctor_details table
        $doctorQuery = "UPDATE doctor_details SET 
                        specialization = ? 
                        WHERE user_id = ?";
        $doctorStmt = $this->conn->prepare($doctorQuery);
        $doctorStmt->bind_param(
            "si", 
            $data['specialization'], 
            $data['user_id']
        );
        $doctorStmt->execute();

        // Update password if provided
        if (isset($data['password'])) {
            $passwordQuery = "UPDATE users SET password = ? WHERE user_id = ?";
            $passwordStmt = $this->conn->prepare($passwordQuery);
            $passwordStmt->bind_param(
                "si", 
                $data['password'], 
                $data['user_id']
            );
            $passwordStmt->execute();
        }
        
        // Update users table
        $userQuery = "UPDATE users SET 
                      name = ?, 
                      phone_number = ?, 
                      email = ? 
                      WHERE user_id = ?";
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->bind_param(
            "sssi", 
            $data['name'], 
            $data['phone_number'], 
            $data['email'], 
            $data['user_id']
        );
        $userStmt->execute();

        // Update doctor_details table
        $doctorQuery = "UPDATE doctor_details SET 
                        specialization = ?, 
                        consultation_fee = ?, 
                        available_days = ?, 
                        consultation_hours_start = ?, 
                        consultation_hours_end = ? 
                        WHERE user_id = ?";
        $doctorStmt = $this->conn->prepare($doctorQuery);
        
        // Convert available days array to comma-separated string
        $availableDays = is_array($data['available_days']) 
            ? implode(',', $data['available_days']) 
            : $data['available_days'];

        $doctorStmt->bind_param(
            "sdsssi", 
            $data['specialization'], 
            $data['consultation_fee'], 
            $availableDays,
            $data['consultation_hours_start'], 
            $data['consultation_hours_end'], 
            $data['user_id']
        );
        $doctorStmt->execute();

        // Update password if provided
        if (isset($data['password'])) {
            $passwordQuery = "UPDATE users SET password = ? WHERE user_id = ?";
            $passwordStmt = $this->conn->prepare($passwordQuery);
            $passwordStmt->bind_param(
                "si", 
                $data['password'], 
                $data['user_id']
            );
            $passwordStmt->execute();
        }


        // Commit transaction
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $this->conn->rollback();
        error_log("Profile Update Error: " . $e->getMessage());
        return false;
    }
}

public function getUserDetailsByEmail($email) {
    $query = "SELECT user_id, name, email, role FROM users WHERE email = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}



public function getUserDetails($userId) {
    $query = "SELECT u.name, u.phone_number, u.email, 
              COALESCE(d.specialization, 'Not Specified') AS specialization
              FROM users u
              LEFT JOIN doctor_details d ON u.user_id = d.user_id 
              WHERE u.user_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}


public function getUserHealthAlerts($userId) {
    $query = "SELECT alert_message 
              FROM health_alerts 
              WHERE user_id = ? 
              AND status = 'active'
              ORDER BY created_at DESC";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $alerts = [];
    while ($row = $result->fetch_assoc()) {
        $alerts[] = $row['alert_message'];
    }
    return $alerts;
}


// public function getPatientMedications($userId) {
//     $query = "SELECT medication_name, dosage, frequency, start_date 
//               FROM medications 
//               WHERE patient_id = ?
//               AND (end_date IS NULL OR end_date >= CURDATE())
//               ORDER BY start_date DESC";
    
//     $stmt = $this->conn->prepare($query);
//     $stmt->bind_param("i", $userId);
//     $stmt->execute();
    
//     $result = $stmt->get_result();
//     return $result->fetch_all(MYSQLI_ASSOC);
// }



public function fetchPatientsByDoctor($doctorId) {
    $query = "SELECT DISTINCT 
                u.user_id AS patient_id, 
                u.name, 
                u.age, 
                u.phone_number AS contact_info,
                (SELECT MAX(appointment_date) 
                 FROM appointments 
                 WHERE patient_id = pd.patient_id) AS last_appointment,
                (SELECT COUNT(DISTINCT medication_id) 
                 FROM medications 
                 WHERE patient_id = pd.patient_id AND doctor_id = ?) AS total_medications
              FROM users u
              JOIN patient_details pd ON u.user_id = pd.user_id
              JOIN appointments a ON pd.patient_id = a.patient_id
              WHERE a.doctor_id = ?
              ORDER BY last_appointment DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $doctorId, $doctorId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


public function getUserMedications($userId) {
    $query = "SELECT m.medication_name, m.dosage, m.frequency, m.start_date 
              FROM medications m
              WHERE m.patient_id = ?
              AND (m.end_date IS NULL OR m.end_date >= CURDATE())
              ORDER BY m.start_date DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}



public function getAvailableDoctors() {
    $query = "SELECT u.user_id AS doctor_id, u.name, 
              COALESCE(d.specialization, 'General Practitioner') AS specialization
              FROM users u
              LEFT JOIN doctor_details d ON u.user_id = d.doctor_id
              WHERE u.role = 'doctor'";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
// public function getPatientDetails($patientId) {
//     $query = "SELECT u.name, u.age, u.phone_number AS contact_info, 
//               '' AS address, pd.patient_id 
//               FROM users u
//               JOIN patient_details pd ON u.user_id = pd.user_id
//               WHERE pd.patient_id = ?";
    
//     $stmt = $this->conn->prepare($query);
//     $stmt->bind_param("i", $patientId);
//     $stmt->execute();
    
//     $result = $stmt->get_result();
//     $patientDetails = $result->fetch_assoc() ?? [
//         'name' => 'N/A',
//         'age' => 'N/A', 
//         'contact_info' => 'N/A',
//         'address' => 'N/A'
//     ];

//     // Add medical history with default empty arrays
//     $patientDetails['medical_history'] = [
//         'chronic_conditions' => [], 
//         'allergies' => [] 
//     ];

//     return $patientDetails;
// }






public function getPatientDetailsByUserId($userId) {
    $query = "SELECT patient_id FROM patient_details WHERE user_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

public function getUserAppointments($userId) {
    // Get patient_id first
    $patientDetails = $this->getPatientDetailsByUserId($userId);
    
    if (!$patientDetails) {
        return [];
    }

    $query = "SELECT a.*, u.name AS doctor_name 
              FROM appointments a
              JOIN users u ON a.doctor_id = u.user_id
              WHERE a.patient_id = ?
              ORDER BY a.appointment_date DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $patientDetails['patient_id']);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function bookAppointment($data) {
    $query = "INSERT INTO appointments 
              (patient_id, doctor_id, appointment_date, appointment_time, reason_for_visit, status) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "iissss", 
        $data['patient_id'],
        $data['doctor_id'], 
        $data['appointment_date'],
        $data['appointment_time'],
        $data['reason_for_visit'], 
        $data['status']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Appointment booking failed: " . $stmt->error);
    }
    
    return true;
}

public function cancelAppointment($appointmentId, $patientId) {
    $query = "UPDATE appointments 
              SET status = 'cancelled' 
              WHERE appointment_id = ? AND patient_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $appointmentId, $patientId);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to cancel appointment: " . $stmt->error);
    }
    
    return true;
}


public function updateEmergencyContact($data) {
    $query = "INSERT INTO emergencycontacts 
              (patient_id, contact_name, contact_relationship, contact_phone, contact_email) 
              VALUES (?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE 
              contact_name = VALUES(contact_name), 
              contact_relationship = VALUES(contact_relationship), 
              contact_phone = VALUES(contact_phone), 
              contact_email = VALUES(contact_email)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "issss", 
        $data['patient_id'], 
        $data['contact_name'], 
        $data['contact_relationship'], 
        $data['contact_phone'], 
        $data['contact_email']
    );
    
    return $stmt->execute();
}
public function getUserProfile($userId) {
    $query = "SELECT name, age, phone_number, email 
              FROM users 
              WHERE user_id = ? AND role = 'patient'";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

public function getPatientMedicalHistory($patientId) {
    // Fetch medical history details
    return [
        'chronic_conditions' => $this->getChronicConditions($patientId),
        'allergies' => $this->getAllergies($patientId)
    ];
}



public function getPatientDetails($patientId) {
    // Join patient_details with users to get complete patient information
    $query = "SELECT u.*, pd.patient_id 
              FROM users u
              JOIN patient_details pd ON u.user_id = pd.user_id
              WHERE pd.patient_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $patientDetails = $result->fetch_assoc();

    // Fetch medical records
    $patientDetails['medical_records'] = $this->getPatientMedicalRecords($patientId);

    // Fetch medications
    $patientDetails['medications'] = $this->getPatientMedications($patientId);

    // Fetch medical history (if applicable)
    $patientDetails['medical_history'] = [
        'chronic_conditions' => $this->getChronicConditions($patientId),
        'allergies' => $this->getAllergies($patientId)
    ];

    return $patientDetails;
}

private function getChronicConditions($patientId) {
    // Implement logic to fetch chronic conditions
    // This might require an additional table in your schema
    return []; // Return empty array if no conditions found
}

private function getAllergies($patientId) {
    // Implement logic to fetch allergies
    // This might require an additional table in your schema
    return []; // Return empty array if no allergies found
}

public function getPatientMedicalRecords($patientId) {
    $query = "SELECT mr.*, u.name AS doctor_name
              FROM medical_records mr
              JOIN users u ON mr.doctor_id = u.user_id
              WHERE mr.patient_id = ?
              ORDER BY mr.date_of_record DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

public function getPatientMedications($patientId) {
    $query = "SELECT m.medication_name, m.dosage, m.frequency, 
              m.start_date, m.end_date, u.name AS prescribed_by
              FROM medications m
              JOIN users u ON m.doctor_id = u.user_id
              WHERE m.patient_id = ?
              AND (m.end_date IS NULL OR m.end_date >= CURDATE())
              ORDER BY m.start_date DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// public function addHealthVital($data) {
//     // First, get the patient_id from patient_details
//     $patientQuery = "SELECT patient_id FROM patient_details WHERE user_id = ?";
//     $stmt = $this->conn->prepare($patientQuery);
//     $stmt->bind_param("i", $data['user_id']);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     if ($result->num_rows === 0) {
//         throw new Exception("No patient details found for this user");
//     }
    
//     $patientDetails = $result->fetch_assoc();
//     $patientId = $patientDetails['patient_id'];

//     // Now insert health vitals using the patient_id
//     $query = "INSERT INTO health_vitals 
//               (patient_id, recorded_date, recorded_time, 
//                blood_pressure, heart_rate, blood_sugar_level, 
//                temperature, notification_sent) 
//               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
//     $stmt = $this->conn->prepare($query);
//     $stmt->bind_param(
//         "issssssi", 
//         $patientId, 
//         $data['recorded_date'], 
//         $data['recorded_time'], 
//         $data['blood_pressure'], 
//         $data['heart_rate'], 
//         $data['blood_sugar_level'], 
//         $data['temperature'], 
//         $data['notification_sent']
//     );
    
//     return $stmt->execute();
// }


public function deleteMedicalRecord($recordId, $doctorId) {
    $query = "DELETE FROM medical_records 
              WHERE record_id = ? AND doctor_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("ii", $recordId, $doctorId);
    
    return $stmt->execute();
}
public function addMedicalRecord($data) {
    $query = "INSERT INTO medical_records 
              (patient_id, doctor_id, diagnosis, treatment, prescriptions, date_of_record, report_generated) 
              VALUES (?, ?, ?, ?, ?, ?, 0)";
    
    $stmt = $this->conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Statement preparation failed: " . $this->conn->error);
    }
    
    $stmt->bind_param(
        "iissss", 
        $data['patient_id'], 
        $data['doctor_id'], 
        $data['diagnosis'], 
        $data['treatment'], 
        $data['prescriptions'], 
        $data['date_of_record']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Medical record insertion failed: " . $stmt->error);
    }
    
    return true;
}
public function updateMedicalRecord($data) {
    $query = "UPDATE medical_records 
              SET patient_id = ?, 
                  diagnosis = ?, 
                  treatment = ?, 
                  prescriptions = ?, 
                  date_of_record = ? 
              WHERE record_id = ? AND doctor_id = ?";
    
    $stmt = $this->conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Statement preparation failed: " . $this->conn->error);
    }
    
    $stmt->bind_param(
        "issssis", 
        $data['patient_id'], 
        $data['diagnosis'], 
        $data['treatment'], 
        $data['prescriptions'] ?? null, 
        $data['date_of_record'], 
        $data['record_id'], 
        $data['doctor_id']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Medical record update failed: " . $stmt->error);
    }
    
    return true;
}
public function fetchmedical_records($doctorId) {
    $query = "SELECT mr.*, u.name as patient_name 
              FROM medical_records mr 
              JOIN patient_details pd ON mr.patient_id = pd.patient_id JOIN users u ON 
              u.user_id=pd.user_id
              WHERE mr.doctor_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
public function getAllMedications($doctorId) {
    $query = "SELECT m.medication_id, 
                     u.name AS patient_name, 
                     m.medication_name, 
                     m.dosage, 
                     m.frequency, 
                     m.start_date, 
                     m.end_date 
              FROM medications m
              JOIN patient_details pd ON pd.patient_id = m.patient_id
              JOIN users u ON u.user_id=pd.user_id
              WHERE m.doctor_id = ?
              ORDER BY m.start_date DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// public function addMedication($medicationData) {
//     $query = "INSERT INTO medications 
//               (patient_id, doctor_id, medication_name, dosage, frequency, start_date, end_date) 
//               VALUES (?, ?, ?, ?, ?, ?, ?)";
    
//     $stmt = $this->conn->prepare($query);
//     $stmt->bind_param(
//         "iissssss", 
//         $medicationData['patient_id'], 
//         $medicationData['doctor_id'],
//         $medicationData['medication_name'], 
//         $medicationData['dosage'], 
//         $medicationData['frequency'], 
//         $medicationData['start_date'], 
//         $medicationData['end_date']
//     );
    
//     return $stmt->execute();
// }

public function updateMedication($medicationData) {
    $query = "UPDATE medications 
              SET medication_name = ?, 
                  dosage = ?, 
                  frequency = ?, 
                  start_date = ?, 
                  end_date = ? 
              WHERE medication_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "sssssi", 
        $medicationData['medication_name'], 
        $medicationData['dosage'], 
        $medicationData['frequency'], 
        $medicationData['start_date'], 
        $medicationData['end_date'], 
        $medicationData['medication_id']
    );
    
    return $stmt->execute();
}
public function getPatients() {
    $query = "SELECT u.user_id, u.name 
              FROM users u
              JOIN patient_details pd ON u.user_id = pd.user_id
              WHERE u.role = 'patient'";
    
    $result = $this->conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $this->conn->error);
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
public function addMedication($medicationData) {
    // Ensure patient_id is correctly retrieved
    $query = "INSERT INTO medications 
              (patient_id, doctor_id, medication_name, dosage, frequency, start_date, end_date) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $this->conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Statement preparation failed: " . $this->conn->error);
    }
    
    // Verify that patient_id is set and not null
    if (!isset($medicationData['patient_id']) || empty($medicationData['patient_id'])) {
        // Try to get patient_id from patient_details if not provided
        $patientQuery = "SELECT patient_id FROM patient_details WHERE user_id = ?";
        $patientStmt = $this->conn->prepare($patientQuery);
        $patientStmt->bind_param("i", $medicationData['user_id']);
        $patientStmt->execute();
        $result = $patientStmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("No patient found for the given user ID");
        }
        
        $patientDetails = $result->fetch_assoc();
        $medicationData['patient_id'] = $patientDetails['patient_id'];
    }
    
    $stmt->bind_param(
        "iisssss", 
        $medicationData['patient_id'], 
        $medicationData['doctor_id'], 
        $medicationData['medication_name'], 
        $medicationData['dosage'], 
        $medicationData['frequency'], 
        $medicationData['start_date'], 
        $medicationData['end_date']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Medication insertion failed: " . $stmt->error);
    }
    
    return true;
}
public function updateAppointmentStatus($data) {
    $query = "UPDATE appointments 
              SET status = ? 
              WHERE appointment_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "si", 
        $data['status'], 
        $data['appointment_id']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update appointment status: " . $stmt->error);
    }
    
    return true;
}

public function updateAppointment($data) {
    $query = "UPDATE appointments 
              SET appointment_date = ?, 
                  reason_for_visit = ?, 
                  status = ? 
              WHERE appointment_id = ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "sssi", 
        $data['appointment_date'], 
        $data['reason_for_visit'], 
        $data['status'], 
        $data['appointment_id']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update appointment: " . $stmt->error);
    }
    
    return true;
}
public function getemergency_contacts($userId, $userRole = 'doctor') {
    try {
        if ($userRole === 'doctor') {
            $query = "SELECT 
                ec.*,
                u.name as patient_name
            FROM emergencycontacts ec
            JOIN patient_details pd ON ec.patient_id = pd.patient_id
            JOIN users u ON pd.user_id = u.user_id
            JOIN doctor_details dd ON u.user_id = dd.user_id
            WHERE dd.doctor_id = ?";

            $stmt = $this->getConnection()->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        // Existing patient role logic
    } catch (Exception $e) {
        error_log("Emergency contacts error: " . $e->getMessage());
        return [];
    }
}

public function getDoctorPatientEmergencyContacts($doctorId) {
     $query = "SELECT 
     u.name AS patient_name, 
     ec.contact_name, 
     ec.contact_relationship, 
     ec.contact_phone, 
     ec.contact_email
   FROM appointments a 
   JOIN emergencycontacts ec ON a.patient_id = ec.patient_id
   JOIN patient_details pd ON pd.patient_id = ec.patient_id
   JOIN users u ON u.user_id = pd.user_id 
   WHERE a.doctor_id = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// public function addHealthVital($data) {
//     $query = "INSERT INTO health_vitals 
//               (patient_id, recorded_date, recorded_time, 
//                blood_pressure, heart_rate, blood_sugar_level, 
//                temperature, notification_sent) 
//               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
//     $stmt = $this->conn->prepare($query);
//     $stmt->bind_param(
//         "issssssi", 
//         $data['patient_id'], 
//         $data['recorded_date'], 
//         $data['recorded_time'], 
//         $data['blood_pressure'], 
//         $data['heart_rate'], 
//         $data['blood_sugar_level'], 
//         $data['temperature'], 
//         $data['notification_sent']
//     );
    
//     return $stmt->execute();
// }

public function fetchHealthVitals($doctorId) {
    $query = "SELECT hv.*, u.name AS patient_name 
              FROM health_vitals hv
              JOIN users u ON hv.patient_id = u.user_id
              JOIN appointments a ON u.user_id = a.patient_id
              WHERE a.doctor_id = ?
              ORDER BY hv.recorded_date DESC, hv.recorded_time DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}




public function sendHealthVitalNotification($patientId, $vitalData) {
    $message = "Your health vitals have been updated:\n" .
               "Blood Pressure: {$vitalData['blood_pressure']}\n" .
               "Heart Rate: {$vitalData['heart_rate']}\n" .
               "Blood Sugar: {$vitalData['blood_sugar_level']}\n" .
               "Temperature: {$vitalData['temperature']}";
    
    $query = "INSERT INTO notifications 
              (user_id, message, created_at, is_read) 
              VALUES (?, ?, 'health_vitals', NOW(), 0)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("is", $patientId, $message);
    
    return $stmt->execute();
}



public function getPatientHealthVitals($userId) {
    $query = "SELECT hv.* 
              FROM health_vitals hv
              JOIN patient_details pd ON hv.patient_id = pd.patient_id
              WHERE pd.user_id = ?
              ORDER BY hv.recorded_date DESC, hv.recorded_time DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

public function addHealthVital($data) {
    $query = "INSERT INTO health_vitals 
              (patient_id, recorded_date, recorded_time, 
               blood_pressure, heart_rate, blood_sugar_level, 
               temperature, notification_sent) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "issssssi", 
        $data['patient_id'], 
        $data['recorded_date'], 
        $data['recorded_time'], 
        $data['blood_pressure'], 
        $data['heart_rate'], 
        $data['blood_sugar_level'], 
        $data['temperature'], 
        $data['notification_sent']
    );
    
    return $stmt->execute();
}

public function sendNotification($data) {
    $query = "INSERT INTO notifications 
              (user_id, message, is_read, created_at) 
              VALUES (?, ?, 0, NOW())";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param(
        "is", 
        $data['user_id'], 
        $data['message']
    );
    
    return $stmt->execute();
}
public function getEmergencyContacts($userId, $userRole = 'doctor') {
    try {
        // Prepare base query
        $query = "";
        $paramType = "i";
        $param = $userId;

        switch ($userRole) {
            case 'doctor':
                    $query = "SELECT 
            ec.*, 
            u.name AS patient_name, 
            pd.patient_id 
        FROM emergencycontacts ec
        JOIN patient_details pd ON ec.patient_id = pd.patient_id
        JOIN users u ON pd.user_id = u.user_id
        JOIN doctor_details dd ON dd.user_id = ? 
        JOIN users u2 ON dd.user_id = u2.user_id
        WHERE pd.user_id IN (
            SELECT user_id FROM patient_details
        )";
        break;

            case 'patient':
                // Emergency contacts for specific patient
                $query = "SELECT ec.* 
                FROM emergencycontacts ec
                JOIN patient_details pd ON ec.patient_id = pd.patient_id
                WHERE pd.user_id = ?";
                break;

            default:
                throw new Exception("Invalid user role");
        }

        // Prepare and execute statement
        $stmt = $this->getConnection()->prepare($query);
        $stmt->bind_param($paramType, $param);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check query execution
        if (!$result) {
            error_log("Emergency contacts query failed: " . $this->getConnection()->error);
            return [];
        }

        // Fetch results
        $contacts = $result->fetch_all(MYSQLI_ASSOC);

        // Additional validation
        if (empty($contacts)) {
            error_log("No emergency contacts found for $userRole with ID: $userId");
        }

        return $contacts;
    } catch (Exception $e) {
        error_log("Emergency contacts fetch error: " . $e->getMessage());
        return [];
    }
}


}
?>
