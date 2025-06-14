<?php
session_start();
require_once('../php/db.php');

$siteResult = mysqli_query($conn, "SELECT * FROM site_info LIMIT 1");
$site = mysqli_fetch_assoc($siteResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site['company_name']) ?> - User Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <h1><a href="dashboard.php"><?= htmlspecialchars($site['company_name']) ?></a></h1>
            </div>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>


    <div class="navigation_information">
        <div class="nav_bar">
            <ul class="nav_buttons">
                <li class="nav_button"><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="nav_button"><a href="bookings.php"><i class="fas fa-ticket-alt"></i> My Bookings</a></li>
                <li class="nav_button"><a href="browse_events.php"><i class="fas fa-search"></i> Browse Events</a></li>
            </ul>
        </div>

        <div class="user_info">
            <i class="fas fa-user-circle"></i>
            <?php
                echo "<p>".$_SESSION["user_name"]."</p>";
            ?>
            <a href="../php/logout.php?token=<?php echo $_SESSION['user_token']; ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="container dashboard-container">
        <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; $_SESSION['error'] = ''; ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; $_SESSION['success'] = ''; ?>
            </div>
        <?php endif; ?>


    <script>
        // Mobile menu toggle
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav_bar').classList.toggle('active');
        });
    </script>
    <!-- Place the script at the end of the body for better performance -->
    <script src="../js/main.js"></script>

