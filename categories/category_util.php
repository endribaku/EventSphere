<?php 
function getCategoryByID($conn, $category_id) {
    $categoryQuery = "SELECT * FROM event_categories WHERE id = ?";
    $categoryStmt = $conn->prepare($categoryQuery);
    $categoryStmt->bind_param("i", $category_id);
    $categoryStmt->execute();
    return $categoryStmt->get_result();
 }

?>