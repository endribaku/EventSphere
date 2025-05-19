<?php
    require_once("user_auth.php");
    include_once("../php/db.php");
    include_once("user_header.php");
?>

<h2 class="section-title">My Bookings</h2>

<div class="filter-form">
    <form action="bookings.php" method="GET">
        <input type="text" name="search" placeholder="Search event or venue"
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

        <select name="status">
            <option value="any" <?= isset($_GET["status"]) && $_GET["status"] == "any" ? "selected" : "" ?>>Any Status</option>
            <option value="upcoming" <?= isset($_GET["status"]) && $_GET["status"] == "upcoming" ? "selected" : "" ?>>Upcoming</option>
            <option value="past" <?= isset($_GET["status"]) && $_GET["status"] == "past" ? "selected" : "" ?>>Past</option>
        </select>

        <select name="sort">
            <option value="ascending" <?= isset($_GET["sort"]) && $_GET["sort"] == "ascending" ? "selected" : "" ?>>Date (Ascending)</option>
            <option value="descending" <?= isset($_GET["sort"]) && $_GET["sort"] == "descending" ? "selected" : "" ?>>Date (Descending)</option>
        </select>

        <div class="date-range">
            <input type="date" name="date_from" placeholder="From Date" value="<?= $_GET["date_from"] ?? '' ?>">
            <input type="date" name="date_to" placeholder="To Date" value="<?= $_GET["date_to"] ?? '' ?>">
        </div>

        <div class="price-range">
            <input type="number" name="min_tickets" placeholder="Min Tickets" value="<?= $_GET["min_tickets"] ?? '' ?>">
            <input type="number" name="max_tickets" placeholder="Max Tickets" value="<?= $_GET["max_tickets"] ?? '' ?>">
        </div>

        <div class="price-range">
            <input type="number" step="0.01" name="min_price" placeholder="Min Total Price" value="<?= $_GET["min_price"] ?? '' ?>">
            <input type="number" step="0.01" name="max_price" placeholder="Max Total Price" value="<?= $_GET["max_price"] ?? '' ?>">
        </div>

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

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

    // Set pagination values early
    $per_page = 5;
    $page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? (int) $_GET["page"] : 1;
    $offset = ($page - 1) * $per_page;


    $countQuery = "SELECT COUNT(*) AS total FROM (" . $bookingsQuery . ") AS total_table";
    $countTypes = $types;
    $countParams = $parameters;

    $bookingsQuery .= " LIMIT ? OFFSET ?";
    $types .= "ii";
    $parameters[] = $per_page;
    $parameters[] = $offset;

    $countStmt = $conn->prepare($countQuery);
    if (count($countParams) > 1) {
        $countStmt->bind_param($countTypes, ...$countParams);
    } else {
        $countStmt->bind_param($countTypes, $countParams[0]);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result()->fetch_assoc();
    $total_results = $countResult["total"];
    $countStmt->close();
    $total_pages = ceil($total_results / $per_page);

    $stmt = $conn->prepare($bookingsQuery);
    if (count($parameters) > 1) {
        $stmt->bind_param($types, ...$parameters);
    } else {
        $stmt->bind_param($types, $parameters[0]);
    }
    $stmt->execute();
    $allBookings = $stmt->get_result();
    $stmt->close();
    
    $currentDate = date("Y-m-d");

    if($allBookings->num_rows > 0) {
        echo '<div class="bookings-container">';
        
        while ($booking = $allBookings->fetch_assoc()) {
            $eventDate = new DateTime($booking["event_date"]);
            $formattedEventDate = $eventDate->format('M d, Y');
            
            $bookingDate = new DateTime($booking["booking_date"]);
            $formattedBookingDate = $bookingDate->format('M d, Y H:i');
            
            $totalPrice = $booking["price"] * $booking["tickets"];
            
            // Determine status
            $status = "";
            $statusClass = "";
            if($booking["event_date"] < $currentDate) {
                $status = "Past";
                $statusClass = "status-past";
            } else if($booking["event_date"] > $currentDate) {
                $status = "Upcoming";
                $statusClass = "status-upcoming";
            } else {
                $status = "Today";
                $statusClass = "status-today";
            }
            
            echo '<div class="booking-card">';
            
            // Left side - Event image
            echo '<div class="booking-image">';
            if (!empty($booking['image'])) {
                echo '<img src="' . htmlspecialchars($booking['image']) . '" alt="' . htmlspecialchars($booking['title']) . '">';
            } else {
                echo '<img src="/placeholder.svg?height=200&width=300" alt="Event placeholder">';
            }
            echo '<div class="booking-status ' . $statusClass . '">' . $status . '</div>';
            echo '</div>';
            
            // Right side - Booking details
            echo '<div class="booking-details">';
            echo '<h3>' . htmlspecialchars($booking["title"]) . '</h3>';
            
            echo '<div class="booking-meta">';
            echo '<div class="meta-item"><i class="fas fa-calendar-alt"></i> ' . $formattedEventDate . '</div>';
            echo '<div class="meta-item"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($booking["venue_name"]) . ', ' . htmlspecialchars($booking["venue_location"]) . '</div>';
            echo '<div class="meta-item"><i class="fas fa-ticket-alt"></i> ' . $booking["tickets"] . ' ticket' . ($booking["tickets"] > 1 ? 's' : '') . '</div>';
            echo '<div class="meta-item"><i class="fas fa-dollar-sign"></i> $' . number_format($totalPrice, 2) . ' total</div>';
            echo '<div class="meta-item"><i class="fas fa-clock"></i> Booked on ' . $formattedBookingDate . '</div>';
            echo '</div>';
            
            echo '<div class="booking-actions">';
            echo '<a href="event_details.php?id=' . $booking['event_id'] . '" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> View Event</a>';

            if($booking["event_date"] > $currentDate) {
                echo '<a href="../bookings/cancel.php?id=' . $booking['booking_id'] . '&event_id='. $booking['event_id'] . '" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to cancel this booking?"><i class="fas fa-times"></i> Cancel Booking</a>';
            }

            echo '</div>'; // Close booking-actions
            echo '</div>'; // Close booking-details
            echo '</div>'; // Close booking-card
        }
        
        echo '</div>'; // Close bookings-container
        
        // Pagination
        if ($total_pages > 1) {
            echo '<div class="pagination">';
            for ($i = 1; $i <= $total_pages; $i++) {
                $query = $_GET;
                $query["page"] = $i;
                $url = htmlspecialchars($_SERVER["PHP_SELF"] . "?" . http_build_query($query));
                $active = $i == $page ? "active" : "";
                echo '<a href="' . $url . '" class="' . $active . '">' . $i . '</a>';
            }
            echo '</div>';
        }
    } else {
        echo '<div class="no-results">';
        echo '<i class="fas fa-ticket-alt fa-3x"></i>';
        echo '<p>No bookings found with the selected filters.</p>';
        echo '<p>Try adjusting your filters or <a href="browse_events.php">browse events</a> to make a booking.</p>';
        echo '</div>';
    }
?>

</div> <!-- Close dashboard-container -->
</body>
</html>



