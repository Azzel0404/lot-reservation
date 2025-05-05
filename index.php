<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lot Reservation System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Modern Color Palette */
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --background: #f1f5f9;
        }

        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            color: var(--dark);
            line-height: 1.6;
        }

        /* Header Styles */
        header {
            position: fixed;
            width: 100%;
            top: 0;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 15px 5%;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary);
            margin-right: 30px;
            display: flex;
            align-items: center;
        }

        .logo i {
            margin-right: 10px;
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin-right: 15px;
        }

        nav ul li a {
            color: var(--dark);
            text-decoration: none;
            font-size: 1rem;
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        nav ul li a i {
            margin-right: 8px;
            font-size: 0.9rem;
        }

        nav ul li a:hover, 
        nav ul li a.active {
            color: var(--primary);
            background-color: rgba(37, 99, 235, 0.1);
        }

        /* Login Button */
        .login-button a {
            background-color: var(--primary);
            color: white;
            padding: 10px 25px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .login-button a i {
            margin-right: 8px;
        }

        .login-button a:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Section Styles */
        .main-content {
            padding-top: 80px;
            min-height: 100vh;
        }

        section {
            min-height: calc(100vh - 80px);
            padding: 5% 10%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease;
            position: absolute;
            width: 100%;
            top: 80px;
            left: 0;
            visibility: hidden;
        }

        section.active {
            opacity: 1;
            transform: translateY(0);
            visibility: visible;
            position: relative;
        }

        h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary);
            font-weight: 700;
        }

        h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--primary);
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            max-width: 700px;
            margin-bottom: 30px;
            color: var(--dark);
        }

        /* Home Section */
        #home {
            background: linear-gradient(135deg, rgba(241, 245, 249, 0.9) 0%, rgba(241, 245, 249, 0.95) 100%), 
                        url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
        }

        .hero-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        /* About Section */
        #about {
            background-color: white;
        }

        .about-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
            width: 100%;
            max-width: 1000px;
        }

        .feature-card {
            background: var(--light);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        /* Services Section */
        #services {
            background-color: var(--light);
        }

        #services ul {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            width: 100%;
            max-width: 900px;
        }

        #services li {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            text-align: left;
            position: relative;
            padding-left: 60px;
        }

        #services li:hover {
            transform: translateY(-10px);
        }

        #services li i {
            position: absolute;
            left: 20px;
            top: 30px;
            font-size: 1.5rem;
            color: var(--secondary);
        }

        /* Contact Section */
        #contact {
            background-color: white;
        }

        .contact-info {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .contact-card {
            background: var(--light);
            padding: 25px;
            border-radius: 10px;
            min-width: 250px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .contact-card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px;
            background-color: var(--dark);
            color: white;
            font-size: 0.9rem;
        }

        .social-icons {
            margin-top: 15px;
        }

        .social-icons a {
            color: white;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: var(--primary);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                padding: 15px;
            }

            .nav-links {
                width: 100%;
                justify-content: space-between;
                margin-top: 15px;
            }

            nav ul {
                flex-wrap: wrap;
                justify-content: center;
            }

            nav ul li {
                margin: 5px;
            }

            h1 {
                font-size: 2.2rem;
            }

            h2 {
                font-size: 1.8rem;
            }

            .about-features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<header>
    <nav>
        <div class="nav-links">
            <div class="logo">
                <i class="fas fa-map-marked-alt"></i>
                <span>ReserveIt</span>
            </div>
            <ul>
                <li><a href="#" class="active" onclick="showSection('home')"><i class="fas fa-home"></i>Home</a></li>
                <li><a href="#" onclick="showSection('about')"><i class="fas fa-info-circle"></i>About</a></li>
                <li><a href="#" onclick="showSection('services')"><i class="fas fa-concierge-bell"></i>Services</a></li>
                <li><a href="#" onclick="showSection('contact')"><i class="fas fa-envelope"></i>Contact</a></li>
            </ul>
        </div>
        
        <div class="login-button">
            <a href="/lot-reservation/login.php"><i class="fas fa-sign-in-alt"></i>Login</a>
        </div>
    </nav>
</header>

<div class="main-content">
    <!-- Home Section -->
    <section id="home" class="active">
      
        <h1>Modern Lot Reservation System</h1>
        <p>Streamline your property management with our easy-to-use reservation platform. Book, manage, and track lots in real-time with complete transparency.</p>
        <div class="login-button" style="margin-top: 20px;">
            <a href="/lot-reservation/login.php"><i class="fas fa-rocket"></i>Get Started</a>
        </div>
    </section>

    <!-- About Section -->
    <section id="about">
        <h2>About Our Platform</h2>
        <p>We've revolutionized lot reservations by creating a seamless digital experience that eliminates paperwork and reduces administrative overhead.</p>
        
        <div class="about-features">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>Time-Saving</h3>
                <p>Reduce manual processing time by up to 80% with our automated reservation system.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Secure</h3>
                <p>Bank-level security protects all your transactions and customer data.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile-Friendly</h3>
                <p>Access your reservations from anywhere, on any device.</p>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services">
        <h2>Our Comprehensive Services</h2>
        <p>We offer a complete suite of tools to manage your property reservations efficiently.</p>
        <ul>
            <li><i class="fas fa-bolt"></i>Instant Online Reservations</li>
            <li><i class="fas fa-binoculars"></i>Real-time Availability Tracking</li>
            <li><i class="fas fa-lock"></i>Secure Payment Processing</li>
            <li><i class="fas fa-bell"></i>Automated Notifications</li>
            <li><i class="fas fa-chart-bar"></i>Detailed Reporting</li>
            <li><i class="fas fa-mobile"></i>Mobile-Friendly Access</li>
        </ul>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <h2>Get In Touch</h2>
        <p>Have questions or need support? Our team is ready to assist you.</p>
        <div class="contact-info">
            <div class="contact-card">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>support@ReserveIt.com</p>
            </div>
            <div class="contact-card">
                <i class="fas fa-phone-alt"></i>
                <h3>Phone</h3>
                <p>+1 (555) 123-4567</p>
            </div>
            <div class="contact-card">
                <i class="fas fa-building"></i>
                <h3>Office</h3>
                <p>123 Business Ave, Suite 200<br>San Francisco, CA 94107</p>
            </div>
        </div>
    </section>
</div>

<footer>
    <p>&copy; 2025 Lot Reservation System. All rights reserved.</p>
    <div class="social-icons">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
    </div>
</footer>

<script>
    // Function to show the corresponding section when the navigation item is clicked
    function showSection(sectionId) {
        // Hide all sections and remove active class from nav items
        const sections = document.querySelectorAll('section');
        const navLinks = document.querySelectorAll('nav ul li a');
        
        sections.forEach(section => {
            section.classList.remove('active');
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
        });
        
        // Show the selected section and mark nav item as active
        document.getElementById(sectionId).classList.add('active');
        document.querySelector(`a[onclick="showSection('${sectionId}')"]`).classList.add('active');
        
        // Scroll to top smoothly
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
</script>

</body>
</html>