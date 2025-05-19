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

$per_page = 3;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

//total pages count
$countQuery = "SELECT COUNT(*) AS total FROM users WHERE 1=1";
$countParams = [];
$countTypes = "";

if (!empty($nameFilter)) {
    $countQuery .= " AND name LIKE ?";
    $countParams[] = "%$nameFilter%";
    $countTypes .= "s";
}
if (!empty($emailFilter)) {
    $countQuery .= " AND email LIKE ?";
    $countParams[] = "%$emailFilter%";
    $countTypes .= "s";
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$total_users = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$total_pages = ceil($total_users / $per_page);


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


$userQuery .= " LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";


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
echo "<div style='margin-top: 20px;'>Pages: ";
        for ($i = 1; $i <= $total_pages; $i++) {
            $query = $_GET;
            $query['page'] = $i;
            $url = htmlspecialchars($_SERVER["PHP_SELF"] . "?" . http_build_query($query));
            $bold = $i == $page ? "style='font-weight:bold;'" : "";
            echo "<a href='$url' $bold>$i</a> ";
        }
        echo "</div>";


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