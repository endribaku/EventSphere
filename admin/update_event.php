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
    unset($_SESSION["event_Error"]);
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
                    </select>
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
</html>
