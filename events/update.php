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

    $imagePath = null;
    $imageUpdateClause = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imagePath = '../images/events/' . $imageName;

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions) || $_FILES['image']['size'] > 5000000) {
            die("Invalid file type or file is too large.");
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
        header("Location: ../organizer/events.php");
    } else {
        die("Failed to update event.");
    }
}

?>
