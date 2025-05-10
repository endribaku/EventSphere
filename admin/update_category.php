<?php 
require_once("admin_auth.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: categories.php");
    exit();
}

$category_id = $_GET["id"];
?>

<?php 
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../categories/category_util.php");

$categoryResult = getCategoryByID($conn, $category_id);
if(!$categoryResult->num_rows > 0) {
    echo "Not found"; 
    exit();
}

$categoryResult = $categoryResult->fetch_assoc();
?>

<div class="category-update-form">
    <form action="../categories/update.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $categoryResult["id"]; ?>">
        
        <div class="form-group">
            <label for="name">Update Name:</label>
            <input type="text" name="name" value="<?php echo $categoryResult["name"] ?>" required>
        </div>

        <button type="submit" name="submit" id="submit">Update Category</button>
    </form>
</div>
