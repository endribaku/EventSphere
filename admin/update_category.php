<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../categories/category_util.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo '<div class="alert alert-danger">Invalid category ID.</div>';
    echo '<a href="categories.php" class="btn btn-primary">Back to Categories</a>';
    exit();
}

$category_id = $_GET["id"];
$categoryResult = getCategoryByID($conn, $category_id);

if(!$categoryResult->num_rows > 0) {
    echo '<div class="alert alert-danger">Category not found.</div>';
    echo '<a href="categories.php" class="btn btn-primary">Back to Categories</a>';
    exit();
}

$categoryResult = $categoryResult->fetch_assoc();

// Count events using this category
$countQuery = "SELECT COUNT(*) as count FROM events WHERE category_id = ?";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$eventCount = $stmt->get_result()->fetch_assoc()['count'];
?>

<div class="card">
    <div class="card-header">
        <h2>Update Category: <?php echo htmlspecialchars($categoryResult["name"]); ?></h2>
        <a href="categories.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Categories</a>
    </div>
    <div class="card-body">
        <div class="category-info">
            <p><strong>Category ID:</strong> <?php echo $categoryResult["id"]; ?></p>
            <p><strong>Events using this category:</strong> <?php echo $eventCount; ?></p>
        </div>
        
        <form action="../categories/update.php" method="POST" class="form">
            <input type="hidden" name="id" value="<?php echo $categoryResult["id"]; ?>">
            
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($categoryResult["name"]); ?>" class="form-input" required>
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">Update Category</button>
                <a href="categories.php" class="btn btn-secondary">Cancel</a>
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

