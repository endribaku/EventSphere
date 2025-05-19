<?php
    require_once("../admin/admin_auth.php"); 
    require_once("../php/db.php");

    if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        echo "Invalid user ID";
        exit();
    }

    $id = $_GET["id"];
    $deleteUserQuery = "DELETE FROM users WHERE id = ?";
    $deletestmt = $conn->prepare($deleteUserQuery);
    $deletestmt->bind_param("i", $id);

    if($deletestmt->execute()) {
        header("Location: ../admin/users.php?delete_success=1");
        exit();
    } else {
        header("Location: ../admin/users.php?error=1");
        exit();
    }
?>