<?php 
require_once("admin_auth.php");

require_once("../php/db.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
</head>



<?php 
require_once("admin_header.php");
?>

<form method="GET" style="margin: 20px 0;">
    <input type="text" name="name" placeholder="Search by name" value="<?= htmlspecialchars($nameFilter) ?>">
    <input type="text" name="email" placeholder="Search by email" value="<?= htmlspecialchars($emailFilter) ?>">
    <button type="submit">Filter</button>
</form>
<?php

$nameFilter = isset($_GET['name']) ? $_GET['name'] : '';
$emailFilter = isset($_GET['email']) ? $_GET['email'] : '';

$userQuery = "SELECT * FROM users WHERE role != 'admin'";
$params = [];
$types = "";

if (!empty($nameFilter)) {
    $userQuery .= " AND name LIKE ?";
    $params[] = "%$nameFilter%";
    $types .= "s";
}
if (!empty($emailFilter)) {
    $userQuery .= " AND email LIKE ?";
    $params[] = "%$emailFilter%";
    $types .= "s";
}

$userStmt = $conn->prepare($userQuery);
if (!empty($params)) {
    $userStmt->bind_param($types, ...$params);
}
$userStmt->execute();
$users = $userStmt->get_result();

echo "<table>
        <tr>
            <th> Id </th>
            <th> Name </th>
            <th> Email </th>
            <th> Actions </th>
           
        </tr>";
while ($user = $users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>". $user["id"] ."</td>";
    echo "<td>". $user["name"] ."</td>";
    echo "<td>". $user["email"] ."</td>";

    // actions
    echo "<td>";
    echo '<button> <a href="update_user.php?id='. $user["id"].' "> Update </button>';
    echo '<button> <a href="../userCrud/delete.php?id='. $user["id"].' ">Delete</button>';
    echo "</tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";

?>