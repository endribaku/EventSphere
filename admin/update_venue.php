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
<footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h2><?= htmlspecialchars($site['company_name']) ?></h2>
                    <p><?= htmlspecialchars($site['footer_text']) ?></p>
                </div>
                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#events">Events</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($site['email']) ?></p>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($site['phone']) ?></p>
                    <div class="social-links">
                        <a href="<?= htmlspecialchars($site['facebook_link']) ?>"><i class="fab fa-facebook-f"></i></a>
                        <a href="<?= htmlspecialchars($site['twitter_link']) ?>"><i class="fab fa-twitter"></i></a>
                        <a href="<?= htmlspecialchars($site['instagram_link']) ?>"><i class="fab fa-instagram"></i></a>
                        <a href="<?= htmlspecialchars($site['linkedin_link']) ?>"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p><?= htmlspecialchars($site['footer_text']) ?></p>
            </div>
        </div>
    </footer>

</html>

