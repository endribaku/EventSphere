<?php
    require_once("organizer_auth.php");
    include_once("organizer_header.php");
    require_once("../php/db.php");
?>

<div class="dashboard-welcome">
    <h2>Organizer Dashboard</h2>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']); ?>. Manage your events from here.</p>
</div>

<div class="dashboard-stats">
    <div class="stat-card">
        <?php
            // Count organizer's events
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE organizer_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $events_count = $result->fetch_assoc()['count'];
        ?>
        <h3>My Events</h3>
        <div class="stat-value"><?= $events_count ?></div>
    </div>
    
    <div class="stat-card">
        <?php
            // Count upcoming events
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE organizer_id = ? AND date >= CURDATE()");
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
            // Count total bookings for organizer's events
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings b 
                                   JOIN events e ON b.event_id = e.id 
                                   WHERE e.organizer_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $bookings_count = $result->fetch_assoc()['count'];
        ?>
        <h3>Total Bookings</h3>
        <div class="stat-value"><?= $bookings_count ?></div>
    </div>
    
    <div class="stat-card">
        <?php
            // Calculate total revenue
            $stmt = $conn->prepare("SELECT SUM(e.price * b.tickets) as revenue FROM bookings b 
                                   JOIN events e ON b.event_id = e.id 
                                   WHERE e.organizer_id = ?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $revenue = $result->fetch_assoc()['revenue'] ?: 0;
        ?>
        <h3>Total Revenue</h3>
        <div class="stat-value">$<?= number_format($revenue, 2) ?></div>
    </div>
</div>

<div class="dashboard-content">
    <h3>Your Upcoming Events</h3>
    
    <?php
        // Get organizer's upcoming events
        $stmt = $conn->prepare("SELECT e.id, e.title, e.date, v.name as venue_name, 
                               (SELECT COUNT(*) FROM bookings WHERE event_id = e.id) as bookings_count
                               FROM events e 
                               JOIN venues v ON e.venue_id = v.id 
                               WHERE e.organizer_id = ? AND e.date >= CURDATE() 
                               ORDER BY e.date ASC LIMIT 5");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<table>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Bookings</th>
                        <th>Actions</th>
                    </tr>';
            
            while ($event = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($event['title']) . '</td>
                        <td>' . $event['date'] . '</td>
                        <td>' . htmlspecialchars($event['venue_name']) . '</td>
                        <td>' . $event['bookings_count'] . '</td>
                        <td>
                            <a href="event_details.php?id=' . $event['id'] . '" class="btn btn-sm btn-primary">Details</a>
                            <a href="view_bookings.php?id=' . $event['id'] . '" class="btn btn-sm btn-secondary">Bookings</a>
                        </td>
                      </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>You have no upcoming events. <a href="create_event.php">Create an event</a> to get started!</p>';
        }
    ?>
    
    <div class="dashboard-actions">
        <a href="create_event.php" class="btn btn-primary">Create New Event</a>
        <a href="events.php" class="btn btn-secondary">View All Events</a>
    </div>
</div>

<div class="dashboard-content">
    <h3>Recent Bookings</h3>
    
    <?php
        // Get recent bookings for organizer's events
        $stmt = $conn->prepare("SELECT b.id, u.name as user_name, e.title as event_title, b.tickets, b.booking_date 
                               FROM bookings b 
                               JOIN users u ON b.user_id = u.id 
                               JOIN events e ON b.event_id = e.id 
                               WHERE e.organizer_id = ? 
                               ORDER BY b.booking_date DESC LIMIT 5");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<table>
                    <tr>
                        <th>User</th>
                        <th>Event</th>
                        <th>Tickets</th>
                        <th>Booking Date</th>
                    </tr>';
            
            while ($booking = $result->fetch_assoc()) {
                echo '<tr>
                        <td>' . htmlspecialchars($booking['user_name']) . '</td>
                        <td>' . htmlspecialchars($booking['event_title']) . '</td>
                        <td>' . $booking['tickets'] . '</td>
                        <td>' . $booking['booking_date'] . '</td>
                      </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No bookings found for your events.</p>';
        }
    ?>
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
