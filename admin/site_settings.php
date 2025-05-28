<?php
require_once("admin_auth.php");
require_once("admin_header.php");
require_once("../php/db.php");

// Fetch existing site settings
$result = mysqli_query($conn, "SELECT * FROM site_info LIMIT 1");
$site = mysqli_fetch_assoc($result);

// Update site info on form submit
if (isset($_POST['submit'])) {
    $company = trim($_POST['company_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $footer = trim($_POST['footer_text']);
    $facebook = trim($_POST['facebook_link']);
    $twitter = trim($_POST['twitter_link']);
    $instagram = trim($_POST['instagram_link']);
    $linkedin = trim($_POST['linkedin_link']);

    $sql = "UPDATE site_info SET company_name=?, email=?, phone=?, footer_text=?, facebook_link=?, twitter_link=?, instagram_link=?, linkedin_link=?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssssss", $company, $email, $phone, $footer, $facebook, $twitter, $instagram, $linkedin);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Site settings updated successfully!";
        header("Location: site_settings.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update settings.";
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Site Settings</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; $_SESSION['error'] = ''; ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; $_SESSION['success'] = ''; ?></div>
        <?php endif; ?>

        <form method="POST" class="form">
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" name="company_name" class="form-input" value="<?= htmlspecialchars($site['company_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Contact Email</label>
                <input type="email" name="email" class="form-input" value="<?= htmlspecialchars($site['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Contact Phone</label>
                <input type="text" name="phone" class="form-input" value="<?= htmlspecialchars($site['phone']) ?>" required>
            </div>

            <div class="form-group">
                <label for="footer_text">Footer Text</label>
                <input type="text" name="footer_text" class="form-input" value="<?= htmlspecialchars($site['footer_text']) ?>">
            </div>

            <div class="form-group">
                <label for="facebook_link">Facebook Link</label>
                <input type="text" name="facebook_link" class="form-input" value="<?= htmlspecialchars($site['facebook_link']) ?>">
            </div>

            <div class="form-group">
                <label for="twitter_link">Twitter Link</label>
                <input type="text" name="twitter_link" class="form-input" value="<?= htmlspecialchars($site['twitter_link']) ?>">
            </div>

            <div class="form-group">
                <label for="instagram_link">Instagram Link</label>
                <input type="text" name="instagram_link" class="form-input" value="<?= htmlspecialchars($site['instagram_link']) ?>">
            </div>

            <div class="form-group">
                <label for="linkedin_link">LinkedIn Link</label>
                <input type="text" name="linkedin_link" class="form-input" value="<?= htmlspecialchars($site['linkedin_link']) ?>">
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">Update Settings</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
