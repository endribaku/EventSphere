<?php 
require_once("../admin/admin_auth.php");
require_once("../php/db.php");
?>

<?php 
   $result = mysqli_query($conn, "SELECT * FROM event_categories");
?>


