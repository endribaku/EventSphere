<?php 
    require_once("../admin/admin_auth.php");
    require_once("../php/db.php");

    if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        header("Location: ../admin/categories.php");
        exit();
    }

    $category_id = $_GET["id"];
    // count if events are
    $countQuery = "SELECT COUNT(*) as count FROM events WHERE category_id = ?";
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $eventCount = $stmt->get_result()->fetch_assoc()['count'];

    if($eventCount > 0) {
        $_SESSION["count_error"] = "This category cannot be deleted because it still has events assigned to it. Please delete or reassign those events first.";
        header("Location: ../admin/categories.php");
        exit();
    }
    
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