<?php 
session_start();
if (
    !isset($_SESSION["user_name"], $_SESSION["user_email"], $_SESSION["user_id"], $_SESSION["user_token"]) ||
    $_SESSION["user_role"] !== "admin"
) {
    header("Location: ../php/logout.php");
    exit();
}

require_once("../php/db.php");

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'admin'");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_unset();
    session_destroy();
    header("Location: ../login.html");
    exit();
}


?>


