<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"], $_SESSION["user_name"], $_SESSION["user_role"], $_SESSION["user_email"])) {
    if (isset($_COOKIE["remember_token"])) {
        require_once("db.php"); // Adjust path as needed

        $token = $_COOKIE["remember_token"];
        $query = "SELECT id, name, email, role FROM users WHERE remember_token = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"];
            $_SESSION["user_email"] = $user["email"];
            $_SESSION["user_role"] = $user["role"];
            $_SESSION["user_token"] = bin2hex(random_bytes(32)); // Only this one for restored sessions
        }

        mysqli_stmt_close($stmt);
    }
}
?>
