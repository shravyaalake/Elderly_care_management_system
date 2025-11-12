<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/DatabaseHandler.php';

$userName = $_SESSION['name'] ?? 'User';
// Authentication Check
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'patient') {
    header("Location: ../../public/index.php");
    exit();
}

// Initialize DatabaseHandler
$dbHandler = new DatabaseHandler();

try {
    // Get patient details
    $userId = $_SESSION['user_id'];
    
    // Get patient ID from patient_details
    $patientDetailsQuery = "SELECT patient_id FROM patient_details WHERE user_id = ?";
    $stmt = $dbHandler->getConnection()->prepare($patientDetailsQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $patientResult = $stmt->get_result();
    
    if ($patientResult->num_rows === 0) {
        throw new Exception("Patient details not found");
    }
    
    $patientDetails = $patientResult->fetch_assoc();
    $patientId = $patientDetails['patient_id'];

  // In patient's emergency contacts page
$userId = $_SESSION['user_id'];
$emergencyContacts = $dbHandler->getEmergencyContacts($userId, 'patient');


    // Handle Add Contact Request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $contactData = [
            'patient_id' => $patientId,
            'contact_name' => $_POST['contact_name'] ?? '',
            'contact_relationship' => $_POST['contact_relationship'] ?? '',
            'contact_phone' => $_POST['contact_phone'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? ''
        ];

        // Add or update emergency contact
        $result = $dbHandler->updateEmergencyContact($contactData);
        
        // Redirect to prevent form resubmission
        header("Location: emergency_contacts.php");
        exit();
    }
} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
    $emergencyContacts = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Emergency Contacts - Elderly Care Management</title>
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
                            <a class="nav-link" href="appointments.php">
                                <i class="bi bi-calendar-check me-2"></i>Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="emergency_contacts.php">
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
                <h1 class="h2">Emergency Contacts</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmergencyContactModal">
                    Add New Contact
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
                                <th>Name</th>
                                <th>Relationship</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($emergencyContacts as $contact): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contact['contact_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($contact['contact_relationship'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($contact['contact_phone'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($contact['contact_email'] ?? 'N/A'); ?></td>
                                <td>
                                    <button 
                                        class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editEmergencyContactModal"
                                        onclick="populateEditModal(<?php echo htmlspecialchars(json_encode($contact)); ?>)">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Emergency Contact Modal -->
    <div class="modal fade" id="addEmergencyContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Emergency Contact</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="contact_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Relationship</label>
                            <input type="text" name="contact_relationship" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="tel" name="contact_phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="contact_email" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Add Contact</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Emergency Contact Modal -->
    <div class="modal fade" id="editEmergencyContactModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Emergency Contact</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="contact_id" id="edit-contact-id">
                        <div class="mb-3">
                            <label>Name</label>
                            <input type="text" name="contact_name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Relationship</label>
                            <input type="text" name="contact_relationship" id="edit-relationship" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone</label>
                            <input type="tel" name="contact_phone" id="edit-phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="contact_email" id="edit-email" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function populateEditModal(contact) {
        document.getElementById('edit-contact-id').value = contact.contact_id;
        document.getElementById('edit-name').value = contact.contact_name;
        document.getElementById('edit-relationship').value = contact.contact_relationship;
        document.getElementById('edit-phone').value = contact.contact_phone;
        document.getElementById('edit-email').value = contact.contact_email || '';
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
