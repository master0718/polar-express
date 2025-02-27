<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Polar Express</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Page Background */
        body {
            background-color: #003f74;
            color: #ffffff;
        }

        /* Header Section */
        .header {
            position: relative;
            width: 90%;
            max-width: 1600px;
            margin: 0 auto;
            height: auto;
            text-align: center;
        }

        .header img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Desktop Menu */
        .menu {
            position: absolute;
            top: 185px;
            /* Lowered by 15px */
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: nowrap;
            white-space: nowrap;
            /* Prevent line breaks for multi-word items */
        }

        .menu a {
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            color: gold;
            padding: 5px 10px;
            transition: 0.3s;
        }

        .menu a:hover {
            color: white;
            border-bottom: 2px solid white;
        }

        /* Mobile Menu */
        .mobile-menu {
            display: none;
            position: absolute;
            bottom: 10px;
            right: 10px;
            z-index: 10;
        }

        .mobile-menu button {
            background-color: gold;
            color: #003f74;
            border: none;
            padding: 10px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
        }

        /* Mobile Menu Links */
        .mobile-links {
            display: none;
            position: absolute;
            bottom: -220px;
            right: 10px;
            background-color: #003f74;
            border: 1px solid white;
            padding: 10px;
            z-index: 100;
        }

        .mobile-links a {
            display: block;
            color: gold;
            text-decoration: none;
            margin-bottom: 10px;
        }

        .mobile-links a:hover {
            color: white;
        }

        /* Below Header Section */
        .info-section {
            padding: 20px;
            text-align: center;
        }

        .info-section h2 {
            color: gold;
            margin-bottom: 10px;
        }

        .info-section p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        /* Rotary Club Section */
        .rotary-section {
            margin-top: 20px;
            text-align: center;
        }

        .rotary-section img {
            width: 150px;
            margin: 10px auto;
        }

        .rotary-section a {
            text-decoration: none;
            color: gold;
            font-size: 18px;
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 1280px) {
            .menu {
                display: none;
                /* Always hide the desktop menu */
            }

            .mobile-menu {
                display: block;
                /* Show collapsed menu button */
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <img src="PEHeader.jpg" alt="Polar Express Header">


        <!-- Below Header Section -->
        <div class="info-section">
            <h2>Event Details</h2>
            <p><strong>Event Days:</strong> Saturday & Sunday</p>
            <p><strong>First Ride Time:</strong> 9:30 AM</p>
            <p><strong>Ticket Prices:</strong> $42 for Table Seats, $38 for Regular Seats</p>

            <!-- Rotary Club Section -->
            <div class="rotary-section">
                <h2>Presented by White River Rotary Club</h2>
                <a href="http://whiteriverrotaryusa.org/" target="_blank">
                    <img src="rotary.png" alt="Rotary Club Logo">
                </a>
                <a href="http://whiteriverrotaryusa.org/" target="_blank">Visit the Rotary Club</a>
            </div>
        </div>

        <!-- Script for Mobile Menu -->
        <script>
            function toggleMenu() {
                const menu = document.getElementById('mobileLinks');
                menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
            }
        </script>
</body>

</html>