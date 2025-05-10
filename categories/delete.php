<?php 
    require_once("../admin/admin_auth.php");
    require_once("../php/db.php");

    if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        header("Location: ../admin/categories.php");
        exit();
    }

    $category_id = $_GET["id"];
    
    $deleteCategoryQuery = "DELETE FROM event_categories WHERE id = ?";
    $deleteCategoryStmt = $conn->prepare($deleteCategoryQuery);
    $deleteCategoryStmt->bind_param("i", $category_id);
    if($deleteCategoryStmt->execute()) {
        header("Location: ../admin/categories.php");
        exit();
    } else {
        die("Failed to delete category");
    }


?>