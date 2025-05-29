<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo '<div class="alert alert-danger">Invalid venue ID.</div>';
    echo '<a href="venues.php" class="btn btn-primary">Back to Venues</a>';
    exit();
}

$venue_id = $_GET["id"];
$venueQuery = "SELECT * from venues WHERE id = ?";
$venueStmt = $conn->prepare($venueQuery);
$venueStmt->bind_param("i", $venue_id);
$venueStmt->execute();
$venueResult = $venueStmt->get_result();

if($venueResult->num_rows <= 0) {
    echo '<div class="alert alert-danger">Venue not found.</div>';
    echo '<a href="venues.php" class="btn btn-primary">Back to Venues</a>';
    exit();
}

$venueResult = $venueResult->fetch_assoc();
require_once("../misc/countries.list.php");
?>

<div class="card">
    <div class="card-header">
        <h2>Update Venue: <?php echo htmlspecialchars($venueResult["name"]); ?></h2>
        <a href="venues.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Venues</a>
    </div>
    <div class="card-body">
        <form action="../venues/update.php" method="POST" class="form">
            <input type="hidden" name="id" value="<?php echo (int)$venueResult['id']; ?>">
            
            <div class="form-group">
                <label for="name">Venue Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($venueResult["name"]); ?>" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($venueResult["location"]); ?>" class="form-input" required>
                <small class="form-text">Address or area of the venue</small>
            </div>
            
            <div class="form-group">
                <label for="country">Country</label>
                <select id="country" name="country" class="form-select" required>
                    <?php 
                    foreach($countries as $country) {
                        $selected = ($country == $venueResult["country"]) ? "selected": "";
                        echo '<option value="' . htmlspecialchars($country) . '"' . $selected . '>' . htmlspecialchars($country) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="capacity">Capacity</label>
                <input type="number" id="capacity" name="capacity" min="1" max="1000000" value="<?php echo (int)$venueResult["capacity"]; ?>" class="form-input" required>
                <small class="form-text">Maximum number of attendees</small>
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">Update Venue</button>
                <a href="venues.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</div> <!-- Close dashboard-container -->
</body>
<?php include_once("../footer.php");?>
</html>

