<?php 
require_once("../admin/admin_auth.php");
require_once("../php/db.php");
if(!isset($_POST["submit"])) {
    header("Location: ../admin/categories.php");
    exit();
}

if (empty($_POST["name"])) {
    die("Category name cannot be empty.");
}

$name = trim($_POST["name"]);
$insertCategoryQuery=  "INSERT INTO event_categories (name) VALUES (?)";
$insertCategoryStmt = $conn->prepare($insertCategoryQuery);
$insertCategoryStmt->bind_param("s", $name);

if($insertCategoryStmt->execute()) {
    header("Location: ../admin/categories.php");
    exit();
} else {
    die("Event couldn't be inserted successfully");
}

?>