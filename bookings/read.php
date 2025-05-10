<?php 
function getAllBookings($conn) {
    $sql = "SELECT * FROM bookings";
    $stmt = $conn->prepare($sql);
    
    if($stmt->execute()) {
        return $stmt->get_result();
    } else {
        return false;
    }
    
}

?>