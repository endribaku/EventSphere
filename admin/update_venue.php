<?php 
require_once("admin_auth.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Venue</title>
</head>

<?php 
require_once("admin_header.php");
require_once("../php/db.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: venues.php");
    exit();
}

$venue_id = $_GET["id"];
$venueQuery = "SELECT * from venues WHERE id = ?";
$venueStmt = $conn->prepare($venueQuery);
$venueStmt->bind_param("i", $venue_id);
$venueStmt->execute();
$venueResult = $venueStmt->get_result();

if($venueResult->num_rows <= 0) {
    echo "<h1> No venue found </h1>";
    exit();
}

$venueResult = $venueResult->fetch_assoc();
require_once("../misc/countries.list.php");
?>

<div class="venue-update-form">
    <form action="../venues/update.php" method="POST">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($venueResult["name"])?>" required>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" value="<?php echo htmlspecialchars($venueResult["location"]) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="country">Country</label>
            <select name="country" id="country">
                <?php 
                foreach($countries as $country) {
                    //<option value="Afghanistan">Afghanistan</option>
                    $selected = ($country == $venueResult["country"]) ? "selected": "";
                    echo '<option value="'.$country.'"'." $selected>".$country."</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="capacity">Capacity</label>
            <input type="number" name="capacity" min="1"  max="1000000" value="<?php echo (int) $venueResult["capacity"] ?>" required>
        </div>
    </form>
</div>
