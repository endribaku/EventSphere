
<?php 
    // authentication
    require_once("../admin/admin_auth.php");
    require_once("../php/db.php");

    // finding user to update
    if(!isset($_POST["id"])) {
        header("Location: ../admin/users.php");
        exit();
    }
    
    if(isset($_POST["submit"])) {
        $user_id = $_POST["id"];
    $user_name = $_POST["name"];
    $user_email = $_POST["email"];
    $user_password = $_POST["pwd"];
    $user_role = $_POST["role"];
    $userPasswordClause = "";
    
    if (!empty($user_password)) {
        $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssssi", $user_name, $user_email, $user_role, $hashed_password, $user_id);
    } else {
        $updateQuery = "UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssi", $user_name, $user_email, $user_role, $user_id);
    }

    if ($stmt->execute()) {
        header("Location: ../admin/users.php");
        exit();
    } else {
        die("Failed to update user.");
    }
    }
    

?>