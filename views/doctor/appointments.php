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

// Fetch Doctor's Appointments
$doctorId = $_SESSION['user_id'];
$appointments = $dbHandler->getAppointments($doctorId);

// Fetch patients for dropdown
$patients = $dbHandler->getPatients();

// Handle Appointment Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            // Update Appointment Status
            case 'update_status':
                $result = $dbHandler->updateAppointmentStatus([
                    'appointment_id' => $_POST['appointment_id'],
                    'status' => $_POST['status']
                ]);
                break;

            // Schedule New Appointment
            case 'schedule_appointment':
                $result = $dbHandler->scheduleAppointment([
                    'patient_id' => $_POST['patient_id'],
                    'doctor_id' => $doctorId,
                    'appointment_date' => $_POST['appointment_date'],
                    'appointment_time' => date('H:i:s', strtotime($_POST['appointment_date'])),
                    'reason_for_visit' => $_POST['purpose'],
                    'status' => 'scheduled'
                ]);
                break;

            // Cancel Appointment
            case 'cancel_appointment':
                $result = $dbHandler->updateAppointmentStatus([
                    'appointment_id' => $_POST['appointment_id'],
                    'status' => 'cancelled'
                ]);
                break;

            // Edit Appointment
            case 'edit_appointment':
                $result = $dbHandler->updateAppointment([
                    'appointment_id' => $_POST['appointment_id'],
                    'appointment_date' => $_POST['appointment_date'],
                    'reason_for_visit' => $_POST['purpose'],
                    'status' => $_POST['status'] ?? 'scheduled'
                ]);
                break;
        }

        if ($result) {
            $_SESSION['success_message'] = "Operation completed successfully!";
            header("Location: appointments.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Appointments - Elderly Care Management</title>
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
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2"><?php echo htmlspecialchars($doctorName); ?></h5>
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
                            <a class="nav-link  active" href="appointments.php">
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

            <main class="col-md-10 ms-sm-auto px-md-4">
                <!-- Success/Error Messages -->
                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['success_message']); 
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['error_message']); 
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="page-header d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h2">Appointments</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scheduleAppointmentModal">
                        <i class="bi bi-calendar-plus me-2"></i>Schedule New Appointment
                    </button>
                </div>

                <!-- Appointments Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Purpose</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
    <?php foreach ($appointments as $appointment): ?>
    <tr>
        <td><?php echo htmlspecialchars($appointment['patient_name'] ?? 'Unknown'); ?></td>
        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
        <td><?php echo htmlspecialchars($appointment['appointment_time'] ?? 'N/A'); ?></td>
        <td>
            <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                <select name="status" class="form-select form-select-sm" 
                        onchange="this.form.submit();">
                    <option value="scheduled" 
                        <?php echo $appointment['status'] == 'scheduled' ? 'selected' : ''; ?>>
                        Scheduled
                    </option>
                    <option value="completed" 
                        <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>
                        Completed
                    </option>
                    <option value="cancelled" 
                        <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>
                        Cancelled
                    </option>
                </select>
            </form>
        </td>
        <td><?php echo htmlspecialchars($appointment['reason_for_visit'] ?? 'General'); ?></td>
        <td>
            <div class="btn-group">
                <button class="btn btn-sm btn-info" 
                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($appointment)); ?>)">
                    <i class="bi bi-pencil"></i>
                </button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="cancel_appointment">
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>

                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modals remain the same as in previous implementation -->
    <!-- Schedule and Edit Appointment Modals -->
 <!-- Schedule Appointment Modal -->
 <div class="modal fade" id="scheduleAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule New Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="appointments.php">
                        <input type="hidden" name="action" value="schedule_appointment">
                        <div class="mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-control" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo $patient['user_id']; ?>">
                                    <?php echo htmlspecialchars($patient['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Appointment Date</label>
                            <input type="datetime-local" name="appointment_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose</label>
                            <input type="text" name="purpose" class="form-control" required placeholder="Enter consultation purpose">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="Main Clinic">
                        </div>
                        <button type="submit" class="btn btn-primary">Schedule Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div class="modal fade" id="editAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="appointments.php">
                        <input type="hidden" name="action" value="edit_appointment">
                        <input type="hidden" name="appointment_id" id="edit_appointment_id">
                        <div class="mb-3">
                            <label class="form-label">Appointment Date</label>
                            <input type="datetime-local" name="appointment_date" id="edit_appointment_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose</label>
                            <input type="text" name="purpose" id="edit_purpose" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" id="edit_location" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Appointment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openEditModal(appointment) {
        document.getElementById('edit_appointment_id').value = appointment.appointment_id;
        document.getElementById('edit_appointment_date').value = 
            appointment.appointment_date + 'T' + (appointment.appointment_time || '');
        document.getElementById('edit_purpose').value = appointment.reason_for_visit;
        
        const modal = new bootstrap.Modal(document.getElementById('editAppointmentModal'));
        modal.show();
    }
    </script>
</body>
</html>
