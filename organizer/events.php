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
            <th> Status </th>
            <th> Actions </th>
        </tr>";
    while($event = mysqli_fetch_assoc($eventResults)) {
        echo "<tr>";
        echo "<th>".$event["title"]."</th>";
        echo "<th>".$event["date"]."</th>";
        echo "<th>".$event["location"]."<th";
        echo "</tr>";
    }

    
?>