<?php
require_once("../php/db.php");

$folder = "../images/events/";
$files = glob($folder . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);


$dbImages = [];
$result = mysqli_query($conn, "SELECT image FROM events WHERE image IS NOT NULL");
while ($row = mysqli_fetch_assoc($result)) {
    $dbImages[] = realpath("../" . $row['image']);
}


foreach ($files as $file) {
    if (!in_array(realpath($file), $dbImages)) {
        unlink($file);
        echo "Deleted orphan image: " . basename($file) . "<br>";
    }
}
?>
