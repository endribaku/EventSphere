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

<?php include_once("user_header.php"); ?>

<form action="bookings.php" method="GET">
    <label for="status">Status</label>
    <select name="status" id="status">
        <option value="any" <?php if(isset($_GET["status"]) && $_GET["status"] == "any") 
        echo "selected"; else echo ""; ?>>Any</option>
        <option value="upcoming" <?php if(isset($_GET["status"]) && $_GET["status"] == "upcoming") 
        echo "selected"; else echo ""; ?>>Upcoming</option>
        <option value="past" <?php if(isset($_GET["status"]) && $_GET["status"] == "past") 
        echo "selected"; else echo ""; ?>>Past</option>
    </select>
    <label for="sort">Sort</label>
    <select name="sort" id="sort">
        <option value="ascending" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "ascending") 
        echo "selected"; else echo ""; ?>>Ascending</option>
        <option value="descending" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "descending") 
        echo "selected"; else echo ""; ?>>Descending</option>
    </select>

    <button type="submit">Filter</button>
</form>


<?php
   
   $status = isset($_GET["status"]) ? $_GET["status"] : "any";
   $sort = isset($_GET["sort"]) ? $_GET["sort"] : "ascending";
   // filter sql logic
   $bookingsQuery = "SELECT e.title AS title, e.description, e.date AS event_date, v.location AS venue_location, v.name 
   AS venue_name, b.booking_date, e.price, e.image, b.tickets, b.id AS booking_id, e.id AS event_id
   FROM bookings b, events e, venues v WHERE b.event_id = e.id 
   AND e.venue_id = v.id AND user_id = ?";

   $types = "i";
   $parameters = [$_SESSION["user_id"]];

   if(!empty($status)) {
    $currentDate = date("Y-m-d");
    if($status == "upcoming") {
        $bookingsQuery .= " AND e.date >= ?";
        $types .= "s";
        $parameters[] = $currentDate; 
    } else if($status == "past") {
        $bookingsQuery .= " AND e.date < ?";
        $types .= "s";
        $parameters[] = $currentDate;
    } else if($status == "any") {

    }
   }

   if(!empty($sort)) {
     if($sort == "ascending") {
        $bookingsQuery .= " ORDER BY e.date ASC";
     } else if($sort == "descending") {
        $bookingsQuery .= " ORDER BY e.date DESC";
     }
   }
   
   
   $stmt = $conn->prepare($bookingsQuery);
   if(count($parameters) > 1) {
        $stmt->bind_param($types, ...$parameters);
   } else {
    $stmt->bind_param($types, $parameters[0]);
   }
    
   
   $stmt->execute();
   $allBookings = $stmt->get_result();
   $stmt->close();
   if($allBookings->num_rows > 0) {
   
    echo "<table>
                <tr>
                    <th> Event Title </th>
                    <th> Description </th>
                    <th> Event Date </th>
                    <th> Venue </th>
                    <th> Location </th>
                    <th> Tickets </th>
                    <th> Total Price </th>
                    <th> Date of Booking </th>
                    <th> Status </th>
                    <th> Actions </th>
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

            // sum of tickets logic
            $sumOfTickets = $row["price"] * $row["tickets"];
            echo "<td> $" . number_format($sumOfTickets, 2). "</td>";


            echo "<td>".$row["booking_date"]."</td>";
            echo "<td>";
            if($row["event_date"] < $currentDate) {
                echo "Past";
            } else if($row["event_date"] > $currentDate) {
                echo "Upcoming";
            } else {
                echo "Ongoing";
            }
            echo "</td>";
            if($row["event_date"] > $currentDate) {
                echo "<td>";
                echo "<a href='../bookings/cancel.php?id=" . $row['booking_id'] . '&event_id='. $row['event_id']."'>Cancel</a>";
                echo "</td>";
            } else {
                echo "None";
            }
            
            echo "</tr>";
        }

        echo "</table>";
    }
    else {
        echo "<h2>No bookings found with the selected filters.</h2>";
    }
    echo "</body>";
    echo "</html>";

?>


