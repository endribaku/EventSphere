<?php
    require_once("organizer_auth.php");
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
</head>

<?php
    require_once("organizer_header.php");
    include_once("../events/read.php");

    echo "<table>
        <tr>
            <th> Event Name </th>
            <th> Event Date </th>
            <th> Location </th>
            <th> Category </th>
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

        echo "<td><strong>Venue:</strong> " . htmlspecialchars($venue['name']) . "</td>";
        
        // category add
        $categoryQuery = "SELECT * from event_categories WHERE id = ?";
        $categoryStmt = mysqli_prepare($conn, $categoryQuery);
        mysqli_stmt_bind_param($categoryStmt, "i", $event["category_id"]);
        mysqli_stmt_execute($categoryStmt);

        $categoryResult = mysqli_stmt_get_result($categoryStmt);
        $category = mysqli_fetch_assoc($categoryResult);

        echo "<td>".htmlspecialchars($category["name"])."</td>";

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
        echo "<a href='update_event.php?id=" . $event['id'] . "'>Edit</a> | ";
        echo "<a href='../events/delete.php?id=" . $event['id'] . "'>Delete</a>";
        echo "</div>";
        echo "</td>";

        echo "</tr>";
    }
    echo "</table>";

    
?>