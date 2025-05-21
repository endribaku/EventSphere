<?php
session_start();


if(isset($_SESSION["user_id"], $_SESSION["user_email"], 
$_SESSION["user_password"], $_SESSION["user_name"], $_SESSION["user_role"], $_SESSION["user_token"]) && $_GET["token"] === $_SESSION["user_token"]) {
    session_unset();
    session_destroy();
    header("Location: ../login.html");
    exit();
} else {
    // Invalid token or not logged in
    header("Location: ../login.html");
    exit();
}


// gonna add token here


