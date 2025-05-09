<?php
require_once("../php/db.php");
require_once("../admin/admin_auth.php"); // your admin session auth check
require_once("event_utils.php");

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("Invalid event ID.");
}

$event_id = (int)$_GET['id'];

if (deleteEventById($conn, $event_id)) {
    header("Location: ../admin/events.php?status=deleted");
} else {
    header("Location: ../admin/events.php?status=notdeleted");
}
exit();
?>