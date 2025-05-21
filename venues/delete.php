<?php 
require_once("../admin/admin_auth.php");
require_once("../php/db.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ../admin/venues.php");
    exit();
}

$venue_id = $_GET["id"];

//count events
$countVenuesQuery = "SELECT COUNT(*) as count FROM events where venue_id = ?";
$stmt = $conn->prepare($countVenuesQuery);
$stmt->bind_param("i", $venue_id);
$stmt->execute();
$eventCount = $stmt->get_result();
$count = $eventCount->fetch_assoc()["count"];

if($count > 0) {
    $_SESSION["count_error"] = "This venue cannot be deleted because it still has events assigned to it. Please delete or reassign those events first.";
    header("Location: ../admin/venues.php");
    exit();
}


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