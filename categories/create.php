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


// check to create category
// heck if category already exists
$checkQuery = "SELECT id FROM event_categories WHERE name = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("s", $name);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    header("Location: ../admin/categories.php?status=exists");
    exit();
}
$checkStmt->close();

$insertCategoryQuery=  "INSERT INTO event_categories (name) VALUES (?)";
$insertCategoryStmt = $conn->prepare($insertCategoryQuery);
$insertCategoryStmt->bind_param("s", $name);

if($insertCategoryStmt->execute()) {
    header("Location: ../admin/categories.php?status=created");
    exit();
} else {
    die("Event couldn't be inserted successfully");
}

?>