<?php
    require_once("../user/user_auth.php");
    include_once("../php/db.php");

    if (!isset($_POST['id'], $_POST['tickets']) || !is_numeric($_POST['id']) || !is_numeric($_POST['tickets'])) {
        $_SESSION["error"] = "Invalid event id";
        header("Location: ../user/browse_events.php");
        exit();
    }
    
    $event_id = (int)$_POST["id"];
    $nrTickets = (int)$_POST["tickets"];
    include_once("../user/user_header.php");

    


    $eventQuery = "SELECT * from events WHERE id = ?";
    $stmt = $conn->prepare($eventQuery);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result();
    

    if($event->num_rows > 0) {
        $event = $event->fetch_assoc();
        $eventPrice = $event["price"];
        // finding number of bookings for event
        $nrOfBookingsforEventQuery = "SELECT COALESCE(SUM(tickets), 0) AS numberbookings FROM bookings WHERE event_id=?";
        $stmt = $conn->prepare($nrOfBookingsforEventQuery);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $nrOfBookingsForEvent = $stmt->get_result();
        $nrOfBookingsForEvent = $nrOfBookingsForEvent->fetch_assoc();
        
        // finding event capacity
        $venuecapacityQuery= "SELECT v.capacity AS venue_capacity 
        FROM events e, venues v WHERE e.venue_id = v.id AND e.id = ?";
        $stmt = $conn->prepare($venuecapacityQuery);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $venueCapacity = $stmt->get_result();
        $venueCapacity = $venueCapacity->fetch_assoc();
        

        if($nrTickets > ($venueCapacity["venue_capacity"] - $nrOfBookingsForEvent["numberbookings"])) {
            
            $_SESSION["error"] = "Number of Tickets exceeds remaining available seats";
            header("Location: ../user/event_details.php?id=" . $event_id);
            exit();
        } else {
            $totalPrice = $eventPrice * $nrTickets;
            
            $insertBookingQuery = "INSERT INTO bookings (user_id, event_id, tickets, total_price, booking_date) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertBookingQuery);
            $stmt->bind_param("iiids", $_SESSION["user_id"], $event_id, $nrTickets, $totalPrice, date("Y-m-d H:i:s"));
            if($stmt->execute()) {
                
                $_SESSION["success"] = "Booking made successfully";
                header("Location: ../user/event_details.php?id=" . $event_id);
                exit();
            } else {
                
                $_SESSION["error"] = "Booking couldn't be made successfully";
                header("Location: ../user/event_details.php?id=" . $event_id);
                exit();
            } 
        }


    } else {
        echo "Event not found";
    }
?>

