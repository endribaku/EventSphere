<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");

    if (isset($_POST['submit'])) {
       
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $venue_id = $_POST['venue_id'];
        $organizer_id = $_SESSION['user_id']; 
    
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageTmp = $_FILES['image']['tmp_name'];
            $imageName = $_FILES['image']['name'];
            $imagePath = 'uploads/' . $imageName;
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            
            if (in_array($fileExtension, $allowedExtensions) && $_FILES['image']['size'] < 5000000) {
                move_uploaded_file($imageTmp, $imagePath);
            } else {
                echo "Invalid file type or file is too large.";
                exit();
            }
        } else {
            $imagePath = null; 
        }

        $insertQuery = "INSERT INTO events (organizer_id, title, description, date, venue_id, image)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "isssds", $organizer_id, $title, $description, $date, $venue_id, $imagePath);
        $result = mysqli_stmt_execute($stmt);

        if($result) {
            echo "Event Created Successfully!";
        } else {
            echo "Error creating event";
        }
    }

    
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
                echo "<option value='{$venue['id']}'>{$venue['name']}</option>";
            }
            ?>
        </select><br>

        <label for="image">Event Image</label>
        <input type="file" name="image"><br>

        <button type="submit" name="submit">Create Event</button>
    </form>

</body>
</html>
