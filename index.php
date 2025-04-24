<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lot Reservation System</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* General styles for body, sections, and text */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        header {
            position: fixed;
            width: 100%;
            top: 0;
            background-color: #12254c;
            z-index: 1000;
            padding: 10px 20px;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav ul {
            list-style: none;
            display: flex;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin-right: 20px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background-color: #1c3b70;
            border-radius: 5px;
        }

              /* Adjust Login button on the right side */
        .login-button {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-left: 30px;
        }

        .login-button a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 12px 50px; /* Increase padding for better visibility */
            border-radius: 5px;
            transition: background-color 0.3s;
            display: inline-block;
            font-weight: bold; /* Make text bold */
            text-align: center; /* Center-align the text */
        }

        .login-button a:hover {
            background-color: #12254c;
        }
        /* Section Styles */
        section {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 20px;
            transition: opacity 0.5s ease-in-out;
        }

        #home {
            background-color: #f2f2f2;
        }

        #about {
            background-color: #e1e1e1;
            display: none; /* Initially hidden */
        }

        #services {
            background-color: #d1d1d1;
            display: none; /* Initially hidden */
        }

        #contact {
            background-color: #c1c1c1;
            display: none; /* Initially hidden */
        }

        h1, h2 {
            margin: 0;
        }

        footer {
            text-align: center;
            padding: 10px;
            background-color: #12254c;
            color: white;
        }

        /* Add slow motion animation when transitioning between sections */
        .fadeIn {
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        /* Make navigation stick to top */
        header, footer {
            position: fixed;
            width: 100%;
            z-index: 10;
        }

        footer {
            bottom: 0;
        }
    </style>
</head>
<body>

<header>
    <nav>
        <!-- Left-side navigation items -->
        <ul>
            <li><a href="#" onclick="showSection('home')">Home</a></li>
            <li><a href="#" onclick="showSection('about')">About</a></li>
            <li><a href="#" onclick="showSection('services')">Services</a></li>
            <li><a href="#" onclick="showSection('contact')">Contact</a></li>
        </ul>
        
        <!-- Right-side Login -->
        <div class="login-button">
            <a href="/lot-reservation/login.php">Login</a>
        </div>
    </nav>
</header>

<!-- Home Section -->
<section id="home" class="fadeIn">
    <h1>Welcome to the Lot Reservation System</h1>
    <p>Your trusted platform for reserving lots with ease.</p>
</section>

<!-- About Section -->
<section id="about" class="fadeIn">
    <h2>About Us</h2>
    <p>We provide a seamless experience for reserving lots, ensuring transparency and convenience.</p>
</section>

<!-- Services Section -->
<section id="services" class="fadeIn">
    <h2>Our Services</h2>
    <ul>
        <li>Easy Lot Reservations</li>
        <li>Real-time Availability</li>
        <li>Secure Transactions</li>
    </ul>
</section>

<!-- Contact Section -->
<section id="contact" class="fadeIn">
    <h2>Contact Us</h2>
    <p>Email: support@lotreservation.com</p>
    <p>Phone: +123 456 7890</p>
</section>

<footer>
    <p>&copy; 2025 Lot Reservation System. All rights reserved.</p>
</footer>

<script>
    // Function to show the corresponding section when the navigation item is clicked
    function showSection(sectionId) {
        // Hide all sections first
        const sections = document.querySelectorAll('section');
        sections.forEach(section => section.style.display = 'none');

        // Show the selected section with fadeIn effect
        const selectedSection = document.getElementById(sectionId);
        selectedSection.style.display = 'flex';
        selectedSection.classList.add('fadeIn');
    }

    // Initially show the home section
    window.onload = () => showSection('home');
</script>

</body>
</html>
