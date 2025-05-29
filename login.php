<?php
session_start();

// Redirect logged-in users based on role
if (isset($_SESSION["user_id"], $_SESSION["user_role"])) {
    switch ($_SESSION["user_role"]) {
        case "admin":
            header("Location: admin/dashboard.php");
            exit();
        case "organizer":
            header("Location: organizer/dashboard.php");
            exit();
        case "user":
            header("Location: user/dashboard.php");
            exit();
        default:
            header("Location: index.php");
            exit();
    }
}

require_once('php/db.php'); // your database connection

// Fetch site info from database
$siteResult = mysqli_query($conn, "SELECT * FROM site_info LIMIT 1");
$site = mysqli_fetch_assoc($siteResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars($site['company_name']) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <h1><?= htmlspecialchars($site['company_name']) ?></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php#features">Features</a></li>
                    <li><a href="index.php#events">Events</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary">Sign Up</a></li>
                </ul>
            </nav>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="auth-container">
            <h2 class="form-title">Login to Your Account</h2>
            <form id="loginForm" action="php/login.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="emailPHP" class="form-input emailLoginField" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="passwordPHP" class="form-input passwordLoginField" required>
                </div>

                <div class="form-group form-remember">
                        <label>
                            <input type="checkbox" id="remember" name="remember">
                            Remember Me
                        </label>
                    </div>
                <button type="submit" class="btn-submit">Login</button>
            </form>
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>

    <?php include_once("footer.php"); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Mobile menu toggle
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('active');
        });

        function showNotification(message, isError = false) {
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) existingNotification.remove();

            const notification = document.createElement('div');
            notification.className = `notification ${isError ? 'notification-error' : 'notification-success'}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas ${isError ? 'fa-exclamation-circle' : 'fa-check-circle'}"></i>
                    <p>${message}</p>
                </div>
                <button class="notification-close">&times;</button>
            `;

            document.body.appendChild(notification);

            notification.querySelector('.notification-close').addEventListener('click', function () {
                notification.remove();
            });

            setTimeout(() => {
                if (document.body.contains(notification)) {
                    notification.remove();
                }
            }, 5000);
        }

        function validateLoginForm() {
            const email = document.querySelector('.emailLoginField').value.trim();
            const password = document.querySelector('.passwordLoginField').value;

            if (!email) {
                showNotification('Please enter your email address', true);
                return false;
            }

            if (!isValidEmail(email)) {
                showNotification('Please enter a valid email address', true);
                return false;
            }

            if (!password) {
                showNotification('Please enter your password', true);
                return false;
            }

            return true;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        $('#loginForm').on('submit', function (e) {
    e.preventDefault();

    if (!validateLoginForm()) return;

    const email = $('.emailLoginField').val().trim();
    const password = $('.passwordLoginField').val();
    const remember = $('#remember').is(':checked') ? 1 : 0;

    const submitButton = $(this).find('button[type="submit"]');
    const originalText = submitButton.text();

    submitButton.prop('disabled', true);
    submitButton.html('<i class="fas fa-spinner fa-spin"></i> Logging in...');

    $.ajax({
        url: 'php/login.php',
        type: 'POST',
        data: {
            emailPHP: email,
            passwordPHP: password,
            remember: remember
        },
        success: function (response) {
            submitButton.prop('disabled', false);
            submitButton.html(originalText);

            console.log('Login response:', response); // For debugging

            if (response.includes('user') || response.includes('admin') || response.includes('organizer')) {
                showNotification('Login successful! Redirecting...', false);
                setTimeout(() => {
                    if (response.includes('user')) {
                        window.location.href = 'user/dashboard.php';
                    } else if (response.includes('admin')) {
                        window.location.href = 'admin/dashboard.php';
                    } else if (response.includes('organizer')) {
                        window.location.href = 'organizer/dashboard.php';
                    }
                }, 1000);
            } else {
                showNotification(response || 'Invalid email or password', true);
            }
        },
        error: function (xhr, status, error) {
            console.error("Login AJAX error:", status, error); // Optional
            submitButton.prop('disabled', false);
            submitButton.html(originalText);
            showNotification('Connection error. Please check your internet connection.', true);
        }
        });
    });

    </script>
    <script src="js/main.js"></script>
</body>
</html>
