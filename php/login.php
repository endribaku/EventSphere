<?php
include_once("db.php");
session_destroy();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['emailPHP']);
    $password = $_POST['passwordPHP'];
    $remember = isset($_POST['remember']);


    $loginQuery = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $loginQuery);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);


    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_token'] = bin2hex(random_bytes(32));
            $_SESSION['user_password'] = $user['password'];
            $_SESSION['error'] = "";
            $_SESSION['success'] = "";
            if (!empty($_POST["remember"])) {
                $remember_token = bin2hex(random_bytes(32));
            
                // Save token in DB
                $updateQuery = "UPDATE users SET remember_token = ? WHERE id = ?";
                $updateStmt = mysqli_prepare($conn, $updateQuery);
                mysqli_stmt_bind_param($updateStmt, "si", $remember_token, $user['id']);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            
                // Set cookie (30 days)
                setcookie("remember_token", $remember_token, time() + (86400 * 30), "/");
            }
            echo $_SESSION['user_role'];
            
        } else {
            echo "Invalid password"; 
        }
    } else {
        echo "Invalid credentials";
    }


    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
