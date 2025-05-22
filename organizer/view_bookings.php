<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");
    require_once("organizer_header.php");
?>

<div class="bookings-container">
    <?php
    if(!isset($_GET["event_id"]) || !is_numeric($_GET["event_id"])) {
        echo '<div class="alert alert-danger">Invalid event ID.</div>';
        echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
        exit();
    }

    $event_id = $_GET["event_id"];
    
    // First get event details
    $eventQuery = "SELECT e.title, e.date, e.price, v.name AS venue_name 
                  FROM events e 
                  JOIN venues v ON e.venue_id = v.id 
                  WHERE e.id = ? AND e.organizer_id = ?";
    $eventStmt = $conn->prepare($eventQuery);
    $eventStmt->bind_param("ii", $event_id, $_SESSION["user_id"]);
    $eventStmt->execute();
    $eventResult = $eventStmt->get_result();
    
    if($eventResult->num_rows === 0) {
        echo '<div class="alert alert-danger">Event not found or you do not have permission to view these bookings.</div>';
        echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
        exit();
    }
    
    $event = $eventResult->fetch_assoc();
    
    // Get bookings for this event
    $bookingsQuery = "SELECT u.name, u.email, b.tickets, b.booking_date, e.price 
                     FROM users u, bookings b, events e 
                     WHERE u.id = b.user_id AND b.event_id = e.id AND event_id = ?";
    $bookings = $conn->prepare($bookingsQuery);
    $bookings->bind_param("i", $event_id);
    $bookings->execute();
    $bookings = $bookings->get_result();
    
    // Calculate total tickets and revenue
    $totalTickets = 0;
    $totalRevenue = 0;
    $bookingsData = [];
    
    if($bookings->num_rows > 0) {
        while($booking = $bookings->fetch_assoc()) {
            $totalTickets += $booking["tickets"];
            $totalRevenue += $booking["tickets"] * $booking["price"];
            $bookingsData[] = $booking;
        }
    }
    ?>
    
    <div class="event-bookings-header">
        <h2>Bookings for: <?php echo htmlspecialchars($event["title"]); ?></h2>
        <p><strong>Event Date:</strong> <?php echo date('F d, Y', strtotime($event["date"])); ?></p>
        <p><strong>Venue:</strong> <?php echo htmlspecialchars($event["venue_name"]); ?></p>
        <p><strong>Ticket Price:</strong> $<?php echo number_format($event["price"], 2); ?></p>
    </div>
    
    <div class="bookings-summary">
        <div class="summary-card">
            <div class="summary-value"><?php echo $bookings->num_rows; ?></div>
            <div class="summary-label">Total Bookings</div>
        </div>
        <div class="summary-card">
            <div class="summary-value"><?php echo $totalTickets; ?></div>
            <div class="summary-label">Tickets Sold</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">$<?php echo number_format($totalRevenue, 2); ?></div>
            <div class="summary-label">Total Revenue</div>
        </div>
    </div>

    <?php if($bookings->num_rows === 0): ?>
        <div class="no-bookings">
            <i class="fas fa-ticket-alt fa-3x"></i>
            <p>No bookings found for this event.</p>
            <a href="events.php" class="btn btn-primary">Back to Events</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Tickets</th>
                        <th>Total Price</th>
                        <th>Booking Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookingsData as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking["name"]); ?></td>
                            <td><?php echo htmlspecialchars($booking["email"]); ?></td>
                            <td><?php echo htmlspecialchars($booking["tickets"]); ?></td>
                            <td>$<?php echo number_format($booking["tickets"] * $booking["price"], 2); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($booking["booking_date"])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="export-options">
            <button class="btn btn-secondary" onclick="window.print()"><i class="fas fa-print"></i> Print Bookings</button>
            <!-- You could add CSV export functionality here -->
        </div>
        
        <a href="events.php" class="btn btn-primary back-link"><i class="fas fa-arrow-left"></i> Back to Events</a>
    <?php endif; ?>
</div>