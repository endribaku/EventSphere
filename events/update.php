<?php

require_once("../php/db.php");
require_once("../organizer/organizer_auth.php");



if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("Invalid event ID.");
}




$event_id = $_GET['id'];
$organizer_id = $_SESSION['user_id'];

if(isset($_POST["submit"])) {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $date = $_POST["date"];
    $venueid = $_POST["venue"];
    $categoryid = $_POST["category"];
    $price = $_POST["price"];
    
    //this check shouldnt be, i need to add validation on update_event for this 
    if(empty($title) || empty($description) || empty($date) || empty($venueid)) {
        die("Please fill all required fields.");
    }

    if ($_POST["venue"] === "new") {
        $newVenueName = trim($_POST['new_venue_name']);
        $newVenueLocation = trim($_POST['new_venue_location']);
        $newVenueCapacity = (int)$_POST['new_venue_capacity'];
        $newVenueCountry = trim($_POST['country']);

        if (empty($newVenueName) || empty($newVenueLocation) || empty($newVenueCountry) || $newVenueCapacity <= 0) {
            $_SESSION['event_error'] = "Please provide all details for the new venue including country.";
            header("Location: ../organizer/update_event.php?id=" . $event_id);
            exit();
        }

        $insertVenueQuery = "INSERT INTO venues (name, location, country, capacity) VALUES (?, ?, ?, ?)";
        $venueStmt = mysqli_prepare($conn, $insertVenueQuery);
        mysqli_stmt_bind_param($venueStmt, "sssi", $newVenueName, $newVenueLocation, $newVenueCountry, $newVenueCapacity);

        if (!mysqli_stmt_execute($venueStmt)) {
            $_SESSION['event_error'] = "Failed to create new venue.";
            header("Location: ../organizer/update_event.php?id=" . $event_id);
            exit();
        }

        $venueid = mysqli_insert_id($conn);
    } else {
        $venueid = (int)$_POST["venue"];
    }

    

    $conflictQuery = "SELECT id FROM events WHERE venue_id = ? AND date = ? AND id != ?";
    $conflictStmt = mysqli_prepare($conn, $conflictQuery);
    mysqli_stmt_bind_param($conflictStmt, "isi", $venueid, $date, $event_id);
    mysqli_stmt_execute($conflictStmt);
    $conflictResult = mysqli_stmt_get_result($conflictStmt);

    if (mysqli_num_rows($conflictResult) > 0) {
        $_SESSION['event_error'] = "❌ Conflict: There's already another event scheduled at this venue on the same date.";
        header("Location: ../organizer/update_event.php?id=" . $event_id);
        exit();
    }

    $imagePath = null;
    $imageUpdateClause = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imagePath = '../images/events/' . $imageName;

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions) || $_FILES['image']['size'] > 5000000) {
            $_SESSION["image_error"] = "Invalid file type or file is too large";
            header("Location: ../organizer/update_event.php?id=". $event_id);
            exit();
        }

        $oldImageQuery = "SELECT image FROM events WHERE id = ? AND organizer_id = ?";
        $oldStmt = mysqli_prepare($conn, $oldImageQuery);
        mysqli_stmt_bind_param($oldStmt, "ii", $event_id, $organizer_id);
        mysqli_stmt_execute($oldStmt);
        mysqli_stmt_bind_result($oldStmt, $oldImagePath);
        mysqli_stmt_fetch($oldStmt);
        mysqli_stmt_close($oldStmt);

        
        if ($oldImagePath && file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }

        move_uploaded_file($imageTmp, $imagePath);
        $imageUpdateClause = ", image = ?";
    }
    
    $query = "UPDATE events SET title = ?, description = ?, price = ?, date = ?, venue_id = ?, category_id = ? $imageUpdateClause WHERE id = ? AND organizer_id = ?";
    $stmt = mysqli_prepare($conn, $query); 

    if ($imagePath) {
        mysqli_stmt_bind_param($stmt, "ssdsiisii", $title, $description, $price ,$date, $venueid, $categoryid, $imagePath, $event_id, $organizer_id);
    } else {
        mysqli_stmt_bind_param($stmt, "ssdsiiii", $title, $description, $price, $date, $venueid, $categoryid, $event_id, $organizer_id);
    }

    $result = mysqli_stmt_execute($stmt);

    if($result) {
        header("Location: ../organizer/events.php?status=updated");
    } else {
        die("Failed to update event.");
    }
}

?>
