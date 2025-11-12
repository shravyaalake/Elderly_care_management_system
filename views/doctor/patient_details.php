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

// Initialize DatabaseHandler
$dbHandler = new DatabaseHandler();

try {
    // Fetch Patient Details
    $patientId = $_GET['id'] ?? null;
    
    if (!$patientId) {
        throw new Exception("No patient ID provided");
    }

    // Fetch Patient Details
    $patientDetails = $dbHandler->getPatientDetails($patientId);

    // Fetch Medical Records
    $medicalRecords = $dbHandler->getPatientMedicalRecords($patientId);

    // Fetch Medications
    $medications = $dbHandler->getPatientMedications($patientId);

} catch (Exception $e) {
   // Add default values and null checks
$patientDetails = $patientDetails ?? [
    'name' => 'Unknown',
    'age' => 'N/A',
    'contact_info' => 'N/A',
    'address' => 'N/A',
    'medical_history' => [
        'chronic_conditions' => [],
        'allergies' => []
    ]
];
    $medicalRecords = [];
    $medications = [];
    $error = "Unable to fetch patient data: " . $e->getMessage();
}

// Safely access medical history
$chronicConditions = $patientDetails['medical_history']['chronic_conditions'] ?? [];
$allergies = $patientDetails['medical_history']['allergies'] ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Details - Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
           <!-- Sidebar Navigation -->
           <nav class="sidebar">
                <div class="position-sticky">
                    <div class="text-center my-4">
                        <i class="bi bi-person-circle text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2">Dr. <?php echo $_SESSION['name'] ?? 'Doctor'; ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-house"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="patients.php">
                                <i class="bi bi-people"></i> Patients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="health_vitals.php">
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
                            <a class="nav-link" href="appointments.php">
                                <i class="bi bi-calendar-check"></i> Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="emergency_contacts.php">
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
                    </ul>
                    <div class="sidebar-footer">
                        <a href="../logout.php" class="btn btn-danger logout-btn">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="container">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <h1 class="my-4">Patient Profile: <?php echo htmlspecialchars($patientDetails['name']); ?></h1>
                    
                    <!-- Patient Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">Personal Information</div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($patientDetails['name'] ?? 'Unknown'); ?></p>
                                    <p><strong>Age:</strong> <?php echo htmlspecialchars($patientDetails['age'] ?? 'N/A'); ?></p>
                                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($patientDetails['contact_info'] ?? 'N/A'); ?></p>
                                    <p><strong>Address:</strong> <?php echo htmlspecialchars($patientDetails['address'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Medical History -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">Medical History</div>
                                <div class="card-body">
                                    <h5>Chronic Conditions</h5>
                                    <?php if (!empty($chronicConditions)): ?>
                                        <ul>
                                            <?php foreach($chronicConditions as $condition): ?>
                                                <li><?php echo htmlspecialchars($condition); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No chronic conditions recorded.</p>
                                    <?php endif; ?>
                                    
                                    <h5>Allergies</h5>
                                    <?php if (!empty($allergies)): ?>
                                        <ul>
                                            <?php foreach($allergies as $allergy): ?>
                                                <li><?php echo htmlspecialchars($allergy); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No allergies recorded.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Records -->
                    <div class="card mb-4">
                        <div class="card-header">Medical Records</div>
                        <div class="card-body">
                            <?php if (!empty($medicalRecords)): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Diagnosis</th>
                                            <th>Treatment Plan</th>
                                            <th>Record Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($medicalRecords as $record): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($record['diagnosis']); ?></td>
                                            <td><?php echo htmlspecialchars($record['treatment_plan']); ?></td>
                                            <td><?php echo htmlspecialchars($record['record_date']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No medical records found.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Medications -->
                    <div class="card mb-4">
                        <div class="card-header">Current Medications</div>
                        <div class="card-body">
                            <?php if (!empty($medications)): ?>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Medication Name</th>
                                            <th>Dosage</th>
                                            <th>Frequency</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($medications as $medication): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($medication['medication_name']); ?></td>
                                            <td><?php echo htmlspecialchars($medication['dosage']); ?></td>
                                            <td><?php echo htmlspecialchars($medication['frequency']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No current medications recorded.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
