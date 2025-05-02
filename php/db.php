<?php
    $db_server = "localhost";
    $db_user= "root";
    $db_pass= "";
    $db_name= "event_booking";
   
    if($conn = mysqli_connect(
                        $db_server,
                        $db_user,
                        $db_pass,
                        $db_name
    )) {
       
    } else {
        die(''. mysqli_connect_error());
    }

?>