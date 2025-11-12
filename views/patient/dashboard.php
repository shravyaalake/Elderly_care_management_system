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
    // Fetch Patient's Information
    $userId = $_SESSION['user_id']; 
    $userName = $_SESSION['name']; 

    // Fetch Upcoming Appointments
    $upcomingAppointments = $dbHandler->getUserAppointments($userId); 

 

    // Fetch Medications
    $medications = $dbHandler->getPatientMedications($userId);

    // Fetch notifications with improved query
$notificationsQuery = "SELECT * FROM notifications 
                       WHERE user_id = ? 
                       ORDER BY created_at DESC 
                       LIMIT 10";
$stmt = $conn->prepare($notificationsQuery);

$stmt->bindParam(1, $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$notificationsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);


   
    
} catch (Exception $e) { 
    $upcomingAppointments = []; 

    $medications = [];
    $notificationsResult = null;
    $error = "Unable to fetch dashboard data: " . htmlspecialchars($e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard - Elderly Care Management</title>
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
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .welcome-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: rgba(255,255,255,0.1);
        transform: rotate(-45deg);
    }

    .welcome-header h1 {
        font-size: 2.2rem;
        font-weight: 700;
        color: #20c997;
        margin-bottom: 0.5rem;
        z-index: 1;
    }

    .welcome-header p {
        font-size: 1rem;
        opacity: 0.8;
        max-width: 600px;
        z-index: 1;
    }

    @media (max-width: 768px) {
        .welcome-header {
            padding: 1.5rem;
        }

        .welcome-header h1 {
            font-size: 1.8rem;
        }

        .welcome-header p {
            font-size: 0.9rem;
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
        footer {
            padding: 1rem 0;
        }

        footer p {
            font-size: 0.8rem;
        }
    }
    </style></head>
<body>
<div class="container-fluid">
<div class="row">
    <!-- Sidebar Navigation (remains the same) -->
    <nav class="col-md-2 sidebar">
    <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-white" style="font-size: 3rem;"></i>
                        <h5 class="text-white mt-2"><?php echo $userName; ?></h5>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
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
            </nav>    </nav>
           
    <main class="col-md-10 ms-sm-auto px-md-4">
        <div class="welcome-header">
            <h1 class="h2">Welcome, <?php echo htmlspecialchars($userName); ?></h1>
            <p>Here's an overview of your health management dashboard</p>
        </div>
        <section id="about-us">
    <h2>Empowering Elderly Care</h2>
    <p>Welcome to Elderly Care Management System! Our platform is dedicated to helping users manage elderly care appointments, schedules, and healthcare information efficiently. Our mission is to provide accessible tools for families to ensure the best care for their loved ones.</p>
    
    <div class="mission-highlights">
        <div class="mission-item">
            <i class="bi bi-clipboard-pulse"></i>
            <h4>Personalized Healthcare</h4>
            <p>Tailored medical tracking and appointment management for individual needs.</p>
        </div>
        <div class="mission-item">
            <i class="bi bi-shield-lock"></i>
            <h4>Secure Information</h4>
            <p>Advanced security protocols protecting patient confidentiality.</p>
        </div>
        <div class="mission-item">
            <i class="bi bi-journal-medical"></i>
            <h4>Easy Coordination</h4>
            <p>Simplified scheduling and communication between patients and healthcare providers.</p>
                </div>
    </div>
</section>

        <!-- Notifications Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card dashboard-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="bi bi-bell me-2"></i>Notifications
                </div>
                <span class="badge bg-primary">
                    <?php 
                    // Count unread notifications
                    $unreadQuery = "SELECT COUNT(*) as unread_count FROM notifications 
                                    WHERE user_id = ? AND is_read = 0";
                    $unreadStmt = $conn->prepare($unreadQuery);
                   
                    $unreadStmt->bindParam(1, $_SESSION['user_id'], PDO::PARAM_INT);
                    
                    $unreadResult = $unreadStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $unreadStmt->execute();
                    $unreadResult = $unreadStmt->fetch(PDO::FETCH_ASSOC);
                                        echo $unreadResult['unread_count'];
                    ?>
                </span>
            </div>
            <div class="card-body">
    <?php 
    // Use count() for PDO result arrays
    if ($notificationsResult && count($notificationsResult) > 0): ?>
        <ul class="list-group">
            <?php foreach ($notificationsResult as $notification): ?>
                <li class="list-group-item <?php echo $notification['is_read'] == 0 ? 'bg-light fw-bold' : ''; ?>">
                    <?php echo htmlspecialchars($notification['message']); ?>
                    <small class="text-muted d-block">
                        <?php echo htmlspecialchars($notification['created_at']); ?>
                    </small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="text-muted">No new notifications.</p>
    <?php endif; ?>
</div>

            <div class="card-footer">
                <button class="btn btn-sm btn-outline-secondary" id="markAllRead">
                    Mark All as Read
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('markAllRead')?.addEventListener('click', function() {
    fetch('mark_notifications_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});
</script>
        <div class="row">
            <!-- Upcoming Appointments -->
            <div class="col-md-6">
                <div class="card dashboard-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-calendar me-2"></i>Upcoming Appointments</span>
                        <a href="appointments.php" class="btn btn-sm btn-light">View All</a>
                    </div>
                    <div class="card-body">
                        <table class="table dashboard-table">
                         
                           
                        
                        </table>
                    </div>
                </div>
            </div>

            
        </div>

        <!-- Medications Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card dashboard-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-prescription2 me-2"></i>Current Medications</span>
                        <a href="medications.php" class="btn btn-sm btn-light">Manage Medications</a>
                    </div>
                    <div class="card-body">
                        <table class="table dashboard-table">
                            
                            <tbody>
                                <?php foreach ($medications as $medication): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($medication['medication_name']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['frequency']); ?></td>
                                        <td><?php echo htmlspecialchars($medication['start_date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Elderly Care Management System. All rights reserved.</p>
        </footer>
    </main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
