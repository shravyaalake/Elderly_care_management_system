<?php
session_start();
require_once '../../includes/auth.php';
$userName = $_SESSION['name'] ?? 'patient'; 

// Authentication Check
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'patient') {
    header("Location: ../../public/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Help & Support - Doctor Dashboard</title>
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
            background-color: var(--primary-color) !important;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar .nav-link {
            color: white;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
            color: var(--accent-color);
        }

        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }

        .dashboard-card .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: bold;
        }

        .dashboard-table thead {
            background-color: var(--secondary-color);
            color: white;
        }

        .health-alerts .list-group-item {
            transition: all 0.3s ease;
        }

        .health-alerts .list-group-item:hover {
            background-color: #f8f9fa;
            transform: translateX(10px);
        }

        main {
            margin-left: 250px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: relative;
                min-height: auto;
            }
            main {
                margin-left: 0;
            }
        }

        .welcome-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
            <nav class="col-md-2 sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2"><?php echo $userName; ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link  " href="dashboard.php">
                                <i class="bi bi-house-door me-2"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="medications.php">
                                <i class="bi bi-prescription me-2"></i>Medications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="appointments.php">
                                <i class="bi bi-calendar-check me-2"></i>Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="emergency_contacts.php">
                                <i class="bi bi-telephone me-2"></i>Emergency Contacts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="bi bi-person me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="help_support.php">
                                <i class="bi bi-question-circle me-2"></i>Help & Support
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav> 

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto">
                <div class="container-fluid">
                    <h1 class="mt-4 mb-3">Help & Support</h1>
                    
                    <div class="card help-card mb-4">
                        <div class="card-body">
                            <h3>Frequently Asked Questions</h3>
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                            How do I add a new patient record?
                                        </button>
                                    </h2>
                                    <div id="faq1" class="accordion-collapse collapse show">
                                        <div class="accordion-body">
                                            Navigate to the Patients section and click "Add New Patient"
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                            How can I update patient medications?
                                        </button>
                                    </h2>
                                    <div id="faq2" class="accordion-collapse collapse">
                                        <div class="accordion-body">
                                            Go to the patient's details and use the Medications section to update
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card help-card">
                        <div class="card-body support-form">
                            <h3>Contact Support</h3>
                            <form>
                                <div class="mb-3">
                                    <label class="form-label">Subject</label>
                                    <input type="text" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea class="form-control" rows="4"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Send Support Request</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
