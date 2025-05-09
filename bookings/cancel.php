<?php 
    require_once("../user/user_auth.php");
    include_once("../php/db.php");

    if(!isset($_GET["id"], $_GET["event_id"]) || !is_numeric($_GET["id"]) || !is_numeric($_GET["event_id"])) {
        $_SESSION["error"] = "Invalid event id";
        header("Location: ../user/browse_events.php");
        exit();
    }
    

    $bookingId = $_GET["id"];
    $userId = $_SESSION["user_id"];
    $event_id = $_GET["event_id"];

    $cancelBookingQuery = "DELETE FROM bookings WHERE id = ? AND user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($cancelBookingQuery);
    $stmt->bind_param("iii", $bookingId, $userId, $event_id);
    $stmt->execute();

    if($stmt->affected_rows > 0) {
        $_SESSION["success"] = "Cancellation made successfully";
        header("Location: ../user/bookings.php");
        exit();
    } else {
        $_SESSION["success"] = "Cancellation error";
        header("Location: ../user/bookings.php");
        exit();
    }




?>