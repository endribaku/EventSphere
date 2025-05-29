<?php
require_once("../php/db.php");
require_once("../admin/admin_auth.php"); 
require_once("event_utils.php");

if (!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
    die("Invalid event ID.");
}

$event_id = (int)$_POST['id'];

if(isset($_POST["submit"])) {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $date = $_POST["date"];
    $venueid = $_POST["venue"];
    $categoryid = $_POST["category"];
    $price = $_POST["price"];

    

    $conflictQuery = "SELECT id FROM events WHERE venue_id = ? AND date = ? AND id != ?";
    $conflictStmt = mysqli_prepare($conn, $conflictQuery);
    mysqli_stmt_bind_param($conflictStmt, "isi", $venueid, $date, $event_id);
    mysqli_stmt_execute($conflictStmt);
    $conflictResult = mysqli_stmt_get_result($conflictStmt);

    if (mysqli_num_rows($conflictResult) > 0) {
        $_SESSION['event_error'] = "❌ Conflict: There's already another event scheduled at this venue on the same date.";
        header("Location: ../admin/update_event.php?id=" . $event_id);
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
            header("Location: ../admin/update_event.php?id=". $event_id);
            exit();
        }

        $oldImageQuery = "SELECT image FROM events WHERE id = ?";
        $oldStmt = mysqli_prepare($conn, $oldImageQuery);
        mysqli_stmt_bind_param($oldStmt, "i", $event_id);
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
    
    $query = "UPDATE events SET title = ?, description = ?, price = ?, date = ?, venue_id = ?, category_id = ? $imageUpdateClause WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($imagePath) {
        mysqli_stmt_bind_param($stmt, "ssdsiisi", $title, $description, $price ,$date, $venueid, $categoryid, $imagePath, $event_id);
    } else {
        mysqli_stmt_bind_param($stmt, "ssdsiii", $title, $description, $price, $date, $venueid, $categoryid, $event_id);
    }

    $result = mysqli_stmt_execute($stmt);

    if($result) {
        header("Location: ../admin/events.php?status=updated");
    } else {
        die("Failed to update event.");
    }
}

?>