<?php
    require_once("user_auth.php");
    include_once("user_header.php");
    include_once("../php/db.php");
?>

<div class="dashboard-welcome">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?> 👋</h2>
    <p>Explore and book amazing events from your dashboard.</p>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <?php
            // Count user's bookings
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $bookings_count = $result->fetch_assoc()['count'];
        ?>
        <h3>My Bookings</h3>
        <div class="stat-value"><?= $bookings_count ?></div>
    </div>
    
    <div class="stat-card">
        <?php
            // Count upcoming events for the user
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings b 
                                   JOIN events e ON b.event_id = e.id 
                                   WHERE b.user_id = ? AND e.date >= CURDATE()");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $upcoming_count = $result->fetch_assoc()['count'];
        ?>
        <h3>Upcoming Events</h3>
        <div class="stat-value"><?= $upcoming_count ?></div>
    </div>
    
    <div class="stat-card">
        <?php
            // Count total available events
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE date >= CURDATE()");
            $stmt->execute();
            $result = $stmt->get_result();
            $available_count = $result->fetch_assoc()['count'];
        ?>
        <h3>Available Events</h3>
        <div class="stat-value"><?= $available_count ?></div>
    </div>
</div>

<div class="dashboard-content">
    <h3>Your Upcoming Events</h3>
    
    <?php
        // Get user's upcoming bookings
        $stmt = $conn->prepare("SELECT e.id, e.title, e.date, v.name as venue_name, b.tickets 
                               FROM bookings b 
                               JOIN events e ON b.event_id = e.id 
                               JOIN venues v ON e.venue_id = v.id 
                               WHERE b.user_id = ? AND e.date >= CURDATE() 
                               ORDER BY e.date ASC LIMIT 5");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<table>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Tickets</th>
                        <th>Action</th>
                    </tr>';
            
            while ($booking = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($booking['title']) . '</td>
                        <td>' . htmlspecialchars($booking['date']) . '</td>
                        <td>' . htmlspecialchars($booking['venue_name']) . '</td>
                        <td>' . htmlspecialchars($booking['tickets']) . '</td>
                        <td><a href="event_details.php?id=' . $booking['id'] . '" class="btn btn-sm btn-primary">View</a></td>
                      </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>You have no upcoming events. <a href="browse_events.php">Browse events</a> to book your next experience!</p>';
        }
    ?>
    
    <div class="dashboard-actions">
        <a href="browse_events.php" class="btn btn-primary">Browse Events</a>
        <a href="bookings.php" class="btn btn-secondary">View All Bookings</a>
    </div>
</div>

<div class="dashboard-content">
    <h3>Recommended Events</h3>
    
    <?php
        // Get recommended events (simple implementation - just shows upcoming events)
        $stmt = $conn->prepare("SELECT e.id, e.title, e.date, e.price, c.name as category_name, v.name as venue_name 
                               FROM events e 
                               JOIN venues v ON e.venue_id = v.id 
                               JOIN event_categories c ON e.category_id = c.id 
                               WHERE e.date >= CURDATE() 
                               ORDER BY e.date ASC LIMIT 3");
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<div class="events-grid">';
            
            while ($event = $result->fetch_assoc()) {
                echo '<div class="event-card" onclick="window.location.href=\'event_details.php?id=' . $event['id'] . '\'">
                        <div class="event-details">
                            <h3>' . htmlspecialchars($event['title']) . '</h3>
                            <p class="event-location"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($event['venue_name']) . '</p>
                            <p><i class="fas fa-tag"></i> ' . htmlspecialchars($event['category_name']) . '</p>
                            <p><i class="fas fa-calendar-alt"></i> ' . htmlspecialchars($event['date']) . '</p>
                            <div class="event-footer">
                                <span class="event-price">$' . number_format($event['price'], 2) . '</span>
                                <a href="event_details.php?id=' . $event['id'] . '" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        </div>
                      </div>';
            }
            
            echo '</div>';
        } else {
            echo '<p>No recommended events available at this time.</p>';
        }
    ?>
</div>

</div> <!-- Close dashboard-container -->

<footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2><?= htmlspecialchars($site['company_name']) ?></h2>
                    <p><?= htmlspecialchars($site['footer_text']) ?></p>
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
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($site['email']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($site['phone']) ?></p>
                    <div class="social-links">
                        <a href="<?= htmlspecialchars($site['facebook_link']) ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= htmlspecialchars($site['twitter_link']) ?>"><i class="fab fa-twitter"></i></a>
                        <a href="<?= htmlspecialchars($site['instagram_link']) ?>"><i class="fab fa-instagram"></i></a>
                        <a href="<?= htmlspecialchars($site['linkedin_link']) ?>"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?= htmlspecialchars($site['footer_text']) ?></p>
            </div>
        </div>
    </footer>

</body>
</html>