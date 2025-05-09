<?php 
require_once("../admin/admin_auth.php");
require_once("../php/db.php");


if(isset($_POST["submit"])) {
    $venue_name = $_POST["name"];
    $venue_location = $_POST["location"];
    $venue_capacity = $_POST["capacity"];
    $venue_country = $_POST["country"];

    $userQuery = "INSERT INTO venues (name, location, country, capacity) VALUES (?, ?, ?, ?)";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("sssi", $venue_name, $venue_location, $venue_country, $venue_capacity);
    
    if($userStmt->execute()) {
        header("Location: ../admin/venues.php");
        exit();
    } else {
        die("Event couldn't be inserted successfully");
    }
} else {
    die("Deletion isn't submitted yet");
}


?>