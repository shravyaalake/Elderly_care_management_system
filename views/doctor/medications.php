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

// Define doctorName using session data
$doctorName = $_SESSION['name'] ?? 'Doctor';

// Initialize DatabaseHandler
$dbHandler = new DatabaseHandler();

// Fetch Doctor's Patients and Medications
$doctorId = $_SESSION['user_id'];
$patients = $dbHandler->getPatients();
$medications = $dbHandler->getAllMedications($doctorId);

// Handle Medication Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add_medication':
                $medicationData = [
                    'user_id' => $_POST['patient_id'],
                    'doctor_id' => $doctorId,
                    'medication_name' => $_POST['medication_name'],
                    'dosage' => $_POST['dosage'],
                    'frequency' => $_POST['frequency'],
                    'start_date' => $_POST['start_date'],
                    'end_date' => $_POST['end_date'] ?? null
                ];
                $result = $dbHandler->addMedication($medicationData);
                break;

            case 'update_medication':
                $medicationData = [
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'medication_name' => $medicationName,
                    'dosage' => $dosage,
                    'frequency' => $frequency,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ];
                
                $result = $dbHandler->updateMedication($medicationData);
                break;

            case 'delete_medication':
                $result = $dbHandler->deleteMedication($_POST['medication_id']);
                break;
        }

        if ($result) {
            $_SESSION['success_message'] = "Medication operation successful!";
            header("Location: medications.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medications - Doctor Dashboard</title>
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
                        <h5 class="text-white mt-2"> <?php echo htmlspecialchars($doctorName); ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link " href="dashboard.php">
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
                            <a class="nav-link active" href="medications.php">
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
            <main class="col-md-10 ms-sm-auto px-md-4">
                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo htmlspecialchars($_SESSION['success_message']); 
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?php 
                        echo htmlspecialchars($_SESSION['error_message']); 
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Patient Medications</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMedicationModal">
                        <i class="bi bi-plus-circle me-2"></i>Add New Medication
                    </button>
                </div>

                <!-- Medications Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Medication Name</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($medications as $medication): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($medication['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['medication_name']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['frequency']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['start_date']); ?></td>
                                        <td><?php echo $medication['end_date'] ?? 'Ongoing'; ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button 
                                                    class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editMedicationModal"
                                                    onclick="populateEditModal(
                                                        <?php echo $medication['medication_id']; ?>,
                                                        '<?php echo htmlspecialchars($medication['medication_name']); ?>',
                                                        '<?php echo htmlspecialchars($medication['dosage']); ?>',
                                                        '<?php echo htmlspecialchars($medication['frequency']); ?>',
                                                        '<?php echo $medication['start_date']; ?>',
                                                        '<?php echo $medication['end_date'] ?? ''; ?>'
                                                    )"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button 
                                                    class="btn btn-sm btn-danger" 
                                                    onclick="deleteMedication(<?php echo $medication['medication_id']; ?>)"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add and Edit Medication Modals remain the same as in previous implementation -->
    <!-- Include both modals here -->

    <!-- Add Medication Modal -->
    <div class="modal fade" id="addMedicationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Medication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="medications.php">
                        <input type="hidden" name="action" value="add_medication">
                        <div class="mb-3">
                            <label>Patient Name</label>
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
                            <label>Medication Name</label>
                            <input type="text" name="medication_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Dosage</label>
                            <input type="text" name="dosage" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Frequency</label>
                            <input type="text" name="frequency" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Add Medication</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Medication Modal -->
    <div class="modal fade" id="editMedicationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Medication</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="medications.php">
                        <input type="hidden" name="action" value="update_medication">
                        <input type="hidden" name="medication_id" id="editMedicationId">
                        <div class="mb-3">
                            <label>Medication Name</label>
                            <input type="text" name="medication_name" id="editMedicationName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Dosage</label>
                            <input type="text" name="dosage" id="editDosage" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Frequency</label>
                            < ```php
                            <input type="text" name="frequency" id="editFrequency" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="editStartDate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" id="editEndDate" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Medication</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function populateEditModal(id, name, dosage, frequency, startDate, endDate) {
        document.getElementById('editMedicationId').value = id;
        document.getElementById('editMedicationName').value = name;
        document.getElementById('editDosage').value = dosage;
        document.getElementById('editFrequency').value = frequency;
        document.getElementById('editStartDate').value = startDate;
        document.getElementById('editEndDate').value = endDate;
    }

    function deleteMedication(medicationId) {
        if (confirm('Are you sure you want to delete this medication record?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_medication';
            form.appendChild(actionInput);

            const medicationInput = document.createElement('input');
            medicationInput.type = 'hidden';
            medicationInput.name = 'medication_id';
            medicationInput.value = medicationId;
            form.appendChild(medicationInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
