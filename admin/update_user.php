<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");

if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    echo '<div class="alert alert-danger">Invalid user ID.</div>';
    echo '<a href="../admin/users.php" class="btn btn-primary">Back to Users</a>';
    exit();
} 

$userQuery = "SELECT * from users WHERE id = ?";
$query = $conn->prepare($userQuery);
$query->bind_param("i", $_GET["id"]);
$query->execute();
$result = $query->get_result();

if($result->num_rows > 0) {
    $userAttributes = $result->fetch_assoc();
} else {
    echo '<div class="alert alert-danger">No user found with this ID.</div>';
    echo '<a href="../admin/users.php" class="btn btn-primary">Back to Users</a>';
    exit();
}
?>

<div class="card">
    <div class="card-header">
        <h2>Update User: <?php echo htmlspecialchars($userAttributes['name']); ?></h2>
        <a href="users.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Users</a>
    </div>
    <div class="card-body">
        <form action="../userCrud/update.php" method="POST" class="form">
            <input type="hidden" name="id" value="<?php echo (int)$userAttributes['id']; ?>">
            
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($userAttributes['name']); ?>" class="form-input" required>
            </div>

            <div class="form-group">
                <label for="email">Email (readonly)</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userAttributes['email']); ?>" class="form-input" readonly>
            </div>

            <div class="form-group">
                <label for="pwd">Password (Leave blank to keep current)</label>
                <input type="password" id="pwd" name="pwd" class="form-input">
                <small class="form-text">Only fill this field if you want to change the user's password.</small>
            </div>
        
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-select">
                    <option value="user" <?php if($userAttributes['role'] == "user") echo "selected"; ?>>User</option>
                    <option value="organizer" <?php if($userAttributes['role'] == "organizer") echo "selected"; ?>>Organizer</option>
                    <option value="admin" <?php if($userAttributes['role'] == "admin") echo "selected"; ?>>Admin</option>
                </select>
            </div>
        
            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">Update User</button>
                <a href="users.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</div> <!-- Close dashboard-container -->
</body>
<?php include_once("../footer.php");?>

</html>

