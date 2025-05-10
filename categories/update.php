<?php 
require_once("../admin/admin_auth.php");
require_once("../php/db.php");

if(isset($_POST["submit"])) {
    $category_id = $_POST["id"];
    $category_name = $_POST["name"];
    
    $insertCategoryQuery = "UPDATE event_categories SET name= ? WHERE id = ?";
    $insertCategoryStmt = $conn->prepare($insertCategoryQuery);
    $insertCategoryStmt->bind_param("si", $category_name, $category_id);
    
    if($insertCategoryStmt->execute()) {
        header("Location: ../admin/categories.php");
        exit();
    } else {
        die("Failed to update category.");
    }
} else {
    die("Update form hasn't been submitted yet");
}

?>