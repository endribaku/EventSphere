<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../events/event_utils.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo '<div class="alert alert-danger">Invalid event ID.</div>';
    echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
    exit();
}

if(isset($_SESSION["event_error"])) {
    echo '<div class="alert alert-danger">'.$_SESSION["event_error"].'</div>';
    unset($_SESSION["event_error"]);
}

if(isset($_SESSION["image_error"])) {
    echo '<div class="alert alert-danger">'.$_SESSION["image_error"].'</div>';
    unset($_SESSION["image_error"]);
}

$event_id = $_GET["id"];
$event = getEventById($conn, $event_id);

if(!$event || $event->num_rows === 0) {
    echo '<div class="alert alert-danger">Event not found.</div>';
    echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
    exit();
}

$event = $event->fetch_assoc();
?>

<div class="card">
    <div class="card-header">
        <h2>Update Event: <?php echo htmlspecialchars($event["title"]); ?></h2>
        <a href="events.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Events</a>
    </div>
    <div class="card-body">
        <form action="../events/admin_update.php" method="POST" enctype="multipart/form-data" class="form">
            <input type="hidden" name="id" value="<?php echo $event["id"]; ?>">
            
            <?php if (!empty($event['image'])): ?>
            <div class="form-group">
                <label>Current Image</label>
                <div class="current-image">
                    <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image">
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="date">Event Date</label>
                    <input type="date" id="date" name="date" value="<?php echo $event['date']; ?>" class="form-input" required>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($event['price']); ?>" class="form-input" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="venue">Venue</label>
                    <select id="venue" name="venue" class="form-select" required>
                        <?php
                        $venuesQuery = "SELECT * FROM venues ORDER BY name ASC";
                        $venuesResult = mysqli_query($conn, $venuesQuery);
                        while ($venue = mysqli_fetch_assoc($venuesResult)) {
                            $selected = ($venue['id'] == $event['venue_id']) ? "selected" : "";
                            echo "<option value='{$venue['id']}' {$selected}>{$venue['name']} ({$venue['location']})</option>";
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
                        <label for="new_venue_location">Location</label>
                        <input type="text" name="new_venue_location" id="new_venue_location" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="new_venue_capacity">Capacity</label>
                        <input type="number" name="new_venue_capacity" id="new_venue_capacity" class="form-input" min="1">
                    </div>
                </div>
                
                <div class="form-group form-group-half">
                    <label for="category">Event Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <?php
                        $categoryQuery = "SELECT * FROM event_categories ORDER BY name ASC";
                        $categoryResult = mysqli_query($conn, $categoryQuery);
                        while ($category = mysqli_fetch_assoc($categoryResult)) {
                            $selected = ($category["id"] == $event["category_id"]) ? "selected" : "";
                            echo "<option value='{$category['id']}' {$selected}>{$category['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Event Image (Leave blank to keep current image)</label>
                <input type="file" id="image" name="image" class="form-input">
                <small class="form-text">Recommended size: 1200 x 600 pixels. Max file size: 5MB.</small>
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">Update Event</button>
                <a href="events.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const venueSelect = document.getElementById('venue');
    const newVenueFields = document.getElementById('new-venue-fields');
    const newVenueInputs = newVenueFields.querySelectorAll('input, select');

    function toggleNewVenueFields() {
        if (venueSelect.value === 'new') {
            newVenueFields.style.display = 'block';
            newVenueInputs.forEach(input => input.setAttribute('required', 'required'));
        } else {
            newVenueFields.style.display = 'none';
            newVenueInputs.forEach(input => input.removeAttribute('required'));
        }
    }

    venueSelect.addEventListener('change', toggleNewVenueFields);
    toggleNewVenueFields(); // Call once on page load in case of prefill
});
</script>
