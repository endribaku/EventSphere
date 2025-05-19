<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../bookings/read.php");
?>

<h2 class="section-title">Manage Bookings</h2>

<div class="filter-form">
    <form action="bookings.php" method="GET">
        <div class="form-row">
            <div class="form-group form-group-half">
                <label for="user">User Name</label>
                <input type="text" id="user" name="user" class="form-input" value="<?php echo isset($_GET['user']) ? htmlspecialchars($_GET['user']) : ''; ?>">
            </div>
            
            <div class="form-group form-group-half">
                <label for="event">Event Name</label>
                <input type="text" id="event" name="event" class="form-input" value="<?php echo isset($_GET['event']) ? htmlspecialchars($_GET['event']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group form-group-third">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="any" <?php if(isset($_GET["status"]) && $_GET["status"] == "any") echo "selected"; ?>>Any</option>
                    <option value="upcoming" <?php if(isset($_GET["status"]) && $_GET["status"] == "upcoming") echo "selected"; ?>>Upcoming</option>
                    <option value="past" <?php if(isset($_GET["status"]) && $_GET["status"] == "past") echo "selected"; ?>>Past</option>
                </select>
            </div>
            
            <div class="form-group form-group-third">
                <label for="sort">Sort</label>
                <select id="sort" name="sort" class="form-select">
                    <option value="ascending" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "ascending") echo "selected"; ?>>Date (Ascending)</option>
                    <option value="descending" <?php if(isset($_GET["sort"]) && $_GET["sort"] == "descending") echo "selected"; ?>>Date (Descending)</option>
                </select>
            </div>
            
            <div class="form-group form-group-third">
                <label for="tickets">Tickets</label>
                <input type="number" id="tickets" name="tickets" placeholder="Minimum tickets" class="form-input" value="<?php echo isset($_GET['tickets']) ? htmlspecialchars($_GET['tickets']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group form-group-half">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-input" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
            </div>
            
            <div class="form-group form-group-half">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-input" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="bookings.php" class="btn btn-secondary">Reset Filters</a>
    </form>
</div>

<?php 
// Display status messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'cancelled') {
        echo '<div class="alert alert-success">Booking cancelled successfully.</div>';
    } elseif ($_GET['status'] === 'error') {
        echo '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    }
}

$user = (isset($_GET["user"])) ? $_GET["user"] : "";
$event = (isset($_GET["event"])) ? $_GET["event"] :"";
$status = (isset($_GET["status"])) ? $_GET["status"] : "any";
$sort = (isset($_GET["sort"])) ? $_GET["sort"] : "ascending";
$start_date = (isset($_GET["start_date"])) ? $_GET["start_date"] :"";
$end_date = (isset($_GET["end_date"])) ? $_GET["end_date"] :"";
$tickets = (isset($_GET["tickets"]) && is_numeric($_GET["tickets"])) ? (int)$_GET["tickets"] : null;

$per_page = 10;
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

if (!is_null($tickets)) {
    $countQuery .= " AND b.tickets >= ?";
    $countParams[] = $tickets;
    $countTypes .= "i";
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
$bookingsQuery = "SELECT u.name, u.email, e.title AS title, e.description, e.date AS event_date, 
   v.location AS venue_location, v.name AS venue_name, b.booking_date, e.price, e.image, 
   b.tickets, b.id AS booking_id, e.id AS event_id
   FROM bookings b
   JOIN events e ON b.event_id = e.id 
   JOIN venues v ON e.venue_id = v.id 
   JOIN users u ON b.user_id = u.id 
   WHERE 1=1";
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

    $end_date .= " 23:59:59";
    $bookingsQuery .= " AND b.booking_date <= ?";
    $parameters[] = $end_date;
    $types .= "s";  
}

if (!is_null($tickets)) {
    $bookingsQuery .= " AND b.tickets >= ?";
    $parameters[] = $tickets;
    $types .= "i";
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

$currentDate = date("Y-m-d");

if($bookingResult->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Tickets</th>
                    <th>Total Price</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    
    while($booking = $bookingResult->fetch_assoc()) {
        $eventDate = new DateTime($booking['event_date']);
        $formattedEventDate = $eventDate->format('M d, Y');
        
        $bookingDate = new DateTime($booking['booking_date']);
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
        
        echo '<tr>';
        echo '<td>';
        echo '<div class="user-info">';
        echo '<span class="user-name">' . htmlspecialchars($booking["name"]) . '</span>';
        echo '<span class="user-email">' . htmlspecialchars($booking["email"]) . '</span>';
        echo '</div>';
        echo '</td>';
        
        echo '<td>' . htmlspecialchars($booking["title"]) . '</td>';
        echo '<td>' . $formattedEventDate . '</td>';
        echo '<td>' . htmlspecialchars($booking["venue_name"]) . '</td>';
        echo '<td>' . $booking["tickets"] . '</td>';
        echo '<td>$' . number_format($totalPrice, 2) . '</td>';
        echo '<td>' . $formattedBookingDate . '</td>';
        echo '<td><span class="status-badge ' . $statusClass . '">' . $status . '</span></td>';
        
        echo '<td class="actions">';
        echo '<a href="event_details.php?id=' . $booking['event_id'] . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> View Event</a>';
        
        if($booking["event_date"] > $currentDate) {
            echo '<a href="../bookings/admin_cancel.php?id=' . $booking['booking_id'] . '&event_id='. $booking['event_id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to cancel this booking?\')"><i class="fas fa-times"></i> Cancel</a>';
        }
        
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';

    // Pagination
    if ($total_pages > 1) {
        echo '<div class="pagination">';
        for ($i = 1; $i <= $total_pages; $i++) {
            $query = $_GET;
            $query["page"] = $i;
            $url = htmlspecialchars($_SERVER["PHP_SELF"] . "?" . http_build_query($query));
            $active = ($i == $page) ? "active" : "";
            echo '<a href="' . $url . '" class="' . $active . '">' . $i . '</a>';
        }
        echo '</div>';
    }
} else {
    echo '<div class="no-results">';
    echo '<i class="fas fa-ticket-alt fa-3x"></i>';
    echo '<p>No bookings found matching your criteria.</p>';
    echo '</div>';
}
?>

</div> <!-- Close dashboard-container -->
</body>
</html>
