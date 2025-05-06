<?php
    require_once("user_auth.php");
    include_once("../php/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Events</title>

</head>




<?php
   include_once("user_header.php");
?>


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
            <option value="date_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_asc') echo 'selected'; ?>> Ascending</option>
            <option value="date_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_desc') echo 'selected'; ?>> Descending</option>
        </select>

        <button type="submit">Filter</button>
    </form>
</div>

<?php
   $searchBar = isset($_GET["search"]) ? $_GET["search"] : "";
   $category = isset($_GET["category"]) ? $_GET["category"] : "";
   $date_filter = isset($_GET["date_filter"]) ? $_GET["date_filter"] : "";
   $sort = isset($_GET["sort"]) ? $_GET["sort"] : "";

   // filter sql query logic
   $filterQuery = "SELECT e.*, c.name AS category_name FROM events e, event_categories c WHERE 1=1 AND e.category_id = c.id";
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

   switch ($sort) {
    case 'date_asc':
        $filterQuery .= " ORDER BY e.date ASC";
        break;
    case 'date_desc':
        $filterQuery .= " ORDER BY e.date DESC";
        break;
    default:
        // Default sorting (you can change this)
        $filterQuery .= " ORDER BY e.date ASC";
        break;
    }

    $stmt = $conn->prepare($filterQuery);
    if(!empty($parameters)) {
        $stmt->bind_param($types, ...$parameters);
    }

    $stmt->execute();
    $result = $stmt->get_result();


    if ($result->num_rows > 0) {
        echo '<div class="events-container">';
        while ($event = $result->fetch_assoc()) {
            echo '<div class="event-card" onclick="window.location.href='."'event_details.php?id=".$event['id']."'".'">';
            echo '<h3>' . htmlspecialchars($event['title']) . '</h3>';
            echo '<p>Category: ' . htmlspecialchars($event['category_name']) . '</p>';
            echo '<p>Date: ' . htmlspecialchars($event['date']) . '</p>';
            echo '<p>' . htmlspecialchars($event['description']) . '</p>';
            echo '</div>';
        }
        echo '</div>';
        echo '</body>';
        echo '</html>';
    } else {
        echo '<p>No events found matching your criteria.</p>';
    }
?>







