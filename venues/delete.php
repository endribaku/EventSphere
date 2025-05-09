<?php 
require_once("../admin/admin_auth.php");
require_once("../php/db.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ../admin/venues.php");
    exit();
}

$venue_id = $_GET["id"];
$venueDeleteQuery = "DELETE FROM venues WHERE id = ?";
$venueStmt = $conn->prepare($venueDeleteQuery);
$venueStmt->bind_param("i", $venue_id);

if($venueStmt ->execute()) {
    header("Location: ../admin/venues.php");
    exit();
} else {
    die("Could not delete successfully");
}


?>