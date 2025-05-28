<?php
session_start();
require_once('../php/db.php');

// Fetch general site information
$siteResult = mysqli_query($conn, "SELECT * FROM site_info LIMIT 1");
$site = mysqli_fetch_assoc($siteResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site['company_name']) ?> - Admin Dashboard</title>
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
                <h1><a href="dashboard.php"><?= htmlspecialchars($site['company_name']) ?> Admin</a></h1>
            </div>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <div class="navigation_information">
        <div class="nav_bar">
            <ul class="nav_buttons">
                <li class="nav_button"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav_button"><a href="users.php"><i class="fas fa-users"></i> Manage Users</a></li>
                <li class="nav_button"><a href="events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                <li class="nav_button"><a href="venues.php"><i class="fas fa-map-marker-alt"></i> Manage Venues</a></li>
                <li class="nav_button"><a href="categories.php"><i class="fas fa-tags"></i> Manage Categories</a></li>
                <li class="nav_button"><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Manage Bookings</a></li>
                <li class="nav_button"><a href="site_settings.php"><i class="fas fa-cogs"></i> Site Settings</a></li> <!-- ✅ Added button -->
            </ul>
        </div>

        <div class="admin_info">
            <i class="fas fa-user-shield"></i>
            <p><?= htmlspecialchars($_SESSION["user_name"]) ?></p>
            <a href="../php/logout.php?token=<?= urlencode($_SESSION['user_token']) ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="container dashboard-container">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error']; $_SESSION['error'] = ''; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; $_SESSION['success'] = ''; ?>
            </div>
        <?php endif; ?>

    <script>
        // Mobile menu toggle
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.nav_bar').classList.toggle('active');
        });
    </script>
    <script src="../js/main.js"></script>


