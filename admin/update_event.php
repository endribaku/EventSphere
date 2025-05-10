<?php 
require_once("admin_auth.php");
if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: events.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Event</title>
</head>



<?php 
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../events/event_utils.php");

$event_id = $_GET["id"];

$event = getEventById($conn, $event_id);

if(!$event) {
    header("Location: events.php");
    exit();
}
$event = $event->fetch_assoc();

?>


<div class="event-update-form" enctype="multipart/form-data">
    <h2><?php echo $event["title"]; ?></h2>
    <form action="../events/admin_update.php" method="POST">
        
        <?php if (!empty($event['image'])): ?>
        <div class="form-group">
            <label>Current Image:</label><br>
            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" style="max-height: 100px;">
        </div>
        <?php endif; ?>
        
        <input type="hidden" name="id" value="<?php echo $event["id"]; ?>">

        <div class="form-group">
                <label for="name">Update Title: </label>
                <input type="text" name="title" value= "<?php echo htmlspecialchars($event['title']);?>" id="title" class="form-input" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea><br>
        </div>

        <div class="form-group">
            <label for="date">Event Date</label>
            <input type="date" name="date" value="<?php echo $event['date']; ?>" required><br>
        </div>
        <div class="form-group">
            <label for="venue">Venue</label>
            <select name="venue" required>
                <?php
                
                $venuesQuery = "SELECT * FROM venues";
                $venuesResult = mysqli_query($conn, $venuesQuery);
                while ($venue = mysqli_fetch_assoc($venuesResult)) {
                    $selected = ($venue['id'] == $event['venue_id']) ? "selected" : "";
                    echo "<option value='{$venue['id']}' {$selected}>{$venue['name']}</option>";
                }
                ?>
            </select><br>
        </div>
        <div class="form-group">
            <label for="image">Event Image (Leave blank to keep current image)</label>
            <input type="file" name="image"><br>
        </div>
        <div class="form-group">
            <label for="category">Event Category</label>
            <select name="category" required>
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
            <label for="price">Price</label>
            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($event['price']); ?>" required>
        </div>
        <button type="submit" name="submit">Update Event</button>
    </form>
</div>
