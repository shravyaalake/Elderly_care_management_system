<?php
session_start();
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/DatabaseHandler.php';

// Check if user is already logged in
if (isset($_SESSION['role']) && $_SESSION['role'] === 'doctor') {
    header("Location: ../views/doctor/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $auth = new Auth();
    
    try {
        // Attempt login specifically for doctors
        $loginResult = $auth->login($email, $password);
        
        if ($loginResult) {
            // Verify if the user is a doctor
            $dbHandler = new DatabaseHandler();
            $userDetails = $dbHandler->getUserDetailsByEmail($email);
            
            if ($userDetails && $userDetails['role'] === 'doctor') {
                // Store doctor-specific session data
                $_SESSION['user_id'] = $userDetails['user_id'];
                $_SESSION['name'] = $userDetails['name'];
                $_SESSION['role'] = 'doctor';
                $_SESSION['email'] = $email;
                
                // Redirect to doctor dashboard
                header("Location: ../views/doctor/dashboard.php");
                exit();
            } else {
                $error = "Access denied. Not a doctor account.";
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
<html>
<head>
    <title>Doctor Login - Elderly Care Management</title>
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
            border: none;
            border-radius: 15px;
            box-shadow: 
                0 10px 25px rgba(37, 40, 59, 0.1),
                0 20px 50px rgba(37, 40, 59, 0.05);
            transition: all 0.4s ease;
            transform: rotateX(10deg);
            overflow: hidden;
        }

        .card:hover {
            transform: rotateX(0deg) scale(1.02);
            box-shadow: 
                0 15px 35px rgba(37, 40, 59, 0.15),
                0 25px 60px rgba(37, 40, 59, 0.1);
        }

        .card-header {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            text-align: center;
            padding: 20px;
            position: relative;
        }

        .card-header h3 {
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.1);
            transform: skewX(-15deg) translateX(-150%);
            transition: all 0.5s ease;
        }

        .card:hover .card-header::before {
            transform: skewX(-15deg) translateX(150%);
        }

        .form-label {
            color: var(--primary-color);
            font-weight: 600;
        }

        .form-control {
            border-color: var(--secondary-color);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(32, 201, 151, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: all 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-3px);
        }

        .login-link {
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            color: var(--accent-color);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .card {
                margin: 20px;
                transform: none;
            }
        }    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3>Doctor Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" 
                                           required placeholder="Enter your email"
                                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control" 
                                           required placeholder="Enter your password">
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                            <div class="text-center mt-3">
                                <a href="../register.php" class="login-link">
                                    Don't have an account? Register
                                </a>
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
