<?php
require_once("../php/db.php");
require_once("../organizer/organizer_auth.php");


if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("Invalid event ID.");
}

$event_id = filter_var($_GET["id"], FILTER_VALIDATE_INT);
if (!$event_id) {
    die("invalid id");
}

$organizer_id = $_SESSION["user_id"];

$findEventQuery = "SELECT *  from events WHERE id = ? AND organizer_id = ?";
$stmt = mysqli_prepare($conn, $findEventQuery);
mysqli_stmt_bind_param($stmt,"ii", $event_id, $organizer_id);
mysqli_stmt_execute($stmt);
$event = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($event);
if(!$event) {
    die("Event not found or the event is not associated with organizer");
}

$imagePath = $event['image'];
if ($imagePath && file_exists($imagePath)) {
    unlink($imagePath); 
}

$deleteEventQuery = "DELETE FROM events WHERE id = ? AND organizer_id = ?";
$stmt = mysqli_prepare($conn, $deleteEventQuery);
mysqli_stmt_bind_param($stmt,"ii", $event_id, $organizer_id);

if(mysqli_stmt_execute($stmt)) {
    header("Location: ../organizer/events.php?status=deleted");
   
} else {
    header("Location: ../organizer/events.php?status=notdeleted");
    
}
exit();
?>