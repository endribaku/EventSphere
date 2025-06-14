<?php
    require_once("../organizer/organizer_auth.php");
    include_once("../organizer/organizer_header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Event</title>
</head>

<?php

if(isset($_SESSION['event_error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['event_error'].'</div>';
    unset($_SESSION['event_error']);
}
if(isset($_SESSION["image_error"])) {
    echo '<div class="alert alert-danger">'.$_SESSION["image_error"].'</div>';
    unset($_SESSION["image_error"]);
}


$event_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if(!$event_id) {
    die("Invalid event ID.");
}

require_once("../php/db.php");

$organizer_id = $_SESSION["user_id"];


$query = "SELECT * from events WHERE id = ? AND organizer_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt,"ii", $event_id, $organizer_id);
mysqli_stmt_execute($stmt);
$event = mysqli_stmt_get_result($stmt);

if(!$event) {
    die("Event not found or you dont have permission for this event");
}

$event = mysqli_fetch_assoc($event);
$eventTitle = $event["title"];
$eventDesc = $event["description"];
$eventTime = $event["date"];

// checking if tickets are sold so we shoudlnt edit dates and venues
$bookingCheckQuery = "SELECT COUNT(*) AS sold_tickets FROM bookings WHERE event_id = ?";
$stmt = mysqli_prepare($conn, $bookingCheckQuery);
mysqli_stmt_bind_param($stmt, 'i', $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bookingData = mysqli_fetch_assoc($result);
$ticketsSold = $bookingData['sold_tickets'];

?>

<div class="event-update-form">
    <h2>Update Event: <?php echo htmlspecialchars($eventTitle); ?></h2>
    
    <form action="../events/update.php?id=<?php echo $event['id']; ?>" method="POST" enctype="multipart/form-data">
        <?php if (!empty($event['image'])): ?>
            <div class="form-group">
                <label>Current Image:</label><br>
                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" class="event-preview-image">
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="title">Event Title</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($event['title']); ?>" class="form-input" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="5" required class="form-input"><?php echo htmlspecialchars($event['description']); ?></textarea>
        </div>

        <div class="form-group">
            <?php if ($ticketsSold > 0): ?>
            <input type="hidden" name="date" value="<?= $event['date']; ?>">
            <input type="date" value="<?= $event['date']; ?>" class="form-input" disabled>
        <?php else: ?>
            <input type="date" name="date" id="date" value="<?= $event['date']; ?>" class="form-input" required min="<?= date('Y-m-d') ?>">
        <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="venue">Venue</label>
            <?php if ($ticketsSold > 0): ?>
                <input type="hidden" name="venue" value="<?= $event['venue_id']; ?>">
                <select class="form-input" disabled>
            <?php else: ?>  
            <select name="venue" id="venue" class="form-input" required <?php echo ($ticketsSold > 0) ? 'disabled' : ''; ?>>
            <?php endif; ?>
                <?php
                $venuesQuery = "SELECT * FROM venues";
                $venuesResult = mysqli_query($conn, $venuesQuery);
                while ($venue = mysqli_fetch_assoc($venuesResult)) {
                    $selected = ($venue['id'] == $event['venue_id']) ? "selected" : "";
                    echo "<option value='{$venue['id']}' {$selected}>{$venue['name']}</option>";
                }
                ?>
                 <option value="new">+ Create New Venue</option>
            </select>
        </div>

        <div id="new-venue-fields" style="display:none; margin-top: 1em;" >
                    <div class="form-group">
                        <label for="new_venue_name">New Venue Name</label>
                        <input type="text" name="new_venue_name" id="new_venue_name" class="form-input">
                    </div> 
                    <div class="form-group">
                        <label for="new_venue_location">Location</label>
                        <input type="text" name="new_venue_location" id="new_venue_location" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country" class="form-select">
                            <option value="">Select a country</option>
                            <?php 
                            require_once("../misc/countries.list.php");
                            foreach($countries as $country) {
                                echo '<option value="' . htmlspecialchars($country) . '">' . htmlspecialchars($country) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new_venue_capacity">Capacity</label>
                        <input type="number" name="new_venue_capacity" id="new_venue_capacity" class="form-input" min="1">
                    </div>
         </div>

        <div class="form-group">
            <label for="image">Event Image (Leave blank to keep current image)</label>
            <input type="file" name="image" id="image">
            <small>Recommended size: 800x400 pixels. Max file size: 5MB.</small>
        </div>

        <div class="form-group">
            <label for="category">Event Category</label>
            <select name="category" id="category" required class="form-input">
                <?php
                $categoryQuery = "SELECT * FROM event_categories";
                $categoryResult = mysqli_query($conn, $categoryQuery);
                while ($category = mysqli_fetch_assoc($categoryResult)) {
                    $selected = ($category["id"] == $event["category_id"]) ? "selected" : "";
                    echo "<option value='{$category['id']}' {$selected}>{$category['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="price">Ticket Price ($)</label>
            <input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo htmlspecialchars($event['price']); ?>" class="form-input" required>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit" class="btn btn-primary" >Update Event</button>
            <a href="events.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>


<?php include_once("../footer.php");?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const venueSelect = document.getElementById('venue');
    const newVenueFields = document.getElementById('new-venue-fields');
    if (venueSelect && newVenueFields) {
        venueSelect.addEventListener('change', function () {
            newVenueFields.style.display = this.value === 'new' ? 'block' : 'none';
        });
    }
});
</script>
</body>
</html>
