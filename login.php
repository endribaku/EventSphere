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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Evently</title>
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
                <h1><a href="index.php">Evently</a></h1>
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
                <button type="submit" class="btn-submit">Login</button>
            </form>
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>Evently</h2>
                    <p>Discover, book, and manage events with ease.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php#features">Features</a></li>
                        <li><a href="index.php#events">Events</a></li>
                        <li><a href="index.php#about">About</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Event Street, City, Country</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 8900</p>
                    <p><i class="fas fa-envelope"></i> info@evently.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Evently. All rights reserved.</p>
            </div>
        </div>
    </footer>

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

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();

            if (!validateLoginForm()) return;

            const email = document.querySelector('.emailLoginField').value;
            const password = document.querySelector('.passwordLoginField').value;

            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'php/login.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                submitButton.disabled = false;
                submitButton.textContent = originalText;

                const response = this.responseText;
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
            };

            xhr.onerror = function() {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                showNotification('Connection error. Please check your internet connection.', true);
            };

            xhr.send('emailPHP=' + encodeURIComponent(email) + '&passwordPHP=' + encodeURIComponent(password));
        });
    </script>
    <script src="js/main.js"></script>
</body>
</html>
