<?php 
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");
?>

<h2 class="section-title">Manage Users</h2>

<div class="filter-form">
    <form method="GET" action="users.php">
        <input type="text" name="name" placeholder="Search by name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>" class="form-input">
        <input type="text" name="email" placeholder="Search by email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>" class="form-input">
        <select name="role" class="form-select">
            <option value="">All Roles</option>
            <option value="user" <?= (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : '' ?>>User</option>
            <option value="organizer" <?= (isset($_GET['role']) && $_GET['role'] === 'organizer') ? 'selected' : '' ?>>Organizer</option>
            <option value="admin" <?= (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<?php
$nameFilter = isset($_GET['name']) ? $_GET['name'] : '';
$emailFilter = isset($_GET['email']) ? $_GET['email'] : '';
$roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

//total pages count
$countQuery = "SELECT COUNT(*) AS total FROM users WHERE 1=1";
$countParams = [];
$countTypes = "";

if (!empty($nameFilter)) {
    $countQuery .= " AND name LIKE ?";
    $countParams[] = "%$nameFilter%";
    $countTypes .= "s";
}
if (!empty($emailFilter)) {
    $countQuery .= " AND email LIKE ?";
    $countParams[] = "%$emailFilter%";
    $countTypes .= "s";
}
if (!empty($roleFilter)) {
    $countQuery .= " AND role = ?";
    $countParams[] = $roleFilter;
    $countTypes .= "s";
}

$countStmt = $conn->prepare($countQuery);
if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}
$countStmt->execute();
$total_users = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$total_pages = ceil($total_users / $per_page);


$userQuery = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";


if (!empty($nameFilter)) {
    $userQuery .= " AND name LIKE ?";
    $params[] = "%$nameFilter%";
    $types .= "s";
}
if (!empty($emailFilter)) {
    $userQuery .= " AND email LIKE ?";
    $params[] = "%$emailFilter%";
    $types .= "s";
}
if (!empty($roleFilter)) {
    $userQuery .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= "s";
}

$userQuery .= " ORDER BY id ASC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";


$userStmt = $conn->prepare($userQuery);
if (!empty($params)) {
    $userStmt->bind_param($types, ...$params);
}
$userStmt->execute();
$users = $userStmt->get_result();

// Display success/error messages
if (isset($_GET['success'])) {
    echo '<div class="alert alert-success">User created successfully.</div>';
}
if (isset($_GET['error'])) {
    if($_GET['error']) {
        echo '<div class="alert alert-danger">Trying to create existing user.</div>';
    } else {
        echo '<div class="alert alert-danger">Failed to create user. Please check inputs.</div>';
    }
    
}
if (isset($_GET['update_success'])) {
    echo '<div class="alert alert-success">User updated successfully.</div>';
}
if (isset($_GET['delete_success'])) {
    echo '<div class="alert alert-success">User deleted successfully.</div>';
}



// Display users table
if ($users->num_rows > 0) {
    echo '<div class="table-responsive">';
    echo '<table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    
    while ($user = $users->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $user["id"] . '</td>';
        echo '<td>' . htmlspecialchars($user["name"]) . '</td>';
        echo '<td>' . htmlspecialchars($user["email"]) . '</td>';
        
        // Format role with badge
        $roleBadge = '';
        switch($user["role"]) {
            case 'admin':
                $roleBadge = '<span class="badge badge-admin">Admin</span>';
                break;
            case 'organizer':
                $roleBadge = '<span class="badge badge-organizer">Organizer</span>';
                break;
            default:
                $roleBadge = '<span class="badge badge-user">User</span>';
        }
        echo '<td>' . $roleBadge . '</td>';

        // Actions
        echo '<td class="actions">';
        echo '<a href="update_user.php?id='. $user["id"].'" class="btn btn-sm btn-secondary"><i class="fas fa-edit"></i> Edit</a>';
        echo '<a href="../userCrud/delete.php?id='. $user["id"].'" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this user?\')"><i class="fas fa-trash"></i> Delete</a>';
        echo '</td>';
        
        echo '</tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
    
    // Pagination
    if ($total_pages > 1) {
        echo '<div class="pagination">';
        for ($i = 1; $i <= $total_pages; $i++) {
            $query = $_GET;
            $query['page'] = $i;
            $url = htmlspecialchars($_SERVER["PHP_SELF"] . "?" . http_build_query($query));
            $active = $i == $page ? "active" : "";
            echo '<a href="' . $url . '" class="' . $active . '">' . $i . '</a>';
        }
        echo '</div>';
    }
} else {
    echo '<div class="no-results">';
    echo '<i class="fas fa-users fa-3x"></i>';
    echo '<p>No users found matching your criteria.</p>';
    echo '</div>';
}
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-user-plus"></i> Create New User</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="../userCrud/create.php" class="form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Enter user's full name" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter user's email address" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter a secure password" class="form-input" required>
                <small class="form-text">Password should be at least 8 characters long</small>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="">Select Role</option>
                    <option value="user">User</option>
                    <option value="organizer">Organizer</option>
                    <option value="admin">Admin</option>
                </select>
                <small class="form-text">Select the appropriate role for this user</small>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
        </form>
    </div>
</div>

</div> <!-- Close dashboard-container -->
</body>
<?php include_once("../footer.php");?>

</html>


