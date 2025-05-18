<?php
    require_once("organizer_auth.php");
    require_once("../php/db.php");
    require_once("organizer_header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
</head>

<div class="search-form">
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
            <option value="this_month" <?php if(isset($_GET['date_filter']) && $_GET['date_filter'] == 'this_month') echo 'selected'; ?>>This Month</option>
        </select>

        <select name="sort">
            <option value="">Sort By</option>
            <option value="date_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_asc') echo 'selected'; ?>> Ascending</option>
            <option value="date_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == 'date_desc') echo 'selected'; ?>> Descending</option>
        </select>

        <input type="number" step="0.01" name="min_tickets" placeholder="Min Tickets Sold" value="<?php echo isset($_GET['min_tickets']) ? htmlspecialchars($_GET['min_tickets']) : ''; ?>">
        <input type="number" step="0.01" name="max_tickets" placeholder="Max Tickets Sold" value="<?php echo isset($_GET['max_tickets']) ? htmlspecialchars($_GET['max_tickets']) : ''; ?>">

        <input type="number" step="0.01" name="min_price" placeholder="Min Price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
        <input type="number" step="0.01" name="max_price" placeholder="Max Price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">

        <button type="submit">Filter</button>
    </form>
</div>

<?php
    
    // include_once("../events/read.php");
    $searchBar = isset($_GET["search"]) ? $_GET["search"] : "";
    $category = isset($_GET["category"]) ? $_GET["category"] : "";
    $date_filter = isset($_GET["date_filter"]) ? $_GET["date_filter"] : "";
    $sort = isset($_GET["sort"]) ? $_GET["sort"] : "";
    $min_tickets = isset($_GET["min_tickets"]) && is_numeric($_GET["min_tickets"]) ? floatval($_GET["min_tickets"]) : null;
    $max_tickets = isset($_GET["max_tickets"]) && is_numeric($_GET["max_tickets"]) ? floatval($_GET["max_tickets"]) : null;
    $min_price = isset($_GET["min_price"]) && is_numeric($_GET["min_price"]) ? floatval($_GET["min_price"]) : null;
    $max_price = isset($_GET["max_price"]) && is_numeric($_GET["max_price"]) ? floatval($_GET["max_price"]) : null;



    $filterQuery = "SELECT * from events e where organizer_id = ?";
    $parameters = [$_SESSION["user_id"]];
    $types = 'i'; 

    
    if(!empty($searchBar)) {
        $filterQuery .= " AND (title LIKE ?) ";  
        $searchTerm = "%$searchBar%";
        $parameters[] = $searchTerm;
        $types .= "s";
    }

    if(!empty($category)) {
        $filterQuery .= " AND category_id = ?";
        $parameters[] = $category;
        $types .= "i";
       }
       $currentDate = date('Y-m-d');
       if(!empty($date_filter)) {
            switch($date_filter) {
                case "today":
                    $filterQuery .= " AND date = ?";
                    $parameters[] = $currentDate;
                    $types .= 's';
                    break;
                case "upcoming":
                    $filterQuery .= " AND date >= ?";
                    $parameters[] = $currentDate;
                    $types .= 's';
                    break;
                
                case 'this_month':
                    $firstDayOfMonth = date('Y-m-01');
                    $lastDayOfMonth = date('Y-m-t');
                    $filterQuery .= " AND date BETWEEN ? AND ?";
                    $parameters[] = $firstDayOfMonth;
                    $parameters[] = $lastDayOfMonth;
                    $types .= "ss";
                    break;
                }
       }

    if(!is_null($min_tickets) || !is_null($max_tickets)) {
        $filterQuery .= " AND id IN (
        SELECT event_id FROM bookings GROUP BY event_id HAVING ";

            if (!is_null($min_tickets)) {
                $filterQuery .= " SUM(tickets) >= ? ";
                $parameters[] = $min_tickets;
                $types .= 'i';
            }

            if (!is_null($max_tickets)) {
                if (!is_null($min_tickets)) {
                    $filterQuery .= " AND ";
                }
                $filterQuery .= " SUM(tickets) <= ? ";
                $parameters[] = $max_tickets;
                $types .= 'i';
            }
        
            $filterQuery .= ")";
    }
    
    
    
    if (!empty($min_price)) {
        $filterQuery .= " AND price >= ?";
        $parameters[] = $min_price;
        $types .= 'd';
    }
    
    if (!empty($max_price)) {
            $filterQuery .= " AND price <= ?";
            $parameters[] = $max_price;
            $types .= 'd';
    }

    switch ($sort) {
        case 'date_asc':
            $filterQuery .= " ORDER BY date ASC";
            break;
        case 'date_desc':
            $filterQuery .= " ORDER BY date DESC";
            break;
        default:
            // Default sorting (you can change this)
            $filterQuery .= " ORDER BY date ASC";
            break;
        }


    $stmt = mysqli_prepare($conn, $filterQuery);
    mysqli_stmt_bind_param($stmt,$types, ...$parameters);
    mysqli_stmt_execute($stmt);
    $eventResults = mysqli_stmt_get_result($stmt);


    if($eventResults->num_rows > 0) {

    echo "<table>
        <tr>
            <th> Event Name </th>
            <th> Event Date </th>
            <th> Location </th>
            <th> Category </th>
            <th> Tickets Sold </th>
            <th> Price </th>
            <th> Status </th>
            <th> Actions </th>
        </tr>";
    

    while($event = mysqli_fetch_assoc($eventResults)) {
        echo "
        <tr class='event'>";
        echo "<td>".htmlspecialchars($event['title'])."</td>";
        echo "<td>".htmlspecialchars($event["date"])."</td>";
        
        $venueQuery = "SELECT * from venues WHERE id = ?";
        $venueStmt = mysqli_prepare($conn, $venueQuery);
        mysqli_stmt_bind_param($venueStmt, "i", $event['venue_id']);
        mysqli_stmt_execute($venueStmt);

        $venueResult = mysqli_stmt_get_result($venueStmt);
        $venue = mysqli_fetch_assoc($venueResult);

        echo "<td>" . htmlspecialchars($venue['name']) . "</td>";
        
        // category add
        $categoryQuery = "SELECT * from event_categories WHERE id = ?";
        $categoryStmt = mysqli_prepare($conn, $categoryQuery);
        mysqli_stmt_bind_param($categoryStmt, "i", $event["category_id"]);
        mysqli_stmt_execute($categoryStmt);

        $categoryResult = mysqli_stmt_get_result($categoryStmt);
        $category = mysqli_fetch_assoc($categoryResult);

        echo "<td>".htmlspecialchars($category["name"])."</td>";
        // tickets Sold
        $ticketsQuery = "SELECT COALESCE(SUM(tickets), 0) AS tickets_sold FROM bookings WHERE event_id = ?";
        $ticketStmt = mysqli_prepare($conn, $ticketsQuery);
        mysqli_stmt_bind_param($ticketStmt, "i", $event['id']);
        mysqli_stmt_execute($ticketStmt);
        $ticketResult = mysqli_stmt_get_result($ticketStmt);
        $ticket = mysqli_fetch_assoc($ticketResult);
        echo "<td> ".htmlspecialchars($ticket["tickets_sold"]). " / ".$venue["capacity"]."</td>";
        // ticket price
        
        echo "<td> $" . number_format($event['price'], 2)."</td>";
        
        // for status
        echo "<td>";
        $today = date("Y-m-d");
        $eventDate = $event["date"];
        if($eventDate > $today) {
            echo "Upcoming";
        } else if($eventDate == $today) {
            echo "Ongoing";
        } else {
            echo "Past";
        }
        echo "</td>";
        // for actions
        echo "<td>";
        echo "<a href='event_details.php?id=" . $event['id'] . "'>Event Details</a> | ";
        echo "<a href='view_bookings.php?id=" . $event['id'] . "'>View Bookings</a> | ";
        echo "<a href='update_event.php?id=" . $event['id'] . "'>Edit</a> | ";
        echo "<a href='../events/delete.php?id=" . $event['id'] . "'>Cancel</a>";
        echo "</div>";
        echo "</td>";

        echo "</tr>";
    }
    echo "</table>";

    }
    else echo "No events found";

?>