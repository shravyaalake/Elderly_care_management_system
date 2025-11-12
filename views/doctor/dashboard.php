<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/DatabaseHandler.php';

// Authentication Check
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../public/login.php");
    exit();
}

// Initialize DatabaseHandler
$dbHandler = new DatabaseHandler();

try {
    // Fetch Doctor's Specific Details
    $doctorId = $_SESSION['user_id'];
    $doctorName = $_SESSION['name'];

    // Fetch Upcoming Appointments
    $appointments = $dbHandler->getAppointments($doctorId, 'doctor');

    // Fetch Health Vitals
    $healthVitals = $dbHandler->fetchHealthVitals($doctorId);

    // Fetch Medical Records
    $medicalRecords = $dbHandler->fetchmedical_records($doctorId);

    // Fetch Medications
    $medications = $dbHandler->getAllMedications($doctorId);

} catch (Exception $e) {
    $error = "Dashboard data retrieval failed: " . $e->getMessage();
    $appointments = [];
    $healthVitals = [];
    $medicalRecords = [];
    $medications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Dashboard - Elderly Healthcare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root {
            --primary-color: #25283B;
            --secondary-color: #4A5568;
            --accent-color: #20c997;
            --light-bg: #f4f4f4;
            --text-color: #333;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Arial', sans-serif;
            color: var(--text-color);
        }

        .sidebar {
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
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
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--accent-color);
        }

        .sidebar .nav-link.text-danger:hover {
            background-color: rgba(255,0,0,0.1);
        }

        main {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .dashboard-card .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
 /* Welcome Header Styling */
 .welcome-header {
        background: linear-gradient(to right, #25283B, #4A5568);
        color: white;
        padding: 1.5rem 2rem;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .welcome-header h1 {
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0;
        color: #20c997;
        display: flex;
        align-items: center;
    }

    .welcome-header h1 i {
        margin-right: 0.75rem;
        font-size: 1.5rem;
    }

    .btn-toolbar .btn-outline-primary {
        border-color: #20c997;
        color: #20c997;
        transition: all 0.3s ease;
    }

    .btn-toolbar .btn-outline-primary:hover {
        background-color: #20c997;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(32,201,151,0.2);
    }

    @media (max-width: 768px) {
        .welcome-header {
            flex-direction: column;
            text-align: center;
        }

        .welcome-header h1 {
            margin-bottom: 1rem;
            justify-content: center;
        }

        .btn-toolbar {
            width: 100%;
            display: flex;
            justify-content: center;
        }
    }
        .dashboard-table thead {
            background-color: var(--secondary-color);
            color: white;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(32, 201, 151, 0.1);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            main {
                margin-left: 0;
            }
        }
         /* About Us Section Styling */
    #about-us {
        background: linear-gradient(135deg, #25283B 0%, #4A5568 100%);
        color: white;
        padding: 4rem 2rem;
        margin: 2rem 0;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        text-align: center;
    }

    #about-us h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 1.5rem;
        color: #20c997;
    }

    #about-us p {
        font-size: 1.1rem;
        line-height: 1.8;
        max-width: 800px;
        margin: 0 auto;
        opacity: 0.9;
    }

    .mission-highlights {
        display: flex;
        justify-content: space-around;
        margin-top: 2rem;
    }

    .mission-item {
        text-align: center;
        padding: 1rem;
        max-width: 250px;
    }

    .mission-item i {
        font-size: 3rem;
        color: #20c997;
        margin-bottom: 1rem;
    }

    /* Footer Styling */
    footer {
        background: linear-gradient(135deg, #25283B 0%, #4A5568 100%);
        color: white;
        padding: 2rem 0;
        text-align: center;
        margin-top: 2rem;
        box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    footer p {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    footer::before {
        content: '';
        display: block;
        width: 100px;
        height: 3px;
        background-color: #20c997;
        margin: 0 auto 1rem;
        transition: width 0.3s ease;
    }

    footer:hover {
        background: linear-gradient(135deg, #4A5568 0%, #25283B 100%);
    }

    footer:hover::before {
        width: 200px;
    }

    @media (max-width: 768px) {
        #about-us, footer {
            padding: 2rem 1rem;
        }

        #about-us h2 {
            font-size: 2rem;
        }

        #about-us p {
            font-size: 1rem;
        }

        footer p {
            font-size: 0.8rem;
        }
    }
</style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
             <!-- Sidebar Navigation -->
             <nav class="sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2"><?php echo htmlspecialchars($doctorName); ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
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
                            <a class="nav-link" href="appointments.php">
                                <i class="bi bi-calendar-check"></i> Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="emergency_contacts.php">
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
            <main class="col-md-10 ms-sm-auto">
                <div class="welcome-header">
                    <h1>
                        <i class="bi bi-person-check"></i>
                        Welcome,<?php echo htmlspecialchars($doctorName); ?>
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
            <a href="patients.php" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-people me-2"></i>View All Patients
            </a>
        </div>
                </div>
                  
              <!-- About Us Section -->
              <section id="about-us">
    <h2>Doctor's Professional Platform</h2>
    <p>Our Doctor Dashboard is designed to streamline healthcare management, providing comprehensive tools for medical professionals to efficiently track patient care, manage appointments, and access critical health information.</p>
    
    <div class="mission-highlights">
        <div class="mission-item">
            <i class="bi bi-clipboard-pulse"></i>
            <h4>Patient Management</h4>
            <p>Comprehensive patient tracking and health monitoring system.</p>
        </div>
        <div class="mission-item">
            <i class="bi bi-shield-lock"></i>
            <h4>Secure Information</h4>
            <p>Advanced security protocols protecting patient confidentiality.</p>
        </div>
        <div class="mission-item">
            <i class="bi bi-journal-medical"></i>
            <h4>Efficient Workflow</h4>
            <p>Streamlined medical record management and appointment scheduling.</p>
        </div>
    </div>
</section>
  <!-- Upcoming Appointments -->
  <div class="card mb-4">
                    <div class="card-header">
                        <h3>Upcoming Appointments</h3>
                    </div>
                    <div class="card-body">
                        
                            
                    </div>
                </div>

                <!-- Recent Health Vitals -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Recent Health Vitals</h3>
                    </div>
                    <div class="card-body">
                        
                    </div>
                </div>

                <!-- Recent Medical Records -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Recent Medical Records</h3>
                    </div>
                    <div class="card-body">
                        
                    </div>
                </div>
<!-- Footer -->
<footer>
    <p>&copy; <?php echo date('Y'); ?> Elderly Care Management System - Doctor's Portal. All rights reserved.</p>
</footer>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
