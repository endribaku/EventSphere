<?php
    include_once("db.php");

    $username = trim($_POST["name"]);
    $password = $_POST["password"];
    $email = trim($_POST["email"]);
    $role = trim($_POST["role"]);


    $registerQuery = "SELECT email FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $registerQuery);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo "Email already in system";
        exit();
    }

    $hashedpswd = password_hash($password, PASSWORD_DEFAULT);

    $insertQuery = "INSERT INTO users (name, email, password, role) 
                    VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertQuery);
    mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashedpswd, $role);
    
    if (mysqli_stmt_execute($stmt)) {  
        echo "User registered";
    } else {
        echo "User didn't register successfully";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
?>
