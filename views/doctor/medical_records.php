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

// Fetch Doctor's Patients and Medical Records
$doctorId = $_SESSION['user_id'];
$medicalRecords = $dbHandler->fetchmedical_records($doctorId);
$patients = $dbHandler->getPatients();

// Handle Medical Record Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add_medical_record':
                $medicalRecordData = [
                    'patient_id' => $_POST['patient_id'],
                    'doctor_id' => $doctorId,
                    'diagnosis' => $_POST['diagnosis'],
                    'treatment' => $_POST['treatment_plan'],
                    'prescriptions' => $_POST['prescriptions'] ?? null,
                    'date_of_record' => date('Y-m-d')
                ];
                $result = $dbHandler->addMedicalRecord($medicalRecordData);
                break;

            case 'update_medical_record':
                $medicalRecordData = [
                    'record_id' => $_POST['record_id'],
                    'patient_id' => $_POST['patient_id'],
                    'doctor_id' => $doctorId,
                    'diagnosis' => $_POST['diagnosis'],
                    'treatment' => $_POST['treatment_plan'],
                    'prescriptions' => $_POST['prescriptions'] ?? null,
                    'date_of_record' => $_POST['record_date']
                ];
                $result = $dbHandler->updateMedicalRecord($medicalRecordData);
                break;

            case 'delete_medical_record':
                $result = $dbHandler->deleteMedicalRecord($_POST['record_id'], $doctorId);
                break;
        }

        if ($result) {
            $_SESSION['success_message'] = "Medical record operation successful!";
            header("Location: medical_records.php");
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
    <title>Medical Records - Doctor Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
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
                            <a class="nav-link active" href="medical_records.php">
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
                    <h1 class="h2">Patient Medical Records</h1>
                    <div class="btn-group">
                        <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addMedicalRecordModal">
                            <i class="bi bi-plus-circle me-2"></i>Add New Record
                        </button>
                        <button class="btn btn-secondary" onclick="generateReport()">
                            <i class="bi bi-file-earmark-pdf me-2"></i>Generate Report
                        </button>
                    </div>
                </div>

                <!-- Medical Records Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                            <thead>
    <tr>
        <th>Patient Name</th>
        <th>Diagnosis</th>
        <th>Treatment Plan</th>
        <th>Prescriptions</th>
        <th>Record Date</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($medicalRecords as $record): ?>
    <tr>
        <td><?php echo htmlspecialchars($record['patient_name']); ?></td>
        <td><?php echo htmlspecialchars($record['diagnosis']); ?></td>
        <td><?php echo htmlspecialchars($record['treatment']); ?></td>
        <td><?php echo htmlspecialchars($record['prescriptions'] ?? 'N/A'); ?></td>
        <td><?php echo htmlspecialchars($record['date_of_record']); ?></td>
        <td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editMedicalRecordModal"
                                                    onclick="populateEditModal(
                                                        '<?php echo $record['record_id']; ?>', 
                                                        '<?php echo $record['patient_id']; ?>', 
                                                        '<?php echo $record['diagnosis']; ?>', 
                                                        '<?php echo $record['treatment']; ?>', 
                                                        '<?php echo $record['date_of_record']; ?>'
                                                    )">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteRecord(<?php echo $record['record_id']; ?>)">
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

    <!-- Modals for Add and Edit Medical Records (similar to previous implementation) -->
    <!-- Include both modals here -->
   <!-- Add Medical Record Modal -->
   <div class="modal fade" id="addMedicalRecordModal" tabindex="-1" aria-labelledby="addMedicalRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMedicalRecordModalLabel">Add New Medical Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="patient_id" class="form-label">Patient</label>
                            <select name="patient_id" class="form-control" required>
    <option value="">Select Patient</option>
    <?php foreach ($patients as $patient): ?>
        <option value="<?php echo $patient['patient_id']; ?>">
            <?php echo htmlspecialchars($patient['name']); ?>
        </option>
    <?php endforeach; ?>
</select>
                        </div>
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis</label>
                            <input type="text" name="diagnosis" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="treatment_plan" class="form-label">Treatment Plan</label>
                            <textarea name="treatment_plan" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
    <label for="prescriptions" class="form-label">Prescriptions</label>
    <textarea name="prescriptions" class="form-control" id="prescriptions"></textarea>
</div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="action" value="add_medical_record" class="btn btn-primary">Save Record</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Medical Record Modal -->
    <div class="modal fade" id="editMedicalRecordModal" tabindex="-1" aria-labelledby="editMedicalRecordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMedicalRecordModalLabel">Edit Medical Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="record_id" id="editRecordId">
                        <div class="mb-3">
                            <label for="editPatientSelect" class="form-label">Patient</label>
                            <select name="patient_id" id="editPatientSelect" class="form-control" required>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['user_id']; ?>"><?php echo htmlspecialchars($patient['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editDiagnosis" class="form-label">Diagnosis</label>
                            <input type="text" name="diagnosis" class="form-control" id="editDiagnosis" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTreatmentPlan" class="form-label">Treatment Plan</label>
                            <textarea name="treatment_plan" class="form-control" id="editTreatmentPlan" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editRecordDate" class="form-label">Record Date</label>
                            <input type="date" name="record_date" class="form-control" id="editRecordDate" required>
                        </div>
                        <div class="mb-3">
    <label for="editPrescriptions" class="form-label">Prescriptions</label>
    <textarea name="prescriptions" class="form-control" id="editPrescriptions"></textarea>
</div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="action" value="update_medical_record" class="btn btn-primary">Update Record</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript functions for populating edit modal and deleting records remain the same
        function populateEditModal(recordId, userId, diagnosis, treatmentPlan, prescriptions, recordDate) {
    document.getElementById('editRecordId').value = recordId;
    document.getElementById('editPatientSelect').value = userId;
    document.getElementById('editDiagnosis').value = diagnosis;
    document.getElementById('editTreatmentPlan').value = treatmentPlan;
    document.getElementById('editPrescriptions').value = prescriptions || '';
    document.getElementById('editRecordDate').value = recordDate;
}


        function deleteRecord(recordId) {
            if (confirm("Are you sure you want to delete this record?")) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_medical_record';
                form.appendChild(actionInput);

                const recordIdInput = document.createElement('input');
                recordIdInput.type = 'hidden';
                recordIdInput.name = 'record_id';
                recordIdInput.value = recordId;
                form.appendChild(recordIdInput);

                document.body.appendChild(form);
                form.submit();
            }
        }

        function generateReport() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            doc.text("Medical Records Report", 20, 20);
            
            const table = document.querySelector('.table-striped tbody');
            let yOffset = 40;

            table.querySelectorAll('tr').forEach((row, index) => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) {
                    doc.text(`Record ${index + 1}:`, 20, yOffset);
                    doc.text(`Patient: ${cells[0].innerText}`, 30, yOffset + 10);
                    doc.text(`Diagnosis: ${cells[1].innerText}`, 30, yOffset + 20);
                    doc.text(`Treatment: ${cells[2].innerText}`, 30, yOffset + 30);
                    doc.text(`Date: ${cells[3].innerText}`, 30, yOffset + 40);
                    
                    yOffset += 60;
                    
                    // Add new page if content exceeds current page
                    if (yOffset > 280) {
                        doc.addPage();
                        yOffset = 40;
                    }
                }
            });

            doc.save('medical_records_report.pdf');
        }
    </script>
</body>
</html>
