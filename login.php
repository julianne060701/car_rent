<?php
session_start();
include('config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); // Ensure JSON output for AJAX

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all fields.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {

        // Block inactive accounts
        if ($row['status'] === 'inactive') {
            echo json_encode(['status' => 'error', 'message' => 'Your account is inactive. Contact admin.']);
            exit;
        }

        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            $redirect = ($row['role'] === 'admin') ? 'admin/index.php' : 'index.php';
            echo json_encode(['status' => 'success', 'redirect' => $redirect]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
    }
    exit; // Stop HTML output
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Booking System - Login</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-icon">
                <span class="car-icon">🚗</span>
            </div>
            <h1>CarBook Pro</h1>
            <p>Fleet Management System</p>
        </div>

        <div class="alert" id="alert"></div>

        <form id="loginForm">

            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
                <span class="password-toggle" onclick="togglePassword()">👁️</span>
            </div>

            <div class="forgot-password">
                <a href="#" onclick="showForgotPassword()">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn" id="loginBtn">
                Sign In
            </button>
        </form>

        <div class="features">
            <div class="feature">
                <div class="feature-icon"></div>
                <span>Secure Authentication</span>
            </div>
            <div class="feature">
                <div class="feature-icon"></div>
                <span>Real-time Fleet Tracking</span>
            </div>
            <div class="feature">
                <div class="feature-icon"></div>
                <span>24/7 Support Available</span>
            </div>
        </div>
    </div>

    <script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggle = document.querySelector('.password-toggle');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggle.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggle.textContent = '👁️';
    }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            showAlert('Login successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        } else {
            showAlert(data.message, 'error');
        }
    })
    .catch(() => {
        showAlert('An error occurred. Please try again.', 'error');
    });
});

function showAlert(message, type) {
    const alert = document.getElementById('alert');
    alert.textContent = message;
    alert.className = `alert ${type}`;
    alert.style.display = 'block';
    setTimeout(() => alert.style.display = 'none', 5000);
}
</script>
</body>
</html>
