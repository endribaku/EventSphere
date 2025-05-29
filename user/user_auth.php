<?php

session_start();
require_once("../php/restore_session.php");

if(!isset($_SESSION["user_id"], $_SESSION["user_name"], 
$_SESSION["user_role"], $_SESSION["user_email"], $_SESSION["user_token"])
|| $_SESSION["user_role"] !== "user") {
    include_once("../php/logout.php");
    exit();
}
?>

