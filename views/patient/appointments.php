<?php
session_start(); 
require_once '../../includes/auth.php'; 
require_once '../../includes/DatabaseHandler.php'; 

// Authentication Check
$auth = new Auth(); 
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'patient') { 
    header("Location: ../../public/index.php"); 
    exit(); 
}

// Initialize DatabaseHandler 
$dbHandler = new DatabaseHandler(); 

try { 
    // Get logged-in user's ID 
    $userId = $_SESSION['user_id']; 
    $userName = $_SESSION['name'] ?? 'Patient'; 

    // Fetch Available Doctors with Specialization
    $availableDoctors = $dbHandler->getAvailableDoctors();

    // Fetch User's Appointments 
    $upcomingAppointments = $dbHandler->getUserAppointments($userId); 
} catch (Exception $e) { 
    $availableDoctors = []; 
    $upcomingAppointments = []; 
    $error = "Unable to fetch data: " . $e->getMessage(); 
} 
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Appointments - Elderly Care Management</title>
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
         <!-- Sidebar Navigation -->
         <nav class="col-md-2 sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2"><?php echo $userName; ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link " href="dashboard.php">
                                <i class="bi bi-house-door me-2"></i>Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="medications.php">
                                <i class="bi bi-prescription me-2"></i>Medications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="appointments.php">
                                <i class="bi bi-calendar-check me-2"></i>Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link " href="emergency_contacts.php">
                                <i class="bi bi-telephone me-2"></i>Emergency Contacts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="bi bi-person me-2"></i>Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="help_support.php">
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
        <main class="col-md-10 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">My Appointments</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookAppointmentModal">
                    Book New Appointment
                </button>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcomingAppointments)): ?>
                                <?php foreach($upcomingAppointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['appointment_time']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['reason_for_visit']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                                        <td>
                                            <?php if ($appointment['status'] === 'scheduled'): ?>
                                                <button class="btn btn-sm btn-danger" 
                                                    onclick="cancelAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                                    Cancel
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No appointments found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Book Appointment Modal -->
            <div class="modal fade" id="bookAppointmentModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Book New Appointment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="bookAppointmentForm" method="POST" action="book_appointments.php">
                                <div class="mb-3">
                                    <label class="form-label">Select Doctor</label>
                                    <select name="doctor_id" class="form-control" required>
                                        <option value="">Choose a Doctor</option>
                                        <?php foreach($availableDoctors as $doctor): ?>
                                            <option value="<?php echo $doctor['doctor_id']; ?>">
                                                <?php echo htmlspecialchars($doctor['name'] . " - " . $doctor['specialization']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Appointment Date</label>
                                    <input type="date" name="appointment_date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Appointment Time</label>
                                    <input type="time" name="appointment_time" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason for Visit</label>
                                    <textarea name="reason_for_visit" class="form-control" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Book Appointment</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function cancelAppointment(appointmentId) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        fetch('cancel_appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'appointment_id=' + appointmentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Appointment cancelled successfully');
                location.reload();
            } else {
                alert('Failed to cancel appointment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the appointment');
        });
    }
}
</script>
</body>
</html>
