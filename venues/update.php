<?php 
require_once("../admin/admin_auth.php");
require_once("../php/db.php");

if(!isset($_POST["id"])) {
    header("Location: ../admin/venues.php");
    exit();
}

if(isset($_POST["submit"])) {
    $venue_id = $_POST["id"];
    $venue_name = $_POST["name"];
    $venue_location = $_POST["location"];
    $venue_country = $_POST["country"];
    $venue_capacity = $_POST["capacity"];

    $updateVenueQuery = "UPDATE venues SET name = ?, location = ?, country = ?, capacity = ? WHERE id = ?";
    $updateVenueStmt = $conn->prepare($updateVenueQuery);
    $updateVenueStmt->bind_param("sssii", $venue_name, $venue_location, $venue_country, $venue_capacity, $venue_id);
    
    if($updateVenueStmt->execute() ) {
        header("Location: ../admin/venues.php?status=updated");
        exit();
    } else {
        die("Update couldn't be made");
    }
} else {
    die("Submit hasn't been made yet");
}



?>
