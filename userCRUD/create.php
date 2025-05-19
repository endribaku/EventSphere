<?php
require_once("../admin/admin_auth.php");
require_once("../php/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"];

    if (!empty($name) && !empty($email) && !empty($password) && in_array($role, ['user', 'organizer', 'admin'])) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            header("Location: ../admin/users.php?success=1");
        } else {
            header("Location: ../admin/users.php?error=1");
        }

        $stmt->close();
    } else {
        header("Location: ../admin/users.php?error=1");
    }

    exit();
}
?>