<?php
require_once '../includes/auth.php'; 
require_once '../includes/DatabaseHandler.php'; 

// Initialize database connection
$db = new DatabaseHandler();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $age = $_POST['age'] ?? null;
    $phone_number = $_POST['contact_info'] ?? '';
    $role = $_POST['role'] ?? 'patient';

    // Validation
    $errors = [];
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (empty($password)) $errors[] = "Password is required";
    if ($age && ($age < 18 || $age > 120)) $errors[] = "Invalid age";

    // If no errors, proceed with registration
    if (empty($errors)) {
        $auth = new Auth();
        
        // Prepare additional details for registration
        $additionalDetails = [
            'age' => $age,
            'phone_number' => $phone_number,
            // Add doctor-specific details if role is doctor
            'specialization' => $role === 'doctor' ? ($_POST['specialization'] ?? null) : null,
            'license_number' => $role === 'doctor' ? ($_POST['license_number'] ?? null) : null,
            'consultation_fee' => $role === 'doctor' ? ($_POST['consultation_fee'] ?? null) : null
        ];

        try {
            // Attempt registration
            $registrationResult = $auth->register(
                $name, 
                $email, 
                $password, 
                $role, 
                $additionalDetails
            );

            if ($registrationResult) {
                // Redirect based on role
                if ($role === 'doctor') {
                    header("Location: login_selection.php");
                } else {
                    header("Location: login_selection.php");
                }
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Elderly Healthcare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        }

        .card:hover {
            transform: rotateX(0deg) scale(1.02);
            box-shadow: 
                0 15px 35px rgba(37, 40, 59, 0.15),
                0 25px 60px rgba(37, 40, 59, 0.1);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            text-align: center;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            padding: 20px;
        }

        .card-header h3 {
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 2px;
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
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-3px);
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            .card {
                margin: 20px;
                transform: none;
            }
        }
        @media (max-width: 992px) {
    .container {
        max-width: 100%;
        padding: 15px;
    }

    .card {
        width: 100%;
        margin: 0 auto;
    }
}

@media (max-width: 768px) {
    body {
        display: flex;
        align-items: flex-start;
        padding-top: 30px;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .form-control {
        font-size: 14px;
        padding: 8px 12px;
    }

    .btn-primary {
        font-size: 14px;
        padding: 10px;
    }
}

@media (max-width: 576px) {
    .card-header h3 {
        font-size: 1.2rem;
        letter-spacing: 1px;
    }

    .form-label {
        font-size: 0.9rem;
    }

    input, select {
        font-size: 14px;
    }
}

/* Ensure form elements are full width on small screens */
@media (max-width: 480px) {
    .form-control, .btn {
        width: 100% !important;
    }

    .card {
        margin: 10px;
        padding: 0;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h3>Register</h3>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($errors)): ?>
                            <div class="error-message">
                                <?php foreach($errors as $error): ?>
                                    <p><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required 
                                       pattern="[A-Za-z\s]+" 
                                       placeholder="Enter your full name" 
                                       title="Name should contain only letters">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required 
                                       placeholder="Enter your email address">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" 
                                       required placeholder="Create a strong password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Age</label>
                                <input type="number" name="age" class="form-control" 
                                       required min="18" max="120" 
                                       placeholder="Enter your age">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" name="contact_info" class="form-control" 
                                       placeholder="Enter your contact number">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Register As</label>
                                <select name="role" id="role" class="form-control">
                                    <option value="patient">Patient</option>
                                    <option value="doctor">Doctor</option>
                                </select>
                            </div>

                            <!-- Doctor-specific fields (initially hidden) -->
                            <div id="doctorFields" style="display:none;">
                                <div class="mb-3">
                                    <label class="form-label">Specialization</label>
                                    <input type="text" name="specialization" class="form-control" 
                                           placeholder="Enter your medical specialization">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">License Number</label>
                                    <input type="text" name="license_number" class="form-control" 
                                           placeholder="Enter your medical license number">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Consultation Fee</label>
                                    <input type="number" name="consultation_fee" class="form-control" 
                                           placeholder="Enter your consultation fee">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Register</button>
                            
                            <div class="text-center mt-3">
                                <a href="login_selection.php" class="text-muted">Already have an account?</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide doctor-specific fields based on role selection
        document.getElementById('role').addEventListener('change', function() {
            const doctorFields = document.getElementById('doctorFields');
            doctorFields.style.display = this.value === 'doctor' ? 'block' : 'none';
        });
    </script>
</body>
</html>
