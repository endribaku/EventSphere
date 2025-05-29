

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once("db.php");

// neqoftese user ben logout kur shtyp butonin logout
if (
    isset($_SESSION["user_id"], $_SESSION["user_token"]) &&
    isset($_GET["token"]) &&
    $_GET["token"] === $_SESSION["user_token"]
) {
    $userId = $_SESSION["user_id"];
    
    // Clear token in the database   
    $stmt = mysqli_prepare($conn, "UPDATE users SET remember_token = NULL WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    session_unset();
    session_destroy();
    setcookie("remember_token", "", time() - 3600, "/", "", false, true); // Expire the cookie
    
    header("Location: ../login.php?success=logged_out");
    exit();
}

// neqoftese user shkon ne nje dashboard te nje roli te tjeter te joautorizuar do te shkoje te dashboard i tij
if (isset($_SESSION["user_role"])) {
    
    switch ($_SESSION["user_role"]) {
        case "admin":
            header("Location: ../admin/dashboard.php?error=invalid_logout");
            break;
        case "organizer":
            header("Location: ../organizer/dashboard.php?error=invalid_logout");
            break;
        case "user":
            header("Location: ../user/dashboard.php?error=invalid_logout");
            break;
        default:
            header("Location: ../index.php?error=invalid_logout");
    }
    exit();
}

// neqoftese user nuk ka hyre ende ne website do behet redirect te faqja login
header("Location: ../login.php?error=unauthorized");
exit();

?>
