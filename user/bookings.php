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
    <input type="text" name="search" placeholder="Search event or venue"
           value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

    <label for="status">Status</label>
    <select name="status" id="status">
        <option value="any" <?= isset($_GET["status"]) && $_GET["status"] == "any" ? "selected" : "" ?>>Any</option>
        <option value="upcoming" <?= isset($_GET["status"]) && $_GET["status"] == "upcoming" ? "selected" : "" ?>>Upcoming</option>
        <option value="past" <?= isset($_GET["status"]) && $_GET["status"] == "past" ? "selected" : "" ?>>Past</option>
    </select>

    <label for="sort">Sort</label>
    <select name="sort" id="sort">
        <option value="ascending" <?= isset($_GET["sort"]) && $_GET["sort"] == "ascending" ? "selected" : "" ?>>Ascending</option>
        <option value="descending" <?= isset($_GET["sort"]) && $_GET["sort"] == "descending" ? "selected" : "" ?>>Descending</option>
    </select>

    <input type="date" name="date_from" value="<?= $_GET["date_from"] ?? '' ?>">
    <input type="date" name="date_to" value="<?= $_GET["date_to"] ?? '' ?>">

    <input type="number" name="min_tickets" placeholder="Min Tickets" value="<?= $_GET["min_tickets"] ?? '' ?>">
    <input type="number" name="max_tickets" placeholder="Max Tickets" value="<?= $_GET["max_tickets"] ?? '' ?>">

    <input type="number" step="0.01" name="min_price" placeholder="Min Price" value="<?= $_GET["min_price"] ?? '' ?>">
    <input type="number" step="0.01" name="max_price" placeholder="Max Price" value="<?= $_GET["max_price"] ?? '' ?>">

    <button type="submit">Filter</button>
</form>



<?php
   
   $status = isset($_GET["status"]) ? $_GET["status"] : "any";
   $sort = isset($_GET["sort"]) ? $_GET["sort"] : "ascending";
   // filter sql logic
   $bookingsQuery = "SELECT e.title AS title, e.description, e.date AS event_date,
    v.location AS venue_location, v.name AS venue_name,
    b.booking_date, e.price, e.image, b.tickets, b.id AS booking_id, e.id AS event_id
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    JOIN venues v ON e.venue_id = v.id
    WHERE b.user_id = ?";

   $types = "i";
   $parameters = [$_SESSION["user_id"]];

   if (!empty($_GET['search'])) {
    $bookingsQuery .= " AND (e.title LIKE ? OR v.name LIKE ?)";
    $searchTerm = "%" . $_GET['search'] . "%";
    $parameters[] = $searchTerm;
    $parameters[] = $searchTerm;
    $types .= "ss";
    }

    if ($status === "upcoming") {
        $bookingsQuery .= " AND e.date > ?";
        $parameters[] = date("Y-m-d");
        $types .= "s";
    } elseif ($status === "past") {
        $bookingsQuery .= " AND e.date < ?";
        $parameters[] = date("Y-m-d");
        $types .= "s";
    }


    if (!empty($_GET["date_from"])) {
        $bookingsQuery .= " AND e.date >= ?";
        $parameters[] = $_GET["date_from"];
        $types .= "s";
    }

    if (!empty($_GET["date_to"])) {
        $bookingsQuery .= " AND e.date <= ?";
        $parameters[] = $_GET["date_to"];
        $types .= "s";
    }

    if (!empty($_GET["min_tickets"]) && is_numeric($_GET["min_tickets"])) {
        $bookingsQuery .= " AND b.tickets >= ?";
        $parameters[] = $_GET["min_tickets"];
        $types .= "i";
    }

    if (!empty($_GET["max_tickets"]) && is_numeric($_GET["max_tickets"])) {
        $bookingsQuery .= " AND b.tickets <= ?";
        $parameters[] = $_GET["max_tickets"];
        $types .= "i";
    }
    // total ticket price
    if (!empty($_GET["min_price"]) && is_numeric($_GET["min_price"])) {
        $bookingsQuery .= " AND (e.price * b.tickets) >= ?";
        $parameters[] = $_GET["min_price"];
        $types .= "d";
    }
    
    if (!empty($_GET["max_price"]) && is_numeric($_GET["max_price"])) {
        $bookingsQuery .= " AND (e.price * b.tickets) <= ?";
        $parameters[] = $_GET["max_price"];
        $types .= "d";
    }
   
    if ($sort === "ascending") {
        $bookingsQuery .= " ORDER BY e.date ASC";
    } elseif ($sort === "descending") {
        $bookingsQuery .= " ORDER BY e.date DESC";
    } else {
        $bookingsQuery .= " ORDER BY e.date ASC"; // default
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


