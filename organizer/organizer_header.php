<?php
session_start();
require_once('../php/db.php');

// Fetch site-wide settings like company name, email, phone
$siteResult = mysqli_query($conn, "SELECT * FROM site_info LIMIT 1");
$site = mysqli_fetch_assoc($siteResult);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($site['company_name']) ?> - Organizer Dashboard</title>
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
                <h1><a href="dashboard.php"><?= htmlspecialchars($site['company_name']) ?> Organizer</a></h1>
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
                <li class="nav_button"><a href="events.php"><i class="fas fa-calendar-alt"></i> My Events</a></li>
                <li class="nav_button"><a href="create_event.php"><i class="fas fa-plus-circle"></i> Create Event</a></li>
            </ul>
        </div>

        <div class="organizer_info">
            <i class="fas fa-user-tie"></i>
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
<script src="../js/main.js"></script>



    
