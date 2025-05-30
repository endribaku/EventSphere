<?php
session_start();

// Redirect logged-in users to their dashboard
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

$siteResult = mysqli_query($conn, "SELECT * FROM site_info LIMIT 1");
$site = mysqli_fetch_assoc($siteResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= htmlspecialchars($site['company_name']) ?></title>
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
            <h2 class="form-title">Create Your Account</h2>
            <form id="registerForm" action="php/register.php" method="post">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                    <span class="form-text">Password must be at least 8 characters long.</span>
                </div>
                <div class="form-group">
                    <label for="role">I want to:</label>
                    <select id="role" name="role" class="form-select" required>
                        <option value="user">Attend Events</option>
                        <option value="organizer">Organize Events</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Create Account</button>
            </form>
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>

    <?php include_once("footer.php"); ?>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>

        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('active');
        });

        function showNotification(message, isError = false) {

            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }
            

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
            

            notification.querySelector('.notification-close').addEventListener('click', function() {
                notification.remove();
            });
            
   
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    notification.remove();
                }
            }, 5000);
        }
        

        function validateRegisterForm() {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (!name) {
                showNotification('Please enter your full name', true);
                return false;
            }
            
            if (!email) {
                showNotification('Please enter your email address', true);
                return false;
            }
            
            if (!isValidEmail(email)) {
                showNotification('Please enter a valid email address', true);
                return false;
            }
            
            if (!password) {
                showNotification('Please enter a password', true);
                return false;
            }
            
            if (password.length < 8) {
                showNotification('Password must be at least 8 characters long', true);
                return false;
            }
            
            return true;
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();

            if (!validateRegisterForm()) {
                return;
            }

            const name = $('#name').val();
            const email = $('#email').val();
            const password = $('#password').val();
            const role = $('#role').val();

            const submitButton = $(this).find('button[type="submit"]');
            const originalText = submitButton.text();

            submitButton.prop('disabled', true);
            submitButton.html('<i class="fas fa-spinner fa-spin"></i> Creating account...');

            $.ajax({
                url: 'php/register.php',
                type: 'POST',
                data: {
                    name: name,
                    email: email,
                    password: password,
                    role: role
                },
                success: function(response) {
                    submitButton.prop('disabled', false);
                    submitButton.text(originalText);

                    if (response.includes('User registered')) {
                        showNotification('Registration successful! Redirecting to login page...', false);
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    } else {
                        showNotification(response || 'Registration failed. Please try again.', true);
                    }
                },
                error: function() {
                    submitButton.prop('disabled', false);
                    submitButton.text(originalText);
                    showNotification('Server error. Please try again later.', true);
                }
                });

        });
    </script>

    <script src="js/main.js"></script>
</body>
</html>
