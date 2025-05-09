

<?php 
    // authentication
    require_once("../admin/admin_auth.php");
    require_once("../php/db.php");

    // finding user to update
    if(!isset($_POST["id"])) {
        header("Location: ../admin/users.php");
        exit();
    }

    

    


?>



