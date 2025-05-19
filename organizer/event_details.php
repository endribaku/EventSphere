<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");
    require_once("organizer_header.php");

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo '<div class="alert alert-danger">Invalid event ID.</div>';
        echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
        exit();
    }
    
    $event_id = $_GET["id"];
    $organizer_id = $_SESSION["user_id"];
    
    $eventQuery = "SELECT e.*, c.name AS category_name, v.name AS venue_name, v.location AS venue_location, v.capacity AS venue_capacity
                    FROM events e
                    JOIN event_categories c ON e.category_id = c.id 
                    JOIN venues v ON e.venue_id = v.id 
                    WHERE e.id = ? AND e.organizer_id = ?";
    $stmt = $conn->prepare($eventQuery);
    $stmt->bind_param("ii", $event_id, $organizer_id);
    $stmt->execute();
    $event = $stmt->get_result();

    if ($event->num_rows === 0) {
        echo '<div class="alert alert-danger">Event not found or you don\'t have permission to view it.</div>';
        echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
        exit();
    }
    
    $event = $event->fetch_assoc();
    
    // Format the date
    $eventDate = new DateTime($event['date']);
    $formattedDate = $eventDate->format('F d, Y');
    
    // Get bookings information
    $bookingsQuery = "SELECT COALESCE(SUM(tickets), 0) AS bookings_number FROM bookings WHERE event_id = ?";
    $stmt = $conn->prepare($bookingsQuery);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $eventBookingsCount = $stmt->get_result();
    $eventBookings = $eventBookingsCount->fetch_assoc();
    
    // Calculate remaining seats and percentage
    $remainingSeats = $event['venue_capacity'] - $eventBookings["bookings_number"];
    $capacityPercentage = ($eventBookings["bookings_number"] / $event['venue_capacity']) * 100;
    
    // Calculate revenue
    $revenue = $eventBookings["bookings_number"] * $event['price'];
    
    // Determine event status
    $currentDate = date('Y-m-d');
    $status = "";
    $statusClass = "";
    if ($currentDate > $event['date']) {
        $status = "Past";
        $statusClass = "status-past";
    } elseif ($currentDate == $event['date']) {
        $status = "Today";
        $statusClass = "status-today";
    } else {
        $status = "Upcoming";
        $statusClass = "status-upcoming";
    }
?>

<div class="event-detail-container organizer-view">
    <div class="event-detail-header">
        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
        <div class="event-actions">
            <a href="update_event.php?id=<?php echo $event_id; ?>" class="btn btn-secondary"><i class="fas fa-edit"></i> Edit Event</a>
            <a href="../events/delete.php?id=<?php echo $event_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?')"><i class="fas fa-trash"></i> Delete Event</a>
            <a href="view_bookings.php?event_id=<?php echo $event_id; ?>" class="btn btn-primary"><i class="fas fa-ticket-alt"></i> View Bookings</a>
            <a href="events.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Events</a>
        </div>
    </div>
    
    <div class="event-detail-content">
        <div class="event-detail-main">
            <div class="event-detail-image">
                <?php if (!empty($event['image'])): ?>
                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-image fa-4x"></i>
                        <p>No image available</p>
                    </div>
                <?php endif; ?>
                <div class="event-status <?php echo $statusClass; ?>"><?php echo $status; ?></div>
            </div>
            
            <div class="event-detail-info">
                <div class="info-section">
                    <h3>Event Details</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-calendar-alt"></i> Date</span>
                            <span class="info-value"><?php echo $formattedDate; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-tag"></i> Category</span>
                            <span class="info-value"><?php echo htmlspecialchars($event['category_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-map-marker-alt"></i> Venue</span>
                            <span class="info-value"><?php echo htmlspecialchars($event['venue_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-location-arrow"></i> Location</span>
                            <span class="info-value"><?php echo htmlspecialchars($event['venue_location']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-dollar-sign"></i> Price</span>
                            <span class="info-value">$<?php echo number_format($event['price'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label"><i class="fas fa-users"></i> Capacity</span>
                            <span class="info-value"><?php echo $event['venue_capacity']; ?> seats</span>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Booking Status</h3>
                    <div class="capacity-info">
                        <div class="capacity-stats">
                            <div class="stat">
                                <span class="stat-value"><?php echo $eventBookings["bookings_number"]; ?></span>
                                <span class="stat-label">Tickets Sold</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value"><?php echo $remainingSeats; ?></span>
                                <span class="stat-label">Seats Remaining</span>
                            </div>
                            <div class="stat">
                                <span class="stat-value">$<?php echo number_format($revenue, 2); ?></span>
                                <span class="stat-label">Revenue</span>
                            </div>
                        </div>
                        
                        <div class="capacity-bar-container">
                            <div class="capacity-label">
                                <span>Capacity</span>
                                <span><?php echo round($capacityPercentage); ?>%</span>
                            </div>
                            <div class="capacity-bar">
                                <div class="capacity-progress" style="width: <?php echo $capacityPercentage; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>Description</h3>
                    <div class="event-description">
                        <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="event-detail-sidebar">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Bookings</h3>
                </div>
                <div class="card-body">
                    <?php
                    // Get recent bookings for this event
                    $bookingsQuery = "SELECT b.id, u.name, b.tickets, b.booking_date 
                                     FROM bookings b 
                                     JOIN users u ON b.user_id = u.id 
                                     WHERE b.event_id = ? 
                                     ORDER BY b.booking_date DESC LIMIT 5";
                    $stmt = $conn->prepare($bookingsQuery);
                    $stmt->bind_param("i", $event_id);
                    $stmt->execute();
                    $bookings = $stmt->get_result();
                    
                    if ($bookings->num_rows > 0) {
                        echo '<ul class="booking-list">';
                        while ($booking = $bookings->fetch_assoc()) {
                            $bookingDate = new DateTime($booking['booking_date']);
                            echo '<li class="booking-item">';
                            echo '<div class="booking-user"><i class="fas fa-user"></i> ' . htmlspecialchars($booking['name']) . '</div>';
                            echo '<div class="booking-details">';
                            echo '<span class="booking-tickets"><i class="fas fa-ticket-alt"></i> ' . $booking['tickets'] . ' ticket' . ($booking['tickets'] > 1 ? 's' : '') . '</span>';
                            echo '<span class="booking-date"><i class="fas fa-clock"></i> ' . $bookingDate->format('M d, Y H:i') . '</span>';
                            echo '</div>';
                            echo '</li>';
                        }
                        echo '</ul>';
                        echo '<a href="view_bookings.php?event_id=' . $event_id . '" class="btn btn-sm btn-secondary btn-block">View All Bookings</a>';
                    } else {
                        echo '<p class="no-data">No bookings for this event yet.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3>Quick Stats</h3>
                </div>
                <div class="card-body">
                    <div class="quick-stats">
                        <div class="stat-item">
                            <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo round($capacityPercentage); ?>%</span>
                                <span class="stat-label">Sold</span>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                            <div class="stat-info">
                                <span class="stat-value">$<?php echo number_format($revenue, 2); ?></span>
                                <span class="stat-label">Revenue</span>
                            </div>
                        </div>
                        <?php
                        // Calculate days until event
                        $now = new DateTime();
                        $interval = $now->diff(new DateTime($event['date']));
                        $daysLeft = $interval->days;
                        if ($now > new DateTime($event['date'])) {
                            $daysLeft = 0;
                        }
                        ?>
                        <div class="stat-item">
                            <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                            <div class="stat-info">
                                <span class="stat-value"><?php echo $daysLeft; ?></span>
                                <span class="stat-label"><?php echo $daysLeft == 1 ? 'Day Left' : 'Days Left'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <!-- Close dashboard-container -->
</body>
</html>





