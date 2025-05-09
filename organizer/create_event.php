<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");

    if (isset($_POST['submit'])) {
       
        $title = $_POST['title'];
        $description = $_POST['description'];
        $date = $_POST['date'];
        $venue_id = $_POST['venue_id'];
        $organizer_id = $_SESSION['user_id']; 
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
    
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageTmp = $_FILES['image']['tmp_name'];
            $imageName = $_FILES['image']['name'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $fileExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            
            $imagePath = '../images/events/' . $imageName;

            if (!is_dir('../images/events')) {
                mkdir('../images/events', 0777, true);
            }

            if (in_array($fileExtension, $allowedExtensions) && $_FILES['image']['size'] < 5000000) {
                copy($imageTmp, $imagePath);
            } else {
                echo "Invalid file type or file is too large.";
                exit();
            }
        } else {
            $imagePath = null; 
        }

        $insertQuery = "INSERT INTO events (organizer_id, title, description, date, venue_id, image, category_id, price)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "isssdsid", $organizer_id, $title, $description, $date, $venue_id, $imagePath, $category_id, $price);
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

        <label for="category">Event Category</label>
        <select name="category_id" id="category_id" required>
            <?php
            $categoryQuery = "SELECT * from event_categories";
            $result = mysqli_query($conn, $categoryQuery);
            while ($category = mysqli_fetch_assoc($result)) {
                echo "<option value='{$category['id']}'>{$category['name']}</option>";
            }
            ?>
        </select>

        <label for="price">Ticket Price ($):</label>
        <input type="number" name="price" step="0.01" min="0" required> 

        <button type="submit" name="submit">Create Event</button>
    </form>

</body>
</html>
