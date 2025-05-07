<?php
    require_once("user_auth.php");
    include_once("../php/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>

</head>




<?php
   include_once("user_header.php");
   $bookingsQuery = "SELECT e.title AS title, e.description, e.date AS event_date, v.location AS venue_location, v.name AS venue_name, b.booking_date, e.image, b.tickets FROM bookings b, events e, venues v WHERE b.event_id = e.id AND e.venue_id = v.id AND user_id = ?";
   $stmt = $conn->prepare($bookingsQuery);
   $stmt->bind_param("i", $_SESSION["user_id"]);
   $stmt->execute();
   $allBookings = $stmt->get_result();
   $stmt->close();

   echo "<table>
            <tr>
                <th> Event Title </th>
                <th> Description </th>
                <th> Event Date </th>
                <th> Venue </th>
                <th> Location </th>
                <th> Tickets </th>
                <th> Date of Booking </th>
                <th> Status </th>
                <th>
            </tr>";

            // will add image in future to the bookings table
    $currentDate = date("Y-m-d");
    while ($row = $allBookings->fetch_assoc()) {
        
        echo "<tr>";
        echo "<td>".$row["title"]."</td>";
        echo "<td>".$row["description"]."</td>";
        echo "<td>".$row["event_date"]."</td>";
        echo "<td>".$row["venue_name"]."</td>";
        echo "<td>".$row["venue_location"]."</td>";
        echo "<td>".$row["tickets"]."</td>";
        echo "<td>".$row["booking_date"]."</td>";
        echo "<td>";
        if($row["event_date"] < $currentDate) {
            echo "Past";
        } else if($row["event_date"] > $currentDate) {
            echo "Upcoming";
        } else {
            echo "Ongoing";
        }
        echo "</tr>";
    }

    echo "</table>";
    echo "</body>";
    echo "</html>";

?>