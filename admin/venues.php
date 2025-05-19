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
?>

<div class="venue-filter-form">
    <form method="GET" action="venues.php">
        <input type="text" name="name" placeholder="Search by name" value="<?= isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '' ?>">
        <input type="text" name="location" placeholder="Search by location" value="<?= isset($_GET['location']) ? htmlspecialchars($_GET['location']) : '' ?>">
        <input type="text" name="country" placeholder="Search by country" value="<?= isset($_GET['country']) ? htmlspecialchars($_GET['country']) : '' ?>">
        <input type="number" name="min_capacity" placeholder="Min capacity" value="<?= isset($_GET['min_capacity']) ? htmlspecialchars($_GET['min_capacity']) : '' ?>">
        <input type="number" name="max_capacity" placeholder="Max capacity" value="<?= isset($_GET['max_capacity']) ? htmlspecialchars($_GET['max_capacity']) : '' ?>">

        <select name="sort">
            <option value="">Sort By</option>
            <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'name_asc') ? 'selected' : '' ?>>Name A–Z</option>
            <option value="capacity_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'capacity_desc') ? 'selected' : '' ?>>Capacity High–Low</option>
        </select>

        <button type="submit">Filter</button>
    </form>
</div>

<?php
require_once("../php/db.php");

$venueQuery = "SELECT * FROM venues WHERE 1=1";
$parameters = [];
$types = '';

// Filters
if (!empty($_GET['name'])) {
    $venueQuery .= " AND name LIKE ?";
    $parameters[] = '%' . $_GET['name'] . '%';
    $types .= 's';
}

if (!empty($_GET['location'])) {
    $venueQuery .= " AND location LIKE ?";
    $parameters[] = '%' . $_GET['location'] . '%';
    $types .= 's';
}

if (!empty($_GET['country'])) {
    $venueQuery .= " AND country LIKE ?";
    $parameters[] = '%' . $_GET['country'] . '%';
    $types .= 's';
}

if (!empty($_GET['min_capacity']) && is_numeric($_GET['min_capacity'])) {
    $venueQuery .= " AND capacity >= ?";
    $parameters[] = intval($_GET['min_capacity']);
    $types .= 'i';
}

if (!empty($_GET['max_capacity']) && is_numeric($_GET['max_capacity'])) {
    $venueQuery .= " AND capacity <= ?";
    $parameters[] = intval($_GET['max_capacity']);
    $types .= 'i';
}

// Sorting
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'name_asc':
            $venueQuery .= " ORDER BY name ASC";
            break;
        case 'capacity_desc':
            $venueQuery .= " ORDER BY capacity DESC";
            break;
    }
} else {
    $venueQuery .= " ORDER BY name ASC"; // Default sort
}

// Prepare and bind
$venueStmt = $conn->prepare($venueQuery);
if (!empty($parameters)) {
    $venueStmt->bind_param($types, ...$parameters);
}
$venueStmt->execute();
$venue = $venueStmt->get_result();

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
    echo "<td>".$venueRow['name']."</td>";
    echo "<td>".$venueRow['location']."</td>";
    echo "<td>".$venueRow['country']."</td>";
    echo "<td>".$venueRow['capacity']."</dh>";

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