<?php 
    require_once("admin_auth.php");
    include_once("admin_header.php");
    require_once("../php/db.php");
?>

<div class="dashboard-welcome">
    <h2>Admin Dashboard</h2>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']); ?>. Manage your event booking platform from here.</p>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <?php
            // Count total users
            $result = $conn->query("SELECT COUNT(*) as count FROM users");
            $users_count = $result->fetch_assoc()['count'];
        ?>
        <h3>Total Users</h3>
        <div class="stat-value"><?= $users_count ?></div>
    </div>
    
    <div class="stat-card">
        <?php
            // Count total events
            $result = $conn->query("SELECT COUNT(*) as count FROM events");
            $events_count = $result->fetch_assoc()['count'];
        ?>
        <h3>Total Events</h3>
        <div class="stat-value"><?= $events_count ?></div>
    </div>
    
    <div class="stat-card">
        <?php
            // Count total bookings
            $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
            $bookings_count = $result->fetch_assoc()['count'];
        ?>
        <h3>Total Bookings</h3>
        <div class="stat-value"><?= $bookings_count ?></div>
    </div>
    
    <div class="stat-card">
        <?php
            // Count total venues
            $result = $conn->query("SELECT COUNT(*) as count FROM venues");
            $venues_count = $result->fetch_assoc()['count'];
        ?>
        <h3>Total Venues</h3>
        <div class="stat-value"><?= $venues_count ?></div>
    </div>
</div>

<div class="dashboard-content">
    <h3>Recent Bookings</h3>
    
    <?php
        // Get recent bookings
        $result = $conn->query("SELECT b.id, u.name as user_name, e.title as event_title, b.tickets, b.booking_date 
                               FROM bookings b 
                               JOIN users u ON b.user_id = u.id 
                               JOIN events e ON b.event_id = e.id 
                               ORDER BY b.booking_date DESC LIMIT 5");
        
        if ($result->num_rows > 0) {
            echo '<table>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Event</th>
                        <th>Tickets</th>
                        <th>Booking Date</th>
                    </tr>';
            
            while ($booking = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . $booking['id'] . '</td>
                        <td>' . htmlspecialchars($booking['user_name']) . '</td>
                        <td>' . htmlspecialchars($booking['event_title']) . '</td>
                        <td>' . $booking['tickets'] . '</td>
                        <td>' . $booking['booking_date'] . '</td>
                      </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No bookings found.</p>';
        }
    ?>
    
    <div class="view-all">
        <a href="bookings.php" class="btn btn-secondary">View All Bookings</a>
    </div>
</div>

<div class="dashboard-content">
    <h3>Upcoming Events</h3>
    
    <?php
        // Get upcoming events
        $result = $conn->query("SELECT e.id, e.title, e.date, v.name as venue_name, c.name as category_name 
                               FROM events e 
                               JOIN venues v ON e.venue_id = v.id 
                               JOIN event_categories c ON e.category_id = c.id 
                               WHERE e.date >= CURDATE() 
                               ORDER BY e.date ASC LIMIT 5");
        
        if ($result->num_rows > 0) {
            echo '<table>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Venue</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>';
            
            while ($event = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($event['title']) . '</td>
                        <td>' . htmlspecialchars($event['category_name']) . '</td>
                        <td>' . htmlspecialchars($event['venue_name']) . '</td>
                        <td>' . $event['date'] . '</td>
                        <td><a href="event_details.php?id=' . $event['id'] . '" class="btn btn-sm btn-primary">View</a></td>
                      </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No upcoming events found.</p>';
        }
    ?>
    
    <div class="view-all">
        <a href="events.php" class="btn btn-secondary">View All Events</a>
    </div>
</div>

</div> <!-- Close dashboard-container -->
</body>
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

</html>
