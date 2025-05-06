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
    $eventQuery = "SELECT e.*, c.name AS category_name, v.name AS venue_name, v.location AS venue_location
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

    <a class="back-link" href="browse_events.php">← Back to Events</a>
</div>

</body>
</html>


