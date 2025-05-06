<?php
    require_once("user_auth.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
</head>

<?php
   include_once("user_header.php");
?>

<h2>Welcome, <?= $_SESSION['user_name']; ?> 👋</h2>