<?php 
require_once("admin_auth.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
</head>

<?php 
require_once("../php/db.php");
require_once("../bookings/read.php");
require_once("../admin/admin_header.php");
?>

<form action="bookings.php" method="GET">
    <div class="form-group">
        <label for="user">User Name</label>
        <input type="text" name="user" id ="user">
    </div>
    
    <div class="form-group">
        <label for="event">Event Name</label>
        <input type="text" name="event" id ="event">
    </div>
    
    <div class="form-group">
        <label for="status">Status</label>
        <select name="status" id="status">
            <option value="any" <?php if(isset($_GET["status"]) && $_GET["status"] == "any") 
            echo "selected"; else echo ""; ?>>Any</option>
            <option value="upcoming" <?php if(isset($_GET["status"]) && $_GET["status"] == "upcoming") 
            echo "selected"; else echo ""; ?>>Upcoming</option>
            <option value="past" <?php if(isset($_GET["status"]) && $_GET["status"] == "past") 
            echo "selected"; else echo ""; ?>>Past</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="sort">Sort</label>
        <select name="sort" id="sort">
            <option value="ascending" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "ascending") 
            echo "selected"; else echo ""; ?>>Ascending</option>
            <option value="descending" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "descending") 
            echo "selected"; else echo ""; ?>>Descending</option>
        </select>
    </div>
    

    <div class="form-group">
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" >
    </div>
    
    
    <div class="form-group">
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" >
    </div>
    

    <button type="submit">Filter</button>
</form>

<?php 
$user = (isset($_GET["user"])) ? $_GET["user"] : "";
$event = (isset($_GET["event"])) ? $_GET["event"] :"";
$status = (isset($_GET["status"])) ? $_GET["status"] : "any";
$sort = (isset($_GET["sort"])) ? $_GET["sort"] : "ascending";
$start_date = (isset($_GET["start_date"])) ? $_GET["start_date"] :"";
$end_date = (isset($_GET["end_date"])) ? $_GET["end_date"] :"";

//filter sql logic
$bookingsQuery = "SELECT u.name, e.title AS title, e.description, e.date AS event_date, v.location AS venue_location, v.name 
   AS venue_name, b.booking_date, e.price, e.image, b.tickets, b.id AS booking_id, e.id AS event_id
   FROM bookings b, events e, venues v, users u WHERE b.event_id = e.id 
   AND e.venue_id = v.id AND b.user_id = u.id";
$parameters = [];
$types = "";

if(!empty($user)) {
    $bookingsQuery .= " AND (u.name) LIKE ?";
    $parameters[] = "%$user%";
    $types .= "s";
}

if(!empty($event)) {
    $bookingsQuery .= " AND (e.title) LIKE ?";
    $parameters[] = "%$event%";
    $types .= "s";
}
if(!empty($status)) {
    $currentDate = date("Y-m-d");
    if($status == "upcoming") {
        $bookingsQuery .= " AND e.date >= ?";
        $parameters[] = $currentDate;
        $types .= "s";
    } else if($status == "past") {
        $bookingsQuery .= " AND e.date < ?";
        $parameters[] = $currentDate;
        $types .= "s";
    }
}

if (!empty($start_date)) {
     
    $start_date .= " 00:00:00";
    $bookingsQuery .= " AND b.booking_date >= ?";
    $parameters[] = $start_date;
    $types .= "s"; 
}

if (!empty($end_date)) {

    $end_date .= "23:59:59";
    $bookingsQuery .= " AND b.booking_date <= ?";
    $parameters[] = $end_date;
    $types .= "s";  
}

if ($sort == "ascending") {
    $bookingsQuery .= " ORDER BY b.booking_date ASC";
} else {
    $bookingsQuery .= " ORDER BY b.booking_date DESC";
}



$bookingResult = $conn->prepare($bookingsQuery);
if(!empty($parameters)) {
    $bookingResult->bind_param($types, ...$parameters);
}
$bookingResult->execute();
$bookingResult = $bookingResult->get_result();


if($bookingResult->num_rows > 0) {
    echo "<table>
                <tr>
                    <th> User Name </th>
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
    while($row = $bookingResult->fetch_assoc()) {
        echo "<tr>";
            echo "<td>".$row["name"]."</td>";
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
                echo "<a href='../bookings/admin_cancel.php?id=" . $row['booking_id'] . '&event_id='. $row['event_id']."'>Cancel</a>";
                echo "</td>";
            } else {
                echo "None";
            }
            
            echo "</tr>";
        }

        echo "</table>";

    }
else {
    echo "<h2> No Bookings Found </h2>";
}

?>