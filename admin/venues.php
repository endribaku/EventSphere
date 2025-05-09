<?php 
require_once("admin_auth.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venues</title>
</head>

<?php 
require_once("admin_header.php");
require_once("../php/db.php");

$venueQuery = "SELECT * from venues";
$venueResult =  $conn->prepare($venueQuery);
$venueResult->execute();
$venue = $venueResult->get_result();

echo '<div class="venue-table">';
echo "<table>
        <tr>
            <th> Name </th>
            <th> Location </th>
            <th> Country </th>
            <th> Capacity </th>
            <th> Actions </th>
        </tr>";
while($venueRow = $venue->fetch_assoc()) {
    echo "<tr>";
    echo "<td>".$venueRow['name']."</th>";
    echo "<td>".$venueRow['location']."</th>";
    echo "<td>".$venueRow['country']."</th>";
    echo "<td>".$venueRow['capacity']."</th>";

    // actions
    echo "<td>";
    echo '<button> <a href="update_venue.php?id='.$venueRow['id'].'"> Update</button>';
    echo '<button> <a href="../venues/delete.php?id='.$venueRow['id'].'"> Delete </button>';
    echo "</td>";

    echo "</tr>";
}

echo "</table>";
echo "</div>";
echo "</body>";
echo "</html>";
?>

<div class="venue-creation-form">
    <h3>Create Venue</h3>
    <form action="../venues/create.php" method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="form-group">
            <label for="name">Location</label>
            <input type="text" name="location" required>
        </div>
        <div class="form-group">
            <?php require_once("../misc/countries.php"); ?>
        </div>
        <div class="form-group">
            <label for="capacity">Capacity</label>
            <input type="number" name="capacity" min="1"  max="1000000" required>
        </div>
        

        <button type="submit" name="submit">Create Venue</button>
    </form>
</div>