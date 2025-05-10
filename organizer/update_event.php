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

<form action="../events/update.php?id=<?php echo $event['id']; ?>" method="POST" enctype="multipart/form-data">
    <?php if (!empty($event['image'])): ?>
        <div class="form-group">
            <label>Current Image:</label><br>
            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image" style="max-height: 100px;">
        </div>
    <?php endif; ?>
    <label for="title">Event Title</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required><br>

    <label for="description">Description</label>
    <textarea name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea><br>

    <label for="date">Event Date</label>
    <input type="date" name="date" value="<?php echo $event['date']; ?>" required><br>

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

    <label for="image">Event Image (Leave blank to keep current image)</label>
    <input type="file" name="image"><br>

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

    <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($event['price']); ?>" required>

    <button type="submit" name="submit">Update Event</button>
</form>