<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
</head>

<?php
    $event_name = $_POST["event_name"];
    $event_description = $_POST["event_description"];
    $event_date = $_POST["event_date"];
    $event_location = $_POST["event_location"];

    $organizer_id = $_SESSION["user_id"];

    $query = "INSERT INTO events (organizer_id, event_name, event_description, event_date, event_location"

?>



<?php
    require_once("organizer_header.php");
?>

<h1>Create New Event</h1>

    <form action="create_event.php" method="POST" enctype="multipart/form-data">
        <label for="title">Event Title</label>
        <input type="text" name="title" required><br>

        <label for="description">Description</label>
        <textarea name="description" required></textarea><br>

        <label for="date">Event Date</label>
        <input type="date" name="date" required><br>

        <label for="venue">Venue</label>
        <select name="venue_id" required>
            
            <?php
            $venuesQuery = "SELECT * FROM venues";
            $result = mysqli_query($conn, $venuesQuery);
            while ($venue = mysqli_fetch_assoc($result)) {
                echo "<option value='{$venue['venue_id']}'>{$venue['venue_name']}</option>";
            }
            ?>
        </select><br>

        <label for="image">Event Image</label>
        <input type="file" name="image"><br>

        <button type="submit" name="submit">Create Event</button>
    </form>

</body>
</html>