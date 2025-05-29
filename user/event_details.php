<?php
    require_once("user_auth.php");
    include_once("../php/db.php");
    include_once("user_header.php");
?>

<?php
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo '<div class="alert alert-danger">Invalid event ID.</div>';
        echo '<a href="browse_events.php" class="btn btn-primary">Back to Events</a>';
        exit();
    }
    
    $event_id = $_GET["id"];
    $eventQuery = "SELECT e.*, c.name AS category_name, v.name AS venue_name, v.location AS venue_location, v.capacity AS venue_capacity
                    FROM events e, event_categories c, venues v WHERE e.category_id = c.id AND e.venue_id = v.id AND e.id = ?";
    $stmt = $conn->prepare($eventQuery);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result();

    if ($event->num_rows === 0) {
        echo '<div class="alert alert-danger">Event not found.</div>';
        echo '<a href="browse_events.php" class="btn btn-primary">Back to Events</a>';
        exit();
    }
    $event = $event->fetch_assoc();
    
    // Format the date
    $eventDate = new DateTime($event['date']);
    $formattedDate = $eventDate->format('F d, Y');
?>

<div class="event-detail-container">
    <div class="event-detail-header">
        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
        <a href="browse_events.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Events</a>
    </div>
    
    <div class="event-detail-content">
        <div class="event-detail-image">
            <?php if (!empty($event['image'])): ?>
                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
            <?php else: ?>
                <img src="../images/placeholder.svg?height=400&width=600" alt="Event placeholder">
            <?php endif; ?>
        </div>
        
        <div class="event-detail-info">
            <div class="event-detail-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo $formattedDate; ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($event['venue_name']); ?>, <?php echo htmlspecialchars($event['venue_location']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span><?php echo htmlspecialchars($event['category_name']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span><?php echo "$" . number_format($event['price'], 2); ?></span>
                </div>
            </div>
            
            <div class="event-detail-description">
                <h3>About This Event</h3>
                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
            </div>
            
            <div class="event-detail-booking">
                <?php
                    $currentDate = date('Y-m-d');

                    // get capacity of bookings
                    $bookingsQuery = "SELECT COALESCE(SUM(tickets), 0) AS bookings_number FROM bookings where event_id = ?";
                    $stmt = $conn->prepare($bookingsQuery);
                    $stmt->bind_param("i", $event["id"]);
                    $stmt->execute();
                    $eventBookingsCount = $stmt->get_result();
                    $eventBookings = $eventBookingsCount->fetch_assoc();
                    
                    // check for duplicate booking
                    $checkQuery = "SELECT * FROM bookings WHERE user_id = ? AND event_id = ?";
                    $stmt = mysqli_prepare($conn, $checkQuery);
                    mysqli_stmt_bind_param($stmt, "ii", $_SESSION['user_id'], $event["id"]);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $duplicateClause = mysqli_num_rows($result) > 0;
                    
                    // Calculate remaining seats
                    $remainingSeats = $event['venue_capacity'] - $eventBookings["bookings_number"];
                    
                    // Display booking status
                    echo '<div class="booking-status">';
                    if ($currentDate > $event['date']) {
                        echo '<div class="status-badge status-past"><i class="fas fa-times-circle"></i> Event has ended</div>';
                    } elseif ($eventBookings["bookings_number"] >= $event['venue_capacity']) {
                        echo '<div class="status-badge status-full"><i class="fas fa-exclamation-circle"></i> Sold Out</div>';
                    } elseif ($duplicateClause) {
                        echo '<div class="status-badge status-booked"><i class="fas fa-check-circle"></i> You\'ve already booked this event</div>';
                    } else {
                        echo '<div class="status-badge status-available"><i class="fas fa-check-circle"></i> Tickets Available</div>';
                    }
                    echo '</div>';
                    
                    // Display capacity information
                    echo '<div class="capacity-info">';
                    echo '<div class="capacity-bar">';
                    $capacityPercentage = ($eventBookings["bookings_number"] / $event['venue_capacity']) * 100;
                    echo '<div class="capacity-progress" style="width: ' . $capacityPercentage . '%"></div>';
                    echo '</div>';
                    echo '<p>' . $remainingSeats . ' seats remaining out of ' . $event["venue_capacity"] . '</p>';
                    echo '</div>';
                    
                    // Display booking form if applicable
                    if($currentDate <= $event['date'] && $eventBookings["bookings_number"] < $event['venue_capacity'] && !$duplicateClause) {
                        echo '<form action="../bookings/create.php" method="post" class="booking-form">
                            <input type="hidden" name="id" value="' . $event['id'] . '">
                            <div class="form-group">
                                <label for="tickets">Number of Tickets:</label>
                                <div class="ticket-selector">
                                    <button type="button" class="ticket-btn minus" onclick="decrementTickets()">-</button>
                                    <input type="number" id="tickets" name="tickets" min="1" max="' . min(10, $remainingSeats) . '" value="1" required>
                                    <button type="button" class="ticket-btn plus" onclick="incrementTickets()">+</button>
                                </div>
                            </div>
                            <div class="total-price">
                                Total: <span id="totalPrice">$' . number_format($event['price'], 2) . '</span>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Book Now</button>
                        </form>';
                        
                        // JavaScript for ticket selection and price calculation
                        echo '<script>
                            function decrementTickets() {
                                var input = document.getElementById("tickets");
                                if (input.value > 1) {
                                    input.value = parseInt(input.value) - 1;
                                    updateTotalPrice();
                                }
                            }
                            
                            function incrementTickets() {
                                var input = document.getElementById("tickets");
                                var max = parseInt(input.getAttribute("max"));
                                if (parseInt(input.value) < max) {
                                    input.value = parseInt(input.value) + 1;
                                    updateTotalPrice();
                                }
                            }
                            
                            function updateTotalPrice() {
                                var tickets = document.getElementById("tickets").value;
                                var price = ' . $event['price'] . ';
                                var total = tickets * price;
                                document.getElementById("totalPrice").textContent = "$" + total.toFixed(2);
                            }
                            
                            document.getElementById("tickets").addEventListener("change", updateTotalPrice);
                        </script>';
                    }
                ?>
            </div>
        </div>
    </div>
</div>

</div> <!-- Close dashboard-container -->
</body>

<?php include_once("../footer.php");?>

</html>

