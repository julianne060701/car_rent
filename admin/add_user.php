<?php
include('../config/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $role      = $_POST['role'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prevent duplicate username/email
    $check = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['error'] = "Username or email already exists.";
        header("Location: user.php");
        exit;
    }

    $check->close();

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $password, $full_name, $role);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User added successfully.";
    } else {
        $_SESSION['error'] = "Error adding user: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: user.php");
    exit;
}
