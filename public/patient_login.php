<?php
session_start();
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/DatabaseHandler.php';

// Check if user is already logged in
// if (isset($_SESSION['role']) && $_SESSION['role'] === 'patient') {
//     header("Location: ../views/patient/dashboard.php");
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $auth = new Auth();
    
    try {
        // Attempt login specifically for patients
        $loginResult = $auth->login($email, $password);
        
        if ($loginResult) {
            // Verify if the user is a patient
            $dbHandler = new DatabaseHandler();
            $userDetails = $dbHandler->getUserDetailsByEmail($email);
            
            if ($userDetails && $userDetails['role'] === 'patient') {
                // Store patient-specific session data
                $_SESSION['user_id'] = $userDetails['user_id'];
                $_SESSION['name'] = $userDetails['name'];
                $_SESSION['role'] = 'patient';
                $_SESSION['email'] = $email;
                
                // Redirect to patient dashboard
                header("Location: ../views/patient/dashboard.php");
                exit();
            } else {
                $error = "Access denied. Not a patient account.";
            }
        } else {
            $error = "Invalid login credentials";
        }
    } catch (Exception $e) {
        $error = "Login error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Login - Elderly Care Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
         body {
        background: linear-gradient(
            135deg, 
            #25283B 0%, 
            #4A5568 50%, 
            #20c997 100%
        );
        background-size: 400% 400%;
        animation: gradientBG 15s ease infinite;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes gradientBG {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }


    .card {
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .card-header {
        background: linear-gradient(to right, #25283B, #4A5568);
        color: white;
        text-align: center;
        padding: 1.5rem;
    }

    .card-header h3 {
        margin: 0;
        color: #20c997;
        font-weight: 700;
    }

    .card-body {
        padding: 2rem;
        background: white;
    }

    .form-control {
        border-radius: 25px;
        padding: 10px 15px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #20c997;
        box-shadow: 0 0 0 0.2rem rgba(32,201,151,0.25);
    }

    .btn-primary {
        background-color: #20c997;
        border: none;
        border-radius: 25px;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #1aa878;
        transform: translateY(-3px);
        box-shadow: 0 4px 6px rgba(32,201,151,0.3);
    }

    .text-center a {
        color: #25283B;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .text-center a:hover {
        color: #20c997;
    }

    @media (max-width: 768px) {
        .card {
            width: 90%;
            margin: auto;
        }
    }
        </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card login-card">
                    <div class="card-header text-center">
                        <h3 class="m-0">User Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                            <div class="text-center mt-3">
                                <a href="register.php" class="text-muted">Don't have an account? Register</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
