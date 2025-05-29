<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
require_once("../categories/read.php");


?>

<h2 class="section-title">Manage Event Categories</h2>

<?php
// Display status messages
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') {
        echo '<div class="alert alert-success">Category created successfully.</div>';
    } elseif ($_GET['status'] === 'updated') {
        echo '<div class="alert alert-success">Category updated successfully.</div>';
    } elseif ($_GET['status'] === 'deleted') {
        echo '<div class="alert alert-success">Category deleted successfully.</div>';
    } elseif ($_GET['status'] === 'error') {
        echo '<div class="alert alert-danger">An error occurred. Please try again.</div>';
    }
}
if(isset($_SESSION["count_error"])) {
  echo '<div class="alert alert-danger">'.$_SESSION["count_error"].'</div>';
  unset($_SESSION["count_error"]);
}

?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Event Categories</h3>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Events</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($category = $result->fetch_assoc()): 
                                // Count events in this category
                                $countQuery = "SELECT COUNT(*) as count FROM events WHERE category_id = ?";
                                $stmt = $conn->prepare($countQuery);
                                $stmt->bind_param("i", $category['id']);
                                $stmt->execute();
                                $eventCount = $stmt->get_result()->fetch_assoc()['count'];
                            ?>
                            <tr>
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo $eventCount; ?></td>
                                <td class="actions">
                                    <a href="update_category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="../categories/delete.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category? This may affect existing events.')"><i class="fas fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="no-data">No categories found. Create your first category using the form.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-plus-circle"></i> Create Category</h3>
        </div>
        <div class="card-body">
            <form action="../categories/create.php" method="POST" class="form">
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter category name" class="form-input" required>
                    <small class="form-text">Choose a clear and descriptive name for the category</small>
                </div>
                
                <button type="submit" name="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Create Category</button>
            </form>
        </div>
    </div>
</div>
</div>

</div> <!-- Close dashboard-container -->
</body>
<?php include_once("../footer.php");?>

</html>
