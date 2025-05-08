<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
</head>

<?php 
    require_once("organizer_header.php");

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "Invalid event ID.";
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
        
        if ($currentDate > $event['date']) {
            echo "<p style='color: red;'>Event finished.</p>";
        } elseif ($eventBookings["bookings_number"] >= $event['venue_capacity']) {
            echo "<p style='color: red;'>Event capacity full.</p>";
        } else {
            $remaining = $event['venue_capacity'] - $eventBookings["bookings_number"];
            echo "<p style='color: green;'>$remaining seats remaining out of {$event['venue_capacity']}.</p>";
        }
       
    ?>
    <!-- <button><a href="../bookings/create.php?id=<?php echo $event['id'];?>">Book Now</a></button> -->
    <a class="back-link" href="events.php">← Back to Events</a>
</div>

</body>
</html>


