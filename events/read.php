<?php
    include_once("../php/db.php");
    session_start();

    $eventQuery = "SELECT * from events where organizer_id = ?";
    $stmt = mysqli_prepare($conn, $eventQuery);
    mysqli_stmt_bind_param($stmt,"i", $_SESSION["user_id"]);
    mysqli_stmt_execute($stmt);

    $eventResults = mysqli_stmt_get_result($stmt);
?>