<?php
session_start();

unset($_SESSION["user_id"], $_SESSION["user_email"], 
$_SESSION["user_password"], $_SESSION["user_name"], $_SESSION["user_role"], $_SESSION["user_token"]);

session_destroy();

header("Location: ../login.html");
exit();
?>
