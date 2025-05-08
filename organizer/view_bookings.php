<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event Bookings</title>
</head>

<?php
    require_once("organizer_header.php");

    if(!isset($_GET["id"]) || !is_numeric( $_GET["id"] )) {
        echo "Invalid event id";
        exit();
    }

    $event_id = $_GET["id"];
    $bookingsQuery = "SELECT u.name, u.email, b.tickets, b.booking_date FROM users u, bookings b 
                    WHERE u.id = b.user_id AND event_id = ?";
    $bookings = $conn->prepare($bookingsQuery);
    $bookings->bind_param("i", $event_id);
    $bookings->execute();
    $bookings = $bookings->get_result();

    if($bookings->num_rows === 0) {
        echo "<h2> No bookings found for this event. </h2>";
    } else {

    echo "<table>
            <tr>
            <th> Name </th>
            <th> Email </th>
            <th> Tickets </th>
            <th> Booking Date </th>
            </tr>";

    while($booking = $bookings->fetch_assoc()) {
        echo "<tr>";
        echo "<td>".htmlspecialchars( $booking["name"] )."</td>";
        echo "<td>".htmlspecialchars( $booking["email"] )."</td>";
        echo "<td>".htmlspecialchars( $booking["tickets"] )."</td>";
        echo "<td>".htmlspecialchars( $booking["booking_date"] )."</td>";
        echo "</tr>";
    }

    echo "</table>";
    echo "</body>";
    echo "</html>";
    }
?>