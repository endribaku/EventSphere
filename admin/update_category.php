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
</html>

