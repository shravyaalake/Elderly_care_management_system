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
$doctorName = $_SESSION['name'] ?? 'Doctor';

try {
    // Fetch doctor's patients' emergency contacts
    $doctorId = $_SESSION['user_id'];
    
 // In doctor's patient emergency contacts page
$doctorId = $_SESSION['user_id'];
$emergencyContacts = $dbHandler->getEmergencyContacts($doctorId, 'doctor');

} catch (Exception $e) {
    $emergencyContacts = [];
    $error = "Unable to fetch emergency contacts: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Emergency Contacts - Doctor Dashboard</title>
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
                        <a class="nav-link " href="appointments.php">
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
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                   
                        </ul>
                </div>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Patient Emergency Contacts</h1>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Contact Name</th>
                                        <th>Relationship</th>
                                        <th>Phone Number</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($emergencyContacts)): ?>
                                        <?php foreach ($emergencyContacts as $contact): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($contact['patient_name'] ?? 'Unknown'); ?></td>
                                                <td><?php echo htmlspecialchars($contact['contact_name']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['contact_relationship']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['contact_phone']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['contact_email'] ?? 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No emergency contacts found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
