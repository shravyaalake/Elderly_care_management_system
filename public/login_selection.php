<!DOCTYPE html>
<html>
<head>
    <title>Login Selection - Elderly Care Management System</title>
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

        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 
                0 10px 25px rgba(37, 40, 59, 0.1),
                0 20px 50px rgba(37, 40, 59, 0.05);
            padding: 40px;
            width: 100%;
            max-width: 900px;
            transform: rotateX(10deg);
            transition: all 0.4s ease;
            display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
        }

        .login-section {
            width: 60%;
            padding-right: 30px;
            border-right: 1px solid #e0e0e0;
        }

        .message-section {
            width: 40%;
            padding-left: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .message-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .message-section textarea {
            width: 100%;
            height: 200px;
            margin-bottom: 15px;
            border: 1px solid var(--primary-color);
            border-radius: 8px;
            padding: 10px;
        }

        .login-container:hover {
            transform: rotateX(0deg) scale(1.02);
            box-shadow: 
                0 15px 35px rgba(37, 40, 59, 0.15),
                0 25px 60px rgba(37, 40, 59, 0.1);
        }

        h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            width: 80px;
            height: 3px;
            background: var(--accent-color);
            transform: translateX(-50%);
        }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn i {
            font-size: 1.2rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-5px);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-5px);
        }

        .btn-success {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-success:hover {
            background-color: #17a2b8;
            border-color: #17a2b8;
            transform: translateY(-5px);
        }

        .btn-outline-dark {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-dark:hover {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 450px;
            }
            .login-section, .message-section {
                width: 100%;
                border-right: none;
                padding: 0;
                display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
            }
            /* Centering buttons and content */
.d-grid {
    width: 100%; /* Ensure full width */
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Additional centering for message section */
.message-section {
    text-align: center;
}

.message-section textarea {
    width: 100%;
    max-width: 300px; /* Limit width if needed */
    margin: 0 auto;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .login-container {
        flex-direction: column;
        align-items: center;
    }
    
    .login-section, .message-section {
        width: 100%;
        max-width: 350px; /* Limit max width on smaller screens */
    }
}
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 mx-auto">
    
        <div class="row">
            <div class="col-12">
                <div class="login-container text-center">
                    <div class="login-section">
                        <h1 class="mb-4">Choose Your Login Type</h1>
                        <div class="d-grid gap-3">
                            <a href="patient_login.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-person"></i> User Login
                            </a>
                            <a href="doctor_login.php" class="btn btn-secondary btn-lg">
                                <i class="bi bi-clipboard-plus"></i> Doctor Login
                            </a>
                            <a href="register.php" class="btn btn-success btn-lg">
                                <i class="bi bi-person-plus"></i> Register New Account
                            </a>
                            <a href="index.php" class="btn btn-outline-dark btn-sm mt-3">
                                Back to Home
                            </a>
                        </div>
                    </div>
                    <div class="message-section">
                        <h3>Leave a Message</h3>
                        <textarea placeholder="Write your message here..."></textarea>
                        <button class="btn btn-primary">Send Message</button>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

</body>
</html>
