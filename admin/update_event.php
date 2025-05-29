<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../events/event_utils.php");

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo '<div class="alert alert-danger">Invalid event ID.</div>';
    echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
    exit();
}

if (isset($_SESSION["event_error"])) {
    echo '<div class="alert alert-danger">' . $_SESSION["event_error"] . '</div>';
    unset($_SESSION["event_error"]);
}

if (isset($_SESSION["image_error"])) {
    echo '<div class="alert alert-danger">' . $_SESSION["image_error"] . '</div>';
    unset($_SESSION["image_error"]);
}

$event_id = $_GET["id"];
$event = getEventById($conn, $event_id);

if (!$event || $event->num_rows === 0) {
    echo '<div class="alert alert-danger">Event not found.</div>';
    echo '<a href="events.php" class="btn btn-primary">Back to Events</a>';
    exit();
}

$event = $event->fetch_assoc();

$bookingCheckQuery = "SELECT COUNT(*) AS sold_tickets FROM bookings WHERE event_id = ?";
$stmt = mysqli_prepare($conn, $bookingCheckQuery);
mysqli_stmt_bind_param($stmt, 'i', $event_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$bookingData = mysqli_fetch_assoc($result);
$ticketsSold = $bookingData['sold_tickets'];
?>

<div class="card">
    <div class="card-header">
        <h2>Update Event: <?= htmlspecialchars($event["title"]) ?></h2>
        <a href="events.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Events</a>
    </div>
    <div class="card-body">
        <form action="../events/admin_update.php" method="POST" enctype="multipart/form-data" class="form">
            <input type="hidden" name="id" value="<?= $event["id"] ?>">

            <?php if (!empty($event['image'])): ?>
                <div class="form-group">
                    <label>Current Image</label>
                    <div class="current-image">
                        <img src="<?= htmlspecialchars($event['image']) ?>" alt="Event Image">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="title">Event Title</label>
                <input type="text" id="title" name="title" value="<?= htmlspecialchars($event['title']) ?>" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-textarea" rows="5" required><?= htmlspecialchars($event['description']) ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="date">Event Date</label>
                    <?php if ($ticketsSold > 0): ?>
                        <input type="hidden" name="date" value="<?= $event['date'] ?>">
                        <input type="date" value="<?= $event['date'] ?>" class="form-input" disabled>
                    <?php else: ?>
                        <input type="date" id="date" name="date" value="<?= $event['date'] ?>" class="form-input" required min="<?= date('Y-m-d') ?>">
                    <?php endif; ?>
                </div>

                <div class="form-group form-group-half">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($event['price']) ?>" class="form-input" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group form-group-half">
                    <label for="venue">Venue</label>

                    <?php
                    $venuesQuery = "SELECT * FROM venues ORDER BY name ASC";
                    $venuesResult = mysqli_query($conn, $venuesQuery);
                    ?>

                    <?php if ($ticketsSold > 0): ?>
                        <select class="form-select" disabled>
                            <?php while ($venue = mysqli_fetch_assoc($venuesResult)): ?>
                                <option value="<?= $venue['id'] ?>" <?= $venue['id'] == $event['venue_id'] ? "selected" : "" ?>>
                                    <?= $venue['name'] ?> (<?= $venue['location'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <input type="hidden" name="venue" value="<?= $event['venue_id'] ?>">
                    <?php else: ?>
                        <?php mysqli_data_seek($venuesResult, 0); ?>
                        <select id="venue" name="venue" class="form-select" required>
                            <?php while ($venue = mysqli_fetch_assoc($venuesResult)): ?>
                                <option value="<?= $venue['id'] ?>" <?= $venue['id'] == $event['venue_id'] ? "selected" : "" ?>>
                                    <?= $venue['name'] ?> (<?= $venue['location'] ?>)
                                </option>
                            <?php endwhile; ?>
                            <option value="new">+ Create New Venue</option>
                        </select>
                    <?php endif; ?>
                </div>

                <?php if ($ticketsSold == 0): ?>
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
                                foreach ($countries as $country): ?>
                                    <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                                <?php endforeach; ?>
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
                <?php endif; ?>

                <div class="form-group form-group-half">
                    <label for="category">Event Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <?php
                        $categoryQuery = "SELECT * FROM event_categories ORDER BY name ASC";
                        $categoryResult = mysqli_query($conn, $categoryQuery);
                        while ($category = mysqli_fetch_assoc($categoryResult)): ?>
                            <option value="<?= $category['id'] ?>" <?= $category["id"] == $event["category_id"] ? "selected" : "" ?>>
                                <?= $category['name'] ?>
                            </option>
                        <?php endwhile; ?>
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
 <!-- Close dashboard-container -->
</body>
<?php include_once("../footer.php");?>

</html>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const venueSelect = document.getElementById('venue');
    const newVenueFields = document.getElementById('new-venue-fields');

    if (!venueSelect || venueSelect.disabled) return;

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
    toggleNewVenueFields(); // Initial state
});
</script>
