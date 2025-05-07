<?php
include_once("db.php");
session_destroy();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['emailPHP']);
    $password = $_POST['passwordPHP'];


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
