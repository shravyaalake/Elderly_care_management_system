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
    // Fetch User's Profile Information from Database
    $userId = $_SESSION['user_id'];
    $userProfile = $dbHandler->getUserProfile($userId);

} catch (Exception $e) {
    // Fallback to session data if database fetch fails
    $userProfile = [
        'name' => $_SESSION['name'] ?? 'Patient',
        'age' => $_SESSION['age'] ?? 'N/A',
        'phone_number' => $_SESSION['phone_number'] ?? 'N/A',
        'email' => $_SESSION['email'] ?? '',
    ];
    $error = "Unable to fetch profile: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile - Elderly Care Management</title>
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
                            <a class="nav-link" href="emergency_contacts.php">
                                <i class="bi bi-telephone me-2"></i>Emergency Contacts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="profile.php">
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
                <div class="container mt-5">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <h1>User Profile</h1>
                    <div class="card">
                        <div class="card-body">
                            <form id="profileUpdateForm" method="POST" action="update_profile.php">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input 
                                            type="text" 
                                            class="form-control" 
                                            name="name" 
                                            value="<?php echo htmlspecialchars($userProfile['name']); ?>"
                                            required
                                            pattern="[A-Za-z\s]+"
                                        >
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Age</label>
                                        <input 
                                            type="number" 
                                            class="form-control" 
                                            name="age" 
                                            value="<?php echo htmlspecialchars($userProfile['age']); ?>"
                                            required
                                            min="18"
                                            max="120"
                                        >
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input 
                                            type="tel" 
                                            class="form-control" 
                                            name="phone_number" 
                                            value="<?php echo htmlspecialchars($userProfile['phone_number']); ?>"
                                            required
                                            pattern="[0-9]{10}"
                                        >
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input 
                                            type="email" 
                                            class="form-control" 
                                            name="email" 
                                            value="<?php echo htmlspecialchars($userProfile['email']); ?>"
                                            required
                                            pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                        >
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Change Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        name="new_password" 
                                        placeholder="Leave blank if no change"
                                        minlength="8"
                                    >
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm Current Password</label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        name="current_password" 
                                        placeholder="Enter current password to save changes"
                                        required
                                    >
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
    <script>
    document.getElementById('profileUpdateForm').addEventListener('submit', function(e) {
        const name = document.querySelector('input[name="name"]');
        const age = document.querySelector('input[name="age"]');
        const phoneNumber = document.querySelector('input[name="phone_number"]');
        const email = document.querySelector('input[name="email"]');
        const currentPassword = document.querySelector('input[name="current_password"]');

        // Validate name
        if (!/^[A-Za-z\s]+$/.test(name.value)) {
            alert('Name should contain only letters');
            e.preventDefault();
            return;
        }

        // Validate age
        if (age.value < 18 || age.value > 120) {
            alert('Age must be between 18 and 120');
            e.preventDefault();
            return;
        }

        // Validate phone number
        if (!/^[0-9]{10}$/.test(phoneNumber.value)) {
            alert('Contact number must be 10 digits');
            e.preventDefault();
            return;
        }

        // Validate email
        if (!/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/.test(email.value)) {
            alert('Invalid email format');
            e.preventDefault();
            return;
        }

        // Ensure current password is entered
        if (!currentPassword.value) {
            alert('Current password is required');
            e.preventDefault();
            return;
        }
    });
    </script>
</body>
</html>
