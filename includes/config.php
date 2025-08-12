<?php
// Database Configuration for GenSan Car Rentals
define('DB_HOST', 'localhost');
define('DB_NAME', 'gensan_car_rentals');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Site Configuration
define('SITE_NAME', 'GenSan Car Rentals');
define('SITE_URL', 'http://localhost/gensan-car-rentals');
define('ADMIN_EMAIL', 'admin@gensanrentals.com');

// Business Information
define('BUSINESS_PHONE', '(083) 555-0123');
define('BUSINESS_EMAIL', 'info@gensanrentals.com');
define('BUSINESS_ADDRESS', 'Pioneer Avenue, General Santos City, 9500');

// Timezone
date_default_timezone_set('Asia/Manila');

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        )
    );
} catch(PDOException $e) {
    // In production, log this error instead of displaying it
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Helper Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_phone($phone) {
    // Basic phone validation for Philippine numbers
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    return (strlen($phone) >= 10 && strlen($phone) <= 15);
}

function send_notification_email($to, $subject, $message) {
    $headers = "From: " . BUSINESS_EMAIL . "\r\n";
    $headers .= "Reply-To: " . BUSINESS_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}
?>