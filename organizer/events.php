<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");
    require_once("organizer_header.php");
?>

<h2 class="section-title">My Events</h2>

<div class="filter-form">
    <form action="events.php" method="GET">
        <input type="text" name="search" placeholder="Search by title or keyword"
            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

        <select name="category">
            <option value="">All Categories</option>
            <?php
               $categories = mysqli_query($conn, "SELECT id, name FROM event_categories");
               while ($category = mysqli_fetch_assoc($categories)) {
                    $selected = (isset($_GET["category"]) && $_GET["category"] == $category["id"]) ? "selected": "";
                    echo "<option value='{$category['id']}' $selected> {$category['name']}</option>";
               }
            ?>
        </select>

        <select name="date_filter">
            <option value="">Any Date</option>
            <option value="today" <?php if(isset($_GET['date_filter']) && $_GET['date_filter'] == "today") echo 'selected'; ?>>Today</option>
            <option value="upcoming" <?php if(isset($_GET['date_filter']) && $_GET['date_filter'] == "upcoming") echo 'selected'; ?>>Upcoming</option>
            <option value="past" <?php if(isset($_GET['date_filter']) && $_GET['date_filter'] == "past") echo 'selected'; ?>>Past</option>
            <option value="this_month" <?php if(isset($_GET['date_filter']) && $_GET['date_filter'] == 'this_month') echo 'selected'; ?>>This Month</option>
        </select>

        <select name="sort">
            <option value="">Sort By</option>
            <option value="date_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_asc') echo 'selected'; ?>>Date (Ascending)</option>
            <option value="date_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_desc') echo 'selected'; ?>>Date (Descending)</option>
            <option value="price_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_asc') echo 'selected'; ?>>Price (Low to High)</option>
            <option value="price_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_desc') echo 'selected'; ?>>Price (High to Low)</option>
        </select>

        <div class="price-range">
            <input type="number" step="0.01" name="min_price" placeholder="Min Price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
            <input type="number" step="0.01" name="max_price" placeholder="Max Price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
        </div>

        <div class="ticket-range">
            <input type="number" step="1" name="min_tickets" placeholder="Min Tickets Sold" value="<?php echo isset($_GET['min_tickets']) ? htmlspecialchars($_GET['min_tickets']) : ''; ?>">
            <input type="number" step="1" name="max_tickets" placeholder="Max Tickets Sold" value="<?php echo isset($_GET['max_tickets']) ? htmlspecialchars($_GET['max_tickets']) : ''; ?>">
        </div>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="events.php" class="btn btn-secondary">Reset Filters</a>
    </form>
</div>

<?php
// Display status messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'deleted') {
        echo '<div class="alert alert-success">Event deleted successfully.</div>';
    } elseif ($_GET['status'] === 'notdeleted') {
        echo '<div class="alert alert-danger">Failed to delete event.</div>';
    } elseif ($_GET['status'] === 'updated') {
        echo '<div class="alert alert-success">Event updated successfully.</div>';
    }
}
    
$searchBar = isset($_GET["search"]) ? $_GET["search"] : "";
$category = isset($_GET["category"]) ? $_GET["category"] : "";
$date_filter = isset($_GET["date_filter"]) ? $_GET["date_filter"] : "";
$sort = isset($_GET["sort"]) ? $_GET["sort"] : "";
$min_tickets = isset($_GET["min_tickets"]) && is_numeric($_GET["min_tickets"]) ? floatval($_GET["min_tickets"]) : null;
$max_tickets = isset($_GET["max_tickets"]) && is_numeric($_GET["max_tickets"]) ? floatval($_GET["max_tickets"]) : null;
$min_price = isset($_GET["min_price"]) && is_numeric($_GET["min_price"]) ? floatval($_GET["min_price"]) : null;
$max_price = isset($_GET["max_price"]) && is_numeric($_GET["max_price"]) ? floatval($_GET["max_price"]) : null;

$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

$filterQuery = "SELECT e.*, c.name AS category_name, v.name AS venue_name, v.location AS venue_location, v.capacity AS venue_capacity,
                (SELECT COALESCE(SUM(tickets), 0) FROM bookings WHERE event_id = e.id) AS tickets_sold
                FROM events e
                JOIN event_categories c ON e.category_id = c.id
                JOIN venues v ON e.venue_id = v.id
                WHERE e.organizer_id = ?";
$parameters = [$_SESSION["user_id"]];
$types = 'i'; 

if(!empty($searchBar)) {
    $filterQuery .= " AND (e.title LIKE ? OR e.description LIKE ?) ";  
    $searchTerm = "%$searchBar%";
    $parameters[] = $searchTerm;
    $parameters[] = $searchTerm;
    $types .= "ss";
}

if(!empty($category)) {
    $filterQuery .= " AND e.category_id = ?";
    $parameters[] = $category;
    $types .= "i";
}

$currentDate = date('Y-m-d');
if(!empty($date_filter)) {
    switch($date_filter) {
        case "today":
            $filterQuery .= " AND e.date = ?";
            $parameters[] = $currentDate;
            $types .= 's';
            break;
        case "upcoming":
            $filterQuery .= " AND e.date >= ?";
            $parameters[] = $currentDate;
            $types .= 's';
            break;
        case "past":
            $filterQuery .= " AND e.date < ?";
            $parameters[] = $currentDate;
            $types .= 's';
            break;
        case 'this_month':
            $firstDayOfMonth = date('Y-m-01');
            $lastDayOfMonth = date('Y-m-t');
            $filterQuery .= " AND e.date BETWEEN ? AND ?";
            $parameters[] = $firstDayOfMonth;
            $parameters[] = $lastDayOfMonth;
            $types .= "ss";
            break;
    }
}

if(!is_null($min_tickets) || !is_null($max_tickets)) {
    $filterQuery .= " HAVING ";
    $conditions = [];
    
    if (!is_null($min_tickets)) {
        $conditions[] = "tickets_sold >= ?";
        $parameters[] = $min_tickets;
        $types .= 'i';
    }

    if (!is_null($max_tickets)) {
        $conditions[] = "tickets_sold <= ?";
        $parameters[] = $max_tickets;
        $types .= 'i';
    }
    
    $filterQuery .= implode(" AND ", $conditions);
}

if (!empty($min_price)) {
    $filterQuery .= (!is_null($min_tickets) || !is_null($max_tickets)) ? " AND " : " HAVING ";
    $filterQuery .= "e.price >= ?";
    $parameters[] = $min_price;
    $types .= 'd';
}

if (!empty($max_price)) {
    $filterQuery .= (!is_null($min_tickets) || !is_null($max_tickets) || !empty($min_price)) ? " AND " : " HAVING ";
    $filterQuery .= "e.price <= ?";
    $parameters[] = $max_price;
    $types .= 'd';
}

switch ($sort) {
    case 'date_asc':
        $filterQuery .= " ORDER BY e.date ASC";
        break;
    case 'date_desc':
        $filterQuery .= " ORDER BY e.date DESC";
        break;
    case 'price_asc':
        $filterQuery .= " ORDER BY e.price ASC";
        break;
    case 'price_desc':
        $filterQuery .= " ORDER BY e.price DESC";
        break;
    default:
        $filterQuery .= " ORDER BY e.date ASC";
        break;
}

$countQuery = "SELECT COUNT(*) AS total FROM (" . $filterQuery . ") AS total_events";
$countTypes = $types;
$countParams = $parameters;

$filterQuery .= " LIMIT ? OFFSET ?";
$types .= "ii";
$parameters[] = $per_page;
$parameters[] = $offset;

$countStmt = mysqli_prepare($conn, $countQuery);
if (!empty($countParams)) {
    mysqli_stmt_bind_param($countStmt, $countTypes, ...$countParams);
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$total_results = mysqli_fetch_assoc($countResult)['total'];
$total_pages = ceil($total_results / $per_page);
mysqli_stmt_close($countStmt);

$stmt = mysqli_prepare($conn, $filterQuery);
mysqli_stmt_bind_param($stmt,$types, ...$parameters);
mysqli_stmt_execute($stmt);
$eventResults = mysqli_stmt_get_result($stmt);

if($eventResults->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Category</th>
                    <th>Tickets Sold</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';

    while($event = mysqli_fetch_assoc($eventResults)) {
        $eventDate = new DateTime($event['date']);
        $formattedDate = $eventDate->format('M d, Y');
        
        // Determine status
        $status = "";
        $statusClass = "";
        if($event["date"] < $currentDate) {
            $status = "Past";
            $statusClass = "status-past";
        } else if($event["date"] > $currentDate) {
            $status = "Upcoming";
            $statusClass = "status-upcoming";
        } else {
            $status = "Today";
            $statusClass = "status-today";
        }
        
        echo '<tr>';
        
        // Event image
        echo '<td class="event-image-cell">';
        if (!empty($event['image'])) {
            echo '<img src="' . htmlspecialchars($event['image']) . '" alt="' . htmlspecialchars($event['title']) . '" class="event-thumbnail">';
        } else {
            echo '<div class="no-image"><i class="fas fa-image"></i></div>';
        }
        echo '</td>';
        
        // Event title
        echo '<td>' . htmlspecialchars($event['title']) . '</td>';
        
        // Date
        echo '<td>' . $formattedDate . '</td>';
        
        // Venue
        echo '<td>' . htmlspecialchars($event['venue_name']) . '<br><small>' . htmlspecialchars($event['venue_location']) . '</small></td>';
        
        // Category
        echo '<td>' . htmlspecialchars($event['category_name']) . '</td>';
        
        // Tickets sold
        echo '<td>' . $event['tickets_sold'] . ' / ' . $event['venue_capacity'] . '</td>';
        
        // Price
        echo '<td>$' . number_format($event['price'], 2) . '</td>';
        
        // Status
        echo '<td><span class="status-badge ' . $statusClass . '">' . $status . '</span></td>';
        
        // Actions
        echo '<td class="actions">';
        echo '<a href="event_details.php?id=' . $event['id'] . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>';
        echo '<a href="view_bookings.php?event_id=' . $event['id'] . '" class="btn btn-sm btn-secondary"><i class="fas fa-ticket-alt"></i></a>';
        echo '<a href="update_event.php?id=' . $event['id'] . '" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i></a>';
        echo '<a href="../events/delete.php?id=' . $event['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this event?\')"><i class="fas fa-trash"></i></a>';
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
            $query['page'] = $i;
            $url = htmlspecialchars($_SERVER["PHP_SELF"] . "?" . http_build_query($query));
            $active = $i == $page ? "active" : "";
            echo '<a href="' . $url . '" class="' . $active . '">' . $i . '</a>';
        }
        echo '</div>';
    }
} else {
    echo '<div class="no-results">';
    echo '<i class="fas fa-calendar-times fa-3x"></i>';
    echo '<p>No events found matching your criteria.</p>';
    echo '<p>Create your first event by clicking the button below.</p>';
    echo '<a href="create_event.php" class="btn btn-primary">Create New Event</a>';
    echo '</div>';
}
?>

</div> <!-- Close dashboard-container -->
</body>
<footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2><?= htmlspecialchars($site['company_name']) ?></h2>
                    <p><?= htmlspecialchars($site['footer_text']) ?></p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#events">Events</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($site['email']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($site['phone']) ?></p>
                    <div class="social-links">
                        <a href="<?= htmlspecialchars($site['facebook_link']) ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= htmlspecialchars($site['twitter_link']) ?>"><i class="fab fa-twitter"></i></a>
                        <a href="<?= htmlspecialchars($site['instagram_link']) ?>"><i class="fab fa-instagram"></i></a>
                        <a href="<?= htmlspecialchars($site['linkedin_link']) ?>"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?= htmlspecialchars($site['footer_text']) ?></p>
            </div>
        </div>
    </footer>

</html>
