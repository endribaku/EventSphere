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

$per_page = 5;
$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;
$offset = ($page - 1) * $per_page;

//paginated logic
$countQuery = "SELECT COUNT(*) AS total FROM bookings b
JOIN events e ON b.event_id = e.id
JOIN venues v ON e.venue_id = v.id
JOIN users u ON b.user_id = u.id WHERE 1=1";
$countParams = [];
$countTypes = "";

if(!empty($user)) {
    $countQuery .= " AND u.name LIKE ?";
    $countParams[] = "%$user%";
    $countTypes .= "s";
}

if(!empty($event)) {
    $countQuery .= " AND e.title LIKE ?";
    $countParams[] = "%$event%";
    $countTypes .= "s";
}

if(!empty($status)) {
    $currentDate = date("Y-m-d");
    if($status == "upcoming") {
        $countQuery .= " AND e.date >= ?";
        $countParams[] = $currentDate;
        $countTypes .= "s";
    } else if($status == "past") {
        $countQuery .= " AND e.date < ?";
        $countParams[] = $currentDate;
        $countTypes .= "s";
    }
}

if (!empty($start_date)) {
    $countQuery .= " AND b.booking_date >= ?";
    $countParams[] = $start_date . " 00:00:00";
    $countTypes .= "s";
}

if (!empty($end_date)) {
    $countQuery .= " AND b.booking_date <= ?";
    $countParams[] = $end_date . " 23:59:59";
    $countTypes .= "s";
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$total_results = $countStmt->get_result()->fetch_assoc()["total"];
$countStmt->close();
$total_pages = ceil($total_results / $per_page);


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

$bookingsQuery .= " LIMIT ? OFFSET ?";
$parameters[] = $per_page;
$parameters[] = $offset;
$types .= "ii";


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

    


        echo "<div style='margin-top:20px;'>Pages: ";
        for ($i = 1; $i <= $total_pages; $i++) {
            $query = $_GET;
            $query["page"] = $i;
            $url = htmlspecialchars($_SERVER["PHP_SELF"] . "?" . http_build_query($query));
            $isCurrent = ($i == $page) ? "style='font-weight:bold;'" : "";
            echo "<a href='$url' $isCurrent>$i</a> ";
        }
        echo "</div>";
}
else {
    echo "<h2> No Bookings Found </h2>";
}

?>