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
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="date">Event Date</label>
            <input type="date" name="date" id="date" value="<?php echo $event['date']; ?>" required>
        </div>

        <div class="form-group">
            <label for="venue">Venue</label>
            <select name="venue" id="venue" required>
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

        <div id="new-venue-fields" style="display:none; margin-top: 1em;">
                    <div class="form-group">
                        <label for="new_venue_name">New Venue Name</label>
                        <input type="text" name="new_venue_name" id="new_venue_name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="new_venue_location">Location</label>
                        <input type="text" name="new_venue_location" id="new_venue_location" class="form-input">
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
            <select name="category" id="category" required>
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
            <input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo htmlspecialchars($event['price']); ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit" class="btn btn-primary">Update Event</button>
            <a href="events.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

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


<script>
document.addEventListener('DOMContentLoaded', function () {
    const venueSelect = document.getElementById('venue');
    const newVenueFields = document.getElementById('new-venue-fields');

    if (venueSelect && newVenueFields) {
        venueSelect.addEventListener('change', function () {
            if (this.value === 'new') {
                newVenueFields.style.display = 'block';
            } else {
                newVenueFields.style.display = 'none';
            }
        });
    }
});
</script>
