<?php
    require_once("user_auth.php");
    include_once("../php/db.php");
    include_once("user_header.php");
?>

<h2 class="section-title">Browse Events</h2>

<div class="search-form">
    <form action="browse_events.php" method="GET">
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
            <option value="this_month" <?php if(isset($_GET['date_filter']) && $_GET['date_filter'] == 'this_month') echo 'selected'; ?>>This Month</option>
        </select>

        <select name="sort">
            <option value="">Sort By</option>
            <option value="date_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_asc') echo 'selected'; ?>>Date (Ascending)</option>
            <option value="date_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_desc') echo 'selected'; ?>>Date (Descending)</option>
            <option value="price_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_asc') echo 'selected'; ?>>Price (Low to High)</option>
            <option value="price_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'price_desc') echo 'selected'; ?>>Price (High to Low)</option>
        </select>

        <input type="number" step="0.01" name="min_price" placeholder="Min Price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
        <input type="number" step="0.01" name="max_price" placeholder="Max Price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<?php
    $per_page = 8; // Increased from 4 to show more events
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $per_page;

   $searchBar = isset($_GET["search"]) ? $_GET["search"] : "";
   $category = isset($_GET["category"]) ? $_GET["category"] : "";
   $date_filter = isset($_GET["date_filter"]) ? $_GET["date_filter"] : "";
   $sort = isset($_GET["sort"]) ? $_GET["sort"] : "";
   $min_price = isset($_GET["min_price"]) && is_numeric($_GET["min_price"]) ? floatval($_GET["min_price"]) : null;
   $max_price = isset($_GET["max_price"]) && is_numeric($_GET["max_price"]) ? floatval($_GET["max_price"]) : null;

   // filter sql query logic
   $filterQuery = "SELECT e.*, c.name AS category_name, v.name AS venue_name FROM events e 
                  JOIN event_categories c ON e.category_id = c.id 
                  JOIN venues v ON e.venue_id = v.id 
                  WHERE 1=1";
   $parameters = [];
   $types = '';

    

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
   if (!empty($min_price)) {
    $filterQuery .= " AND e.price >= ?";
    $parameters[] = $min_price;
    $types .= 'd';
   }

   if (!empty($max_price)) {
        $filterQuery .= " AND e.price <= ?";
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
        // Default sorting (you can change this)
        $filterQuery .= " ORDER BY e.date ASC";
        break;
    }

    $countQuery = "SELECT COUNT(*) AS total FROM (" . $filterQuery . ") AS temp";
    $countParams = $parameters;
    $countTypes = $types;

    $filterQuery .= " LIMIT ? OFFSET ?";
    $types .= "ii";
    $parameters[] = $per_page;
    $parameters[] = $offset;

    $countStmt = $conn->prepare($countQuery);
    if (!empty($countParams)) {
        $countStmt->bind_param($countTypes, ...$countParams);
    }
    $countStmt->execute();
    $total_results = $countStmt->get_result()->fetch_assoc()["total"];
    $countStmt->close();
    $total_pages = ceil($total_results / $per_page);

    

    $stmt = $conn->prepare($filterQuery);
    if(!empty($parameters)) {
        $stmt->bind_param($types, ...$parameters);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="events-container">';
        while ($event = $result->fetch_assoc()) {
            // Format the date
            $eventDate = new DateTime($event['date']);
            $formattedDate = $eventDate->format('M d, Y');
            
            // Get day and month for the event date badge
            $day = $eventDate->format('d');
            $month = $eventDate->format('M');
            
            echo '<div class="event-card" onclick="window.location.href=\'event_details.php?id=' . $event['id'] . '\'">';
            
            // Event image with date badge
            echo '<div class="event-image">';
            if (!empty($event['image'])) {
                echo '<img src="' . htmlspecialchars($event['image']) . '" alt="' . htmlspecialchars($event['title']) . '">';
            } else {
                echo '<img src="/placeholder.svg?height=200&width=300" alt="Event placeholder">';
            }
            
            echo '<div class="event-date">
                    <span class="day">' . $day . '</span>
                    <span class="month">' . $month . '</span>
                  </div>';
            echo '</div>';
            
            // Event details
            echo '<div class="event-details">';
            echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
            echo '<p class="event-category"><i class="fas fa-tag"></i> ' . htmlspecialchars($event['category_name']) . '</p>';
            echo '<p class="event-location"><i class="fas fa-map-marker-alt"></i> ' . htmlspecialchars($event['venue_name']) . '</p>';
            
            // Truncate description if it's too long
            $description = htmlspecialchars($event['description']);
            if (strlen($description) > 100) {
                $description = substr($description, 0, 100) . '...';
            }
            echo '<p class="event-description">' . $description . '</p>';
            
            echo '<div class="event-footer">';
            echo '<span class="event-price">$' . number_format($event['price'], 2) . '</span>';
            echo '<a href="event_details.php?id=' . $event['id'] . '" class="btn btn-sm btn-primary">View Details</a>';
            echo '</div>';
            
            echo '</div>'; // Close event-details
            echo '</div>'; // Close event-card
        }
        echo '</div>';

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
        echo '<i class="fas fa-search fa-3x"></i>';
        echo '<p>No events found matching your criteria.</p>';
        echo '<p>Try adjusting your filters or search terms.</p>';
        echo '</div>';
    }
?>

</div> <!-- Close dashboard-container -->
</body>
</html>







