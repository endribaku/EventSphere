<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
?>

<?php 
$userQuery = "SELECT * FROM users";
$userStmt = $conn->prepare($userQuery);
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