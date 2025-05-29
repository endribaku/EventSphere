<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
?>
<?php 
if(isset($_SESSION["count_error"])) {
    if(isset($_SESSION["count_error"])) {
        echo '<div class="alert alert-danger">'.$_SESSION["count_error"].'</div>';
        unset($_SESSION["count_error"]);
    }
}


?>
<h2 class="section-title">Manage Venues</h2>

<div class="filter-form">
    <form method="GET" action="venues.php">
        <input type="text" name="name" placeholder="Search by name" value="<?= isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '' ?>" class="form-input">
        <input type="text" name="location" placeholder="Search by location" value="<?= isset($_GET['location']) ? htmlspecialchars($_GET['location']) : '' ?>" class="form-input">
        <input type="text" name="country" placeholder="Search by country" value="<?= isset($_GET['country']) ? htmlspecialchars($_GET['country']) : '' ?>" class="form-input">
        
        <div class="capacity-range">
            <input type="number" name="min_capacity" placeholder="Min capacity" value="<?= isset($_GET['min_capacity']) ? htmlspecialchars($_GET['min_capacity']) : '' ?>" class="form-input">
            <input type="number" name="max_capacity" placeholder="Max capacity" value="<?= isset($_GET['max_capacity']) ? htmlspecialchars($_GET['max_capacity']) : '' ?>" class="form-input">
        </div>

        <select name="sort" class="form-select">
            <option value="">Sort By</option>
            <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'name_asc') ? 'selected' : '' ?>>Name A–Z</option>
            <option value="name_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'name_desc') ? 'selected' : '' ?>>Name Z–A</option>
            <option value="capacity_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'capacity_asc') ? 'selected' : '' ?>>Capacity Low–High</option>
            <option value="capacity_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'capacity_desc') ? 'selected' : '' ?>>Capacity High–Low</option>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<?php
// Display status messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        echo '<div class="alert alert-success">Venue created successfully.</div>';
    } elseif ($_GET['status'] === 'updated') {
        echo '<div class="alert alert-success">Venue updated successfully.</div>';
    } elseif ($_GET['status'] === 'deleted') {
        echo '<div class="alert alert-success">Venue deleted successfully.</div>';
    } elseif ($_GET['status'] === 'error') {
        echo '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    }
}

$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count query for total venues after filters
$countQuery = "SELECT COUNT(*) AS total FROM venues WHERE 1=1";
$countParams = [];
$countTypes = '';

if (!empty($_GET['name'])) {
    $countQuery .= " AND name LIKE ?";
    $countParams[] = '%' . $_GET['name'] . '%';
    $countTypes .= 's';
}
if (!empty($_GET['location'])) {
    $countQuery .= " AND location LIKE ?";
    $countParams[] = '%' . $_GET['location'] . '%';
    $countTypes .= 's';
}
if (!empty($_GET['country'])) {
    $countQuery .= " AND country LIKE ?";
    $countParams[] = '%' . $_GET['country'] . '%';
    $countTypes .= 's';
}
if (!empty($_GET['min_capacity']) && is_numeric($_GET['min_capacity'])) {
    $countQuery .= " AND capacity >= ?";
    $countParams[] = intval($_GET['min_capacity']);
    $countTypes .= 'i';
}
if (!empty($_GET['max_capacity']) && is_numeric($_GET['max_capacity'])) {
    $countQuery .= " AND capacity <= ?";
    $countParams[] = intval($_GET['max_capacity']);
    $countTypes .= 'i';
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$total_results = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$total_pages = ceil($total_results / $per_page);

$venueQuery = "SELECT v.*, 
               (SELECT COUNT(*) FROM events WHERE venue_id = v.id) AS events_count,
               (SELECT SUM(b.tickets) FROM bookings b JOIN events e ON b.event_id = e.id WHERE e.venue_id = v.id) AS total_bookings
               FROM venues v WHERE 1=1";
$parameters = [];
$types = '';

// Filters
if (!empty($_GET['name'])) {
    $venueQuery .= " AND v.name LIKE ?";
    $parameters[] = '%' . $_GET['name'] . '%';
    $types .= 's';
}

if (!empty($_GET['location'])) {
    $venueQuery .= " AND v.location LIKE ?";
    $parameters[] = '%' . $_GET['location'] . '%';
    $types .= 's';
}

if (!empty($_GET['country'])) {
    $venueQuery .= " AND v.country LIKE ?";
    $parameters[] = '%' . $_GET['country'] . '%';
    $types .= 's';
}

if (!empty($_GET['min_capacity']) && is_numeric($_GET['min_capacity'])) {
    $venueQuery .= " AND v.capacity >= ?";
    $parameters[] = intval($_GET['min_capacity']);
    $types .= 'i';
}

if (!empty($_GET['max_capacity']) && is_numeric($_GET['max_capacity'])) {
    $venueQuery .= " AND v.capacity <= ?";
    $parameters[] = intval($_GET['max_capacity']);
    $types .= 'i';
}

// Sorting
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'name_asc':
            $venueQuery .= " ORDER BY v.name ASC";
            break;
        case 'name_desc':
            $venueQuery .= " ORDER BY v.name DESC";
            break;
        case 'capacity_asc':
            $venueQuery .= " ORDER BY v.capacity ASC";
            break;
        case 'capacity_desc':
            $venueQuery .= " ORDER BY v.capacity DESC";
            break;
    }
} else {
    $venueQuery .= " ORDER BY v.name ASC"; // Default sort
}

$venueQuery .= " LIMIT ? OFFSET ?";
$parameters[] = $per_page;
$parameters[] = $offset;
$types .= 'ii';

// Prepare and bind
$venueStmt = $conn->prepare($venueQuery);
if (!empty($parameters)) {
    $venueStmt->bind_param($types, ...$parameters);
}
$venueStmt->execute();
$venues = $venueStmt->get_result();

if ($venues->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Country</th>
                    <th>Capacity</th>
                    <th>Events</th>
                    <th>Bookings</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($venue = $venues->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($venue['name']) . '</td>';
        echo '<td>' . htmlspecialchars($venue['location']) . '</td>';
        echo '<td>' . htmlspecialchars($venue['country']) . '</td>';
        echo '<td>' . number_format($venue['capacity']) . '</td>';
        echo '<td>' . ($venue['events_count'] ?? 0) . '</td>';
        echo '<td>' . number_format($venue['total_bookings'] ?? 0) . '</td>';
        
        // Actions
        echo '<td class="actions">';
        echo '<a href="update_venue.php?id=' . $venue['id'] . '" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i> Edit</a>';
        echo '<a href="../venues/delete.php?id=' . $venue['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this venue? This will also delete all associated events.\')"><i class="fas fa-trash"></i> Delete</a>';
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
            $url = htmlspecialchars($_SERVER["PHP_SELF"] . '?' . http_build_query($query));
            $active = ($i == $page) ? "active" : '';
            echo '<a href="' . $url . '" class="' . $active . '">' . $i . '</a>';
        }
        echo '</div>';
    }
} else {
    echo '<div class="no-results">';
    echo '<i class="fas fa-map-marker-alt fa-3x"></i>';
    echo '<p>No venues found matching your criteria.</p>';
    echo '</div>';
}
?>

<div class="card">
    <div class="card-header">
        <h3>Create New Venue</h3>
    </div>
    <div class="card-body">
        <form action="../venues/create.php" method="POST" class="form">
            <div class="form-group">
                <label for="name">Venue Name</label>
                <input type="text" id="name" name="name" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="country">Country</label>
                <select id="country" name="country" class="form-select" required>
                    <option value="">Select a country</option>
                    <?php 
                    require_once("../misc/countries.list.php");
                    foreach($countries as $country) {
                        echo '<option value="' . htmlspecialchars($country) . '">' . htmlspecialchars($country) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="capacity">Capacity</label>
                <input type="number" id="capacity" name="capacity" min="1" max="1000000" class="form-input" required>
            </div>
            
            <button type="submit" name="submit" class="btn btn-primary">Create Venue</button>
        </form>
    </div>
</div>

</div> <!-- Close dashboard-container -->
</body>
<?php include_once("../footer.php");?>
</html>


