<?php 
require_once("admin_auth.php");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
</head>

<?php 
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../categories/read.php");

echo "<table>
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Actions</th>
  </tr>";

while($row = $result->fetch_assoc() ) {
    echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>
                <a href='update_category.php?id={$row['id']}'>Edit</a> |
                <a href='../categories/delete.php?id={$row['id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a>
              </td>";
        echo "</tr>";
}
echo "</table>";
?>

<form action="../categories/create.php" class="category-creation-form" method="POST">
    <h2>Create Category</h2>

    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" name="name" id="name" required>
    </div>

    <button type="submit" name="submit">Create Category</button>
</form>