<<?php
require_once("../php/db.php");
require_once("../admin/admin_auth.php");
require_once("event_utils.php");

if (!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
    die("Invalid event ID.");
}

$event_id = (int)$_POST['id'];

if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $date = $_POST['date'];
    $categoryid = (int)$_POST['category'];
    $price = (float)$_POST['price'];
    $venueid = $_POST['venue'];

    // Handle NEW venue creation
    if ($venueid === "new") {
        $newName = trim($_POST['new_venue_name']);
        $country = trim($_POST['country']);
        $location = trim($_POST['new_venue_location']);
        $capacity = (int)$_POST['new_venue_capacity'];

        if (empty($newName) || empty($country) || empty($location) || $capacity <= 0) {
            $_SESSION['event_error'] = "❌ All new venue fields are required.";
            header("Location: ../admin/update_event.php?id=$event_id");
            exit();
        }

        $insertVenue = "INSERT INTO venues (name, country, location, capacity) VALUES (?, ?, ?, ?)";
        $venueStmt = mysqli_prepare($conn, $insertVenue);
        mysqli_stmt_bind_param($venueStmt, "sssi", $newName, $country, $location, $capacity);
        mysqli_stmt_execute($venueStmt);
        $venueid = mysqli_insert_id($conn); // Use new venue ID
    } else {
        $venueid = (int)$venueid;
    }

    // Conflict check
    $conflictQuery = "SELECT id FROM events WHERE venue_id = ? AND date = ? AND id != ?";
    $conflictStmt = mysqli_prepare($conn, $conflictQuery);
    mysqli_stmt_bind_param($conflictStmt, "isi", $venueid, $date, $event_id);
    mysqli_stmt_execute($conflictStmt);
    $conflictResult = mysqli_stmt_get_result($conflictStmt);

    if (mysqli_num_rows($conflictResult) > 0) {
        $_SESSION['event_error'] = "❌ Conflict: Another event is already scheduled at this venue on that date.";
        header("Location: ../admin/update_event.php?id=$event_id");
        exit();
    }

    // Image processing
    $imagePath = null;
    $imageUpdateClause = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imagePath = '../images/events/' . $imageName;

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions) || $_FILES['image']['size'] > 5 * 1024 * 1024) {
            $_SESSION['image_error'] = "❌ Invalid image file (type or size).";
            header("Location: ../admin/update_event.php?id=$event_id");
            exit();
        }

        // Delete old image
        $oldQuery = "SELECT image FROM events WHERE id = ?";
        $oldStmt = mysqli_prepare($conn, $oldQuery);
        mysqli_stmt_bind_param($oldStmt, "i", $event_id);
        mysqli_stmt_execute($oldStmt);
        mysqli_stmt_bind_result($oldStmt, $oldImage);
        mysqli_stmt_fetch($oldStmt);
        mysqli_stmt_close($oldStmt);

        if ($oldImage && file_exists($oldImage)) {
            unlink($oldImage);
        }

        move_uploaded_file($imageTmp, $imagePath);
        $imageUpdateClause = ", image = ?";
    }

    // Final UPDATE
    $query = "UPDATE events SET title = ?, description = ?, price = ?, date = ?, venue_id = ?, category_id = ? $imageUpdateClause WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($imagePath) {
        mysqli_stmt_bind_param($stmt, "ssdsissi", $title, $description, $price, $date, $venueid, $categoryid, $imagePath, $event_id);
    } else {
        mysqli_stmt_bind_param($stmt, "ssdsiii", $title, $description, $price, $date, $venueid, $categoryid, $event_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../admin/events.php?status=updated");
        exit();
    } else {
        die("❌ Failed to update event. MySQL Error: " . mysqli_stmt_error($stmt));
    }
}
?>
