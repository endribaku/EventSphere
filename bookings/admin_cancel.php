<?php 
// admin delete
require_once("../admin/admin_auth.php");

if(!isset($_GET["id"], $_GET["event_id"]) || !is_numeric($_GET["id"]) || !is_numeric($_GET["event_id"])) {
    header("Location: ../user/browse_events.php");
    exit();
}

$booking_id = $_GET["id"];
$event_id = $_GET["event_id"];

require_once("../php/db.php");
$cancelBookingQuery = "DELETE FROM bookings WHERE id = ? AND event_id = ?";
$cancelBookingStmt = $conn->prepare($cancelBookingQuery);
$cancelBookingStmt->bind_param("ii", $booking_id, $event_id);
$cancelBookingStmt->execute();
if($cancelBookingStmt->affected_rows > 0) {
    $_SESSION["success"] = "Cancellation made successfully";
    header("Location: ../admin/bookings.php");
    exit();
} else {
    $_SESSION["success"] = "Cancellation error";
    header("Location: ../admin/bookings.php");
    exit();
}

?>