<?php
session_start();

// Include the database connection class
include '../../includes/db_connection.php';

// Initialize the database connection
$db = new db_connection();
$conn = $db->getConnection();
// Initialize the variable before any conditional checks
$notification = null;
$notificationType = 'success'; // Optional: also initialize notification type
// Check if user is logged in and is a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    header('Location: ../../index.php');
    exit();
}

// Fetch patients for dropdown
$patientsQuery = "SELECT pd.patient_id, u.name 
                  FROM patient_details pd
                  JOIN users u ON pd.user_id = u.user_id";
$patients = $db->select($patientsQuery);

// Handle health vital update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_health_vital'])) {
    try {
        // Validate input
        $patient_id = $_POST['patient_id'];
        $blood_pressure = $_POST['blood_pressure'];
        $heart_rate = $_POST['heart_rate'];
        $blood_sugar_level = $_POST['blood_sugar_level'];
        $temperature = $_POST['temperature'];

        // Get user_id associated with the patient
        $userQuery = "SELECT user_id FROM patient_details WHERE patient_id = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $user_id = $userData['user_id'];

        // Insert health vitals
        $insertQuery = "INSERT INTO health_vitals 
                        (patient_id, recorded_date, recorded_time, 
                        blood_pressure, heart_rate, blood_sugar_level, 
                        temperature, notification_sent) 
                        VALUES (?, CURDATE(), CURTIME(), ?, ?, ?, ?, 1)";
        
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("isssd", 
            $patient_id, 
            $blood_pressure, 
            $heart_rate, 
            $blood_sugar_level, 
            $temperature
        );
        $stmt->execute();

        // Create notification
        $notificationMessage = "Your Health is doing good!!  - by   " . $_SESSION['name'];
       // Modify notification insertion
$notificationQuery = "INSERT INTO notifications (user_id, message, is_read, created_at) 
VALUES (?, ?, 0, NOW())";
$notifStmt = $conn->prepare($notificationQuery);
$notifStmt->bind_param("is", $user_id, $notificationMessage);
$notifStmt->execute();

        // Set success message
        $notification = "Health vitals updated successfully and notification sent!";
        $notificationType = 'success';

    } catch (Exception $e) {
        $notification = "Error: " . $e->getMessage();
        $notificationType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Health Vitals Update</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #25283B;
            --secondary-color: #4A5568;
            --accent-color: #20c997;
            --light-bg: #f4f4f4;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
        }

        .health-vitals-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .form-control, .form-select {
            padding: 0.75rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(32,201,151,0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .health-vitals-container {
                padding: 1rem;
                margin: 0 15px;
            }

            .row > div {
                margin-bottom: 1rem;
            }
        }

        .alert {
            border-radius: 10px;
        }
        :root {
            --primary-color: #25283B;
            --secondary-color: #4A5568;
            --accent-color: #20c997;
            --light-bg: #f4f4f4;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Arial', sans-serif;
        }

        .sidebar {
            background-color: var(--primary-color);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar .nav-link {
            color: white;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
            color: var(--accent-color);
        }

        main {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .dashboard-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }

        .table thead {
            background-color: var(--secondary-color);
            color: white;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,0.05);
        }

        .btn-sm {
            transition: all 0.3s ease;
        }

        .btn-sm:hover {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }

            main {
                margin-left: 0;
            }
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 15px;
            background-color: rgba(0,0,0,0.2);
        }

        .logout-btn {
            color: white;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
        <nav class="sidebar">
                <div class="position-sticky">
                    <div class="text-center my-4">
                        <i class="bi bi-person-circle text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2"><?php echo $_SESSION['name'] ?? 'Doctor'; ?></h5>
                    </div>
                    <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-door"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">
                            <i class="bi bi-people"></i> Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="health_vitals.php">
                            <i class="bi bi-heart-pulse"></i> Health Vitals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical_records.php">
                            <i class="bi bi-file-medical"></i> Medical Records
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medications.php">
                            <i class="bi bi-prescription"></i> Medications
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="appointments.php">
                            <i class="bi bi-calendar-check"></i> Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link " href="emergency_contacts.php">
                            <i class="bi bi-telephone"></i> Emergency Contacts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person"></i> Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="help_support.php">
                            <i class="bi bi-question-circle"></i> Help & Support
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                   
                        </ul>
                </div>
            </nav>
            <div class="container-fluid py-4">
        <div class="health-vitals-container">
            <div class="text-center mb-4">
                <h2 class="h3">Update Patient Health Vitals</h2>
                <p class="text-muted">Carefully enter patient's health information</p>
            </div>

            <?php if (isset($notification) && $notification): ?>
                <div class="alert alert-<?php echo $notificationType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($notification); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="patient_id" class="form-label">Select Patient</label>
                        <select name="patient_id" id="patient_id" class="form-select" required>
                            <option value="">Choose Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['patient_id']; ?>">
                                    <?php echo htmlspecialchars($patient['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="blood_pressure" class="form-label">Blood Pressure</label>
                        <input type="text" name="blood_pressure" id="blood_pressure" 
                               class="form-control" placeholder="120/80 mmHg" required>
                    </div>

                    <div class="col-md-6">
                        <label for="heart_rate" class="form-label">Heart Rate</label>
                        <input type="number" name="heart_rate" id="heart_rate" 
                               class="form-control" placeholder="Beats per minute" required>
                    </div>

                    <div class="col-md-6">
                        <label for="blood_sugar_level" class="form-label">Blood Sugar</label>
                        <input type="number" name="blood_sugar_level" id="blood_sugar_level" 
                               class="form-control" placeholder="mg/dL" required>
                    </div>

                    <div class="col-md-6">
                        <label for="temperature" class="form-label">Temperature</label>
                        <input type="number" step="0.1" name="temperature" id="temperature" 
                               class="form-control" placeholder="°F" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" name="update_health_vital" 
                                class="btn btn-primary w-100 py-2">
                            Update Health Vitals
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>