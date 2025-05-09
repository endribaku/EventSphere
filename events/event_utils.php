// event_utils.php
<?php
function deleteEventById($conn, $event_id) {
    $getEventQuery = "SELECT * FROM events WHERE id = ?";
    $stmt = mysqli_prepare($conn, $getEventQuery);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);
    
    if (!$event) {
        return false;
    }

    // Delete image if exists
    $imagePath = $event['image'];
    if ($imagePath && file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete event
    $deleteQuery = "DELETE FROM events WHERE id = ?";
    $stmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    return mysqli_stmt_execute($stmt);
}

?>