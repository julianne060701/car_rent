<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "car_booking_system";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8 for proper character handling
$conn->set_charset("utf8");
?>