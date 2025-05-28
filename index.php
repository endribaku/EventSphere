<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evently - Book Amazing Events</title>
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
                <h1>Evently</h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#events">Events</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="register.php" class="btn btn-primary">Register</a></li>
                </ul>
            </nav>
            <div class="menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Discover & Book Amazing Events</h1>
                <p>Find the perfect events for you - concerts, sports, theater, and more. Or create and manage your own events with our powerful platform.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-large">Get Started</a>
                    <a href="#events" class="btn btn-secondary btn-large">Explore Events</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="images/events/2880px-Madison_Square_Garden_(MSG)_-_Full_(48124330357).jpg" alt="Event venue with crowd">
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">Why Choose Evently?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3>Easy Booking</h3>
                    <p>Book tickets for your favorite events in just a few clicks. Secure, fast, and hassle-free.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Create Events</h3>
                    <p>Organize and manage your own events with our powerful tools for event creators.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Discover Events</h3>
                    <p>Find events that match your interests with our advanced search and filtering options.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile Friendly</h3>
                    <p>Access Evently from any device - desktop, tablet, or mobile phone.</p>
                </div>
            </div>
        </div>
    </section>

    <?php
    require_once('php/db.php'); // your database connection
    $sql = "
        SELECT 
            e.id, 
            e.title, 
            e.description, 
            e.date, 
            e.price, 
            e.image, 
            v.name AS venue_name, 
            c.name AS category_name
        FROM events e
        JOIN venues v ON e.venue_id = v.id
        JOIN event_categories c ON e.category_id = c.id
        ORDER BY e.date ASC
        LIMIT 3
    ";

    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("SQL Error: " . mysqli_error($conn));
    }
    ?>
    <section id="events" class="featured-events">
        <div class="container">
            <h2 class="section-title">Featured Events</h2>
            <div class="events-grid">
            <?php 
                while ($event = mysqli_fetch_assoc($result)):
                    $dt = new DateTime($event['date']);
                    $day = $dt->format('d');
                    $month = $dt->format('M');
                ?>
                <div class="event-card">
                    <div class="event-image">
                    <img src="<?= htmlspecialchars(str_replace('../', '', $event['image'])) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                        <div class="event-date">
                            <span class="day"><?= $day ?></span>
                            <span class="month"><?= $month ?></span>
                        </div>
                    </div>
                    <div class="event-details">
                        <h3><?= htmlspecialchars($event['title']) ?></h3>
                        <p class="event-category"><strong>Category:</strong> <?= htmlspecialchars($event['category_name']) ?></p>
                        <p class="event-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['venue_name']) ?></p>
                        <p class="event-description"><?= htmlspecialchars($event['description']) ?></p>
                        <div class="event-footer">
                            <span class="event-price">$<?= number_format($event['price'], 2) ?></span>
                            <a href="login.php" class="btn btn-sm btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <div class="view-all-container">
                <a href="login.php" class="btn btn-secondary">View All Events</a>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <h2 class="section-title">About Evently</h2>
                <p>Evently is your one-stop platform for discovering, booking, and managing events. Whether you're looking to attend the hottest concerts, sporting events, or conferences, or you're an organizer wanting to create and manage your own events, Evently has you covered.</p>
                <p>Our mission is to connect people through memorable experiences and make event management seamless for organizers.</p>
                <a href="register.php" class="btn btn-primary">Join Evently Today</a>
            </div>
            <div class="about-image">
                <img src="images/excited-audience-watching-confetti-fireworks-having-fun-music-festival-night-copy-space.jpg" alt="Event crowd">
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2>Evently</h2>
                    <p>Discover & Book Amazing Events</p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#events">Events</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-envelope"></i> info@evently.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Evently. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Simple mobile menu toggle
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('.main-nav').classList.toggle('active');
        });
    </script>
</body>
</html>

