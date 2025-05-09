<?php 
require_once("admin_auth.php");
require_once("admin_header.php");

require_once("../php/db.php");

    if(!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        
        header("Location: ../admin/users.php");
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
        echo "No user that matches this id";
        exit();
    }
?>


<form action="../userCrud/update.php" method="POST">
    <div class="form-group">

            <input type="hidden" name="id" value="<?php echo (int)$userAttributes['id']; ?>">
            <div class="form-group">
                <label for="name">Update Name: </label>
                <input type="text" name="name" value= "<?php echo htmlspecialchars($userAttributes['name']);?>" id="name" class="form-input">
            </div>

            <div class="form-group">
                <label for="pwd">Update Password: Leave blank to keep current</label>
                <input type="password" name="pwd" id="pwd" class="form-input">
            </div>
        
            <div class="form-group">
                <label for="role">Update Role:</label>
                <select name="role" id="role" class="form-select">
                    <option value="user" <?php if($userAttributes['role'] == "user") echo "selected"?>>User</option>
                    <option value="organizer" <?php if($userAttributes['role'] == "organizer") echo "selected"?>>Organizer</option>
                    <option value="admin" <?php if($userAttributes['role'] == "admin") echo "selected"?> >Admin</option>
                </select>
            </div>
        
            <input type="submit" value="Update User" class="btn btn-submit">
    </div>
</form>
