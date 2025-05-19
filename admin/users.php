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

$userQuery = "SELECT * FROM users WHERE 1=1";
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
            <th> Role </th>
            <th> Actions </th>
           
        </tr>";
while ($user = $users->fetch_assoc()) {
    echo "<tr>";
    echo "<td>". $user["id"] ."</td>";
    echo "<td>". $user["name"] ."</td>";
    echo "<td>". $user["email"] ."</td>";
    echo "<td>". strtoupper($user["role"]) . "</td>";

    // actions
    echo "<td>";
    echo '<button> <a href="update_user.php?id='. $user["id"].' "> Update </button>';
    echo '<button> <a href="../userCrud/delete.php?id='. $user["id"].' ">Delete</button>';
    echo "</tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";

// Optional: show feedback
if (isset($_GET['success'])) echo "<p style='color: green;'>✅ User created successfully.</p>";
if (isset($_GET['error'])) echo "<p style='color: red;'>❌ Failed to create user. Please check inputs.</p>";

?>

<h3>Create New User</h3>
<form method="POST" action="../userCrud/create.php" style="margin: 20px 0; display: flex; flex-direction: column; gap: 10px; max-width: 400px;">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email Address" required>
    <input type="password" name="password" placeholder="Password" required>
    
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="user">User</option>
        <option value="organizer">Organizer</option>
        <option value="admin">Admin</option>
    </select>
    
    <button type="submit">Create User</button>
</form>