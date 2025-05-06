<?php
session_start();

if (
    !isset($_SESSION["user_name"], $_SESSION["user_email"], $_SESSION["user_id"], $_SESSION["user_token"]) ||
    $_SESSION["user_role"] !== "organizer"
) {
    include_once("../php/logout.php");
    exit();
}

?>