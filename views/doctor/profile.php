<?php
session_start();
require_once '../../includes/auth.php';
require_once '../../includes/DatabaseHandler.php';
$doctorName = $_SESSION['name'] ?? 'Doctor';

// Authentication Check
$auth = new Auth();
if (!$auth->isLoggedIn() || $_SESSION['role'] !== 'doctor') {
    header("Location: ../../public/index.php");
    exit();
}

// Initialize DatabaseHandler
$dbHandler = new DatabaseHandler();
$userId = $_SESSION['user_id'];
$error = '';
$successMessage = '';

try {
    // Fetch doctor details from doctor_details table
    $query = "SELECT 
                d.specialization, 
                u.name, 
                u.phone_number, 
                u.email,
                d.consultation_fee,
                d.available_days,
                d.consultation_hours_start,
                d.consultation_hours_end
              FROM users u
              JOIN doctor_details d ON u.user_id = d.user_id 
              WHERE u.user_id = ?";

    $stmt = $dbHandler->getConnection()->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['specialization'] = $row['specialization'] ?? 'Not specified';
        $_SESSION['name'] = $row['name'];
        $_SESSION['contact_info'] = $row['phone_number'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['consultation_fee'] = $row['consultation_fee'] ?? 0;
        $_SESSION['available_days'] = $row['available_days'] ?? '';
        $_SESSION['consultation_hours_start'] = $row['consultation_hours_start'] ?? '';
        $_SESSION['consultation_hours_end'] = $row['consultation_hours_end'] ?? '';
    }
} catch (Exception $e) {
    $error = "Error fetching profile details: " . $e->getMessage();
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate current password
        $currentPassword = $_POST['current_password'];
        $verifyPassword = $auth->verifyPassword($_SESSION['email'], $currentPassword);

        if (!$verifyPassword) {
            throw new Exception("Current password is incorrect.");
        }

        // Prepare update data
        $updateData = [
            'user_id' => $userId,
            'name' => $_POST['name'],
            'phone_number' => $_POST['contact_info'],
            'email' => $_POST['email'],
            'specialization' => $_POST['specialization'],
            'consultation_fee' => $_POST['consultation_fee'],
            'available_days' => $_POST['available_days'],
            'consultation_hours_start' => $_POST['consultation_hours_start'],
            'consultation_hours_end' => $_POST['consultation_hours_end']
        ];

        // Update password if new password is provided
        if (!empty($_POST['new_password'])) {
            $updateData['password'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        }

        // Perform profile update
        $result = $dbHandler->updateDoctorProfile($updateData);

        if ($result) {
            $successMessage = "Profile updated successfully!";

            // Update session variables
            // Ensure all session variables are properly set after update
            $_SESSION['name'] = $updateData['name'];
            $_SESSION['specialization'] = $updateData['specialization'];
            $_SESSION['contact_info'] = $updateData['phone_number'];
            $_SESSION['email'] = $updateData['email'];
            $_SESSION['consultation_fee'] = $updateData['consultation_fee'];
            $_SESSION['available_days'] = implode(',', $updateData['available_days']);
            $_SESSION['consultation_hours_start'] = $updateData['consultation_hours_start'];
            $_SESSION['consultation_hours_end'] = $updateData['consultation_hours_end'];

    }} catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Doctor Profile - Elderly Care Management</title>
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
                            <a class="nav-link  active" href="profile.php">
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
                <div class="container mt-5">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($successMessage)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>

                    <h1>Doctor Profile</h1>
                    <div class="card">
                        <div class="card-body">
                            <form id="profileUpdateForm" method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="<?php echo htmlspecialchars($_SESSION['name'] ?? 'Doctor'); ?>"
                                            required pattern="[A-Za-z\s]+">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Specialization</label>
                                        <input type="text" class="form-control" name="specialization"
                                            value="<?php echo htmlspecialchars($_SESSION['specialization'] ?? 'Not specified'); ?>"
                                            required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" name="contact_info"
                                            value="<?php echo htmlspecialchars($_SESSION['contact_info'] ?? ''); ?>" 
                                            required pattern="[0-9]{10}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email"
                                            value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>"
                                            required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Consultation Fee</label>
                                        <input type="number" class="form-control" name="consultation_fee"
                                            value="<?php echo htmlspecialchars($_SESSION['consultation_fee'] ?? '0'); ?>"
                                            required min="0">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Available Days</label>
                                        <select multiple class="form-control" name="available_days[]">
                                            <?php 
                                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                            $selectedDays = explode(',', $_SESSION['available_days'] ?? '');
                                            foreach($days as $day): 
                                            ?>
                                                <option value="<?php echo $day; ?>" 
                                                    <?php echo in_array($day, $selectedDays) ? 'selected' : ''; ?>>
                                                    <?php echo $day; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Consultation Hours Start</label>
                                        <input type="time" class="form-control" name="consultation_hours_start"
                                            value="<?php echo htmlspecialchars($_SESSION['consultation_hours_start'] ?? ''); ?>"
                                            required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Consultation Hours End</label>
                                        <input type="time" class="form-control" name="consultation_hours_end"
                                            value="<?php echo htmlspecialchars($_SESSION['consultation_hours_end'] ?? ''); ?>"
                                            required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Change Password</label>
                                    <input type="password" class="form-control" name="new_password"
                                        placeholder="Leave blank if no change" minlength="8">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm Current Password</label>
                                    <input type="password" class="form-control" name="current_password"
                                        placeholder="Enter current password to save changes" required>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Profile</button>
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
$query = "SELECT * FROM emergencycontacts ec JOIN patient_details pd ON pd.patient_id=ec.patient_id
JOIN users u ON u.user_id=pd.user_id;