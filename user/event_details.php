<?php
    require_once("user_auth.php");
    include_once("../php/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>

</head>

<?php
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "Invalid event ID.";
        exit;
    }
    include_once("user_header.php");
    $event_id = $_GET["id"];
    $eventQuery = "SELECT e.*, c.name AS category_name, v.name AS venue_name, v.location AS venue_location, v.capacity AS venue_capacity
                    FROM events e, event_categories c, venues v WHERE e.category_id = c.id AND e.venue_id = v.id AND e.id = ?";
    $stmt = $conn->prepare($eventQuery);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result();

    if ($event->num_rows === 0) {
        echo "Event not found.";
        exit;
    }
    $event = $event->fetch_assoc();
?>

<div class="event-detail-container">
    <?php if (!empty($event['image'])): ?>
        <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image">
    <?php endif; ?>

    <h1><?php echo htmlspecialchars($event['title']); ?></h1>
    <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category_name']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
    <p><strong>Venue:</strong> <?php echo htmlspecialchars($event['venue_name']); ?></p>
    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['venue_location']); ?></p>
    <p><strong>Description:</strong></p>
    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
    
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

        $duplicateClause = false;

        if (mysqli_num_rows($result) > 0) {
            $duplicateClause = true;
        } else {
            $duplicateClause = false;
        }
        

        if($currentDate <= $event['date'] && $eventBookings["bookings_number"] < $event['venue_capacity'] && !$duplicateClause){
            echo '<form action="../bookings/create.php" method="post">
            <input type="hidden" name="id" value="'.$event['id'].'">
            <label for="tickets">Number of Tickets:</label>
            <input type="number" id="tickets" name="tickets" min="1" max="10" required>
            <button type="submit">Book Now</button>
             </form>';
            
        } 
        echo $eventBookings["bookings_number"].' out of '.$event["venue_capacity"]." remaining.";
        
        if($currentDate > $event['date']) {
            echo "Event finished.";
        }
        if($eventBookings["bookings_number"] >= $event['venue_capacity']) {
            echo "Event capacity full.";
        }
        if($duplicateClause) {
            echo "You already booked this event";
        }
    ?>
    <!-- <button><a href="../bookings/create.php?id=<?php echo $event['id'];?>">Book Now</a></button> -->
    <a class="back-link" href="browse_events.php">← Back to Events</a>
</div>

</body>
</html>


