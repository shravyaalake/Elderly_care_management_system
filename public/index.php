

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elderly Care Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        :root {
            --primary-bg: #D2D2D2;
            --secondary-bg: #25283B;
            --text-color: #333;
        }
        body {
            background-color: var(--primary-bg);
            background-image: repeating-linear-gradient(
                to right, transparent 0 100px,
                #25283b22 100px 101px
            ),
            repeating-linear-gradient(
                to bottom, transparent 0 100px,
                #25283b22 100px 101px
            );
            font-family: 'Arial', sans-serif;
            overflow-x: hidden;
        }
        body::before {
            position: absolute;
            width: min(1400px, 90vw);
            top: 10%;
            left: 50%;
            height: 90%;
            transform: translateX(-50%);
            content: '';
            background-image: url(images/bg.png);
            background-size: 100%;
            background-repeat: no-repeat;
            background-position: top center;
            pointer-events: none;
            z-index: -1;
        }
        .banner {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .continue-btn {
            position: fixed;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 30px;
            background-color: var(--secondary-bg);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            z-index: 10;
        }
        .continue-btn:hover {
            background-color: var(--primary-bg);
            color: var(--secondary-bg);
        }
        .content {
            text-align: center;
            color: var(--text-color);
            z-index: 5;
        }
        .author {
            background: rgba(255,255,255,0.8);
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            margin: 0 auto;
        }
        /* Responsive Adjustments */
        @media screen and (max-width: 768px) {
            body::before {
                background-size: cover;
                width: 100%;
            }
            .banner .slider {
                width: 150px;
                height: 200px;
                left: calc(50% - 75px);
            }
            .continue-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            .author h2 {
                font-size: 1.5rem;
            }
            .author p {
                font-size: 0.9rem;
            }
        }
        @media screen and (max-width: 480px) {
            .banner .slider {
                width: 100px;
                height: 150px;
                left: calc(50% - 50px);
            }
            .author {
                padding: 10px;
                width: 90%;
            }
        }
        .slider {
    position: relative;
    width: 300px;
    height: 300px;
    margin: 100px auto;
    transform-style: preserve-3d;
    animation: rotate 20s linear infinite;
}

.slider .item {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    transform-origin: center;
    transform: rotateY(calc((var(--position) - 1) * (360 / var(--quantity)) * 1deg)) translateZ(350px);
    transition: transform 0.5s ease;
}

.slider .item img {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 50%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

@keyframes rotate {
    from { transform: perspective(1000px) rotateY(0deg); }
    to { transform: perspective(1000px) rotateY(360deg); }
}
.neon-heading {
    text-align: center;
    color: #25283B;
    font-family: 'Arial', sans-serif;
    font-size: 2.5rem;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    text-shadow: 
        0 0 5px rgba(37, 40, 59, 0.5),
        0 0 10px rgba(37, 40, 59, 0.4),
        0 0 15px rgba(37, 40, 59, 0.3);
    transition: all 0.3s ease;
}

.neon-heading::after {
    content: attr(data-text);
    position: absolute;
    top: 0;
    left: 0;
    color: #4A5568;
    z-index: -1;
    opacity: 0.7;
    filter: blur(15px);
}

.neon-heading:hover {
    text-shadow: 
        0 0 10px rgba(37, 40, 59, 0.7),
        0 0 20px rgba(37, 40, 59, 0.6),
        0 0 30px rgba(37, 40, 59, 0.5);
    transform: scale(1.05);
}

    </style>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="banner">
        <div class="slider" style="--quantity: 10">
            <?php
            // Dynamic image generation
            $images = [
                'https://lirp.cdn-website.com/981e7caa/dms3rep/multi/opt/Image+of+Who+We+Support-640w.png', 'https://meritechsolutions.com/assets/img/activity-monitoring-system-for-elderly.jpg', 'https://www.abelalarm.co.uk/wp-content/uploads/2020/06/5-home-automation-elderly.jpg', 
                'https://media.istockphoto.com/id/1473155461/photo/nurse-hands-and-senior-patient-in-empathy-safety-and-support-of-help-trust-and-healthcare.jpg?s=612x612&w=0&k=20&c=I5fh75AaVB0hVNE4-7JeY9g6sugFP4_4ZEQRPAPvJws=', 'https://www.meddbase.com/wp-content/uploads/2023/03/How-a-Practice-Management-System-Can-Improve-Patient-Care-Efficiency_web-scaled.jpg', 'https://images.pexels.com/photos/3791664/pexels-photo-3791664.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', 
                'https://images.pexels.com/photos/40568/medical-appointment-doctor-healthcare-40568.jpeg', 'https://images.pexels.com/photos/8441870/pexels-photo-8441870.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', 'https://images.pexels.com/photos/5998502/pexels-photo-5998502.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2', 
                'https://images.pexels.com/photos/8949909/pexels-photo-8949909.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'
            ];
            
            foreach ($images as $index => $image) {
                echo "<div class='item' style='--position: " . ($index + 1) . "'>";
                echo "<img src='{$image}'  loading='lazy' alt='Dragon Image " . ($index + 1) . "'>";
                echo "</div>";
            }
            ?>
        </div>
        <div class="content">
            <div class="author">
               
<h2 class="neon-heading" data-text="Elderly Care Management System">
    Elderly Care Management System
</h2>

                <br>
                <p><b>Where care meets technology ,</b></p>
                <br>
                <p>Empowering the golden years with compassion, care, and convenience. Because every moment matters.</p>
                <a href="login_selection.php" class="continue-btn">Continue with Us</a>
            </div>
            <div class="model"></div>
        </div>
    </div>       
    
    <script>
        let currentRotation = 0;
        const totalItems = document.querySelectorAll('.slider .item').length;
        let autoRunAnimationFrame;
        let isAutoRunning = true;

        function updateSlider() {
            document.querySelector('.slider').style.transform = `perspective(1000px) rotateX(-16deg) rotateY(${currentRotation}deg)`;
        }

        function autoRun() {
            if (isAutoRunning) {
                currentRotation += 0.1;
                updateSlider();
                autoRunAnimationFrame = requestAnimationFrame(autoRun);
            }
        }

        // Responsive adjustments for slider
        function adjustSliderForScreenSize() {
            const slider = document.querySelector('.slider');
            const screenWidth = window.innerWidth;
            
            if (screenWidth <= 768) {
                slider.style.width = '150px';
                slider.style.height = '200px';
            } else if (screenWidth <= 480) {
                slider.style.width = '100px';
                slider.style.height = '150px';
            } else {
                slider.style.width = '200px';
                slider.style.height = '250px';
            }
        }

        // Initial setup
        autoRun();
        adjustSliderForScreenSize();

        // Adjust on window resize
        window.addEventListener('resize', adjustSliderForScreenSize);
    </script>
</body>
</html>
