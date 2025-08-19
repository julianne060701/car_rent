<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Include database connection
    if (!file_exists('config/db.php')) {
        throw new Exception("Database configuration file not found.");
    }
    require_once 'config/db.php';

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Get current date and time
    $current_datetime = date('Y-m-d H:i:s');

    // First, update car status based on active bookings
    $update_status_sql = "
        UPDATE cars c 
        SET c.status = CASE 
            WHEN EXISTS (
                SELECT 1 FROM bookings b 
                WHERE b.vehicle_id = c.car_id 
                AND b.status IN ('pending', 'confirmed', 'active')
                AND CONCAT(b.start_date, ' ', b.start_time) <= ?
                AND CONCAT(b.end_date, ' ', b.end_time) >= ?
            ) THEN 0
            ELSE 1
        END
    ";

    $stmt_update = $conn->prepare($update_status_sql);
    if (!$stmt_update) {
        throw new Exception("Failed to prepare status update query: " . $conn->error);
    }

    $stmt_update->bind_param("ss", $current_datetime, $current_datetime);
    $stmt_update->execute();
    $stmt_update->close();

    // Now fetch all vehicles with their current availability
    $sql = "
        SELECT 
            c.car_id,
            c.car_name,
            c.brand,
            c.plate_number,
            c.rate_per_day,
            c.hourly_rate,
            c.status,
            c.car_image,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM bookings b 
                    WHERE b.vehicle_id = c.car_id 
                    AND b.status IN ('pending', 'confirmed', 'active')
                    AND CONCAT(b.start_date, ' ', b.start_time) <= ?
                    AND CONCAT(b.end_date, ' ', b.end_time) >= ?
                ) THEN 0
                ELSE 1
            END as is_available,
            (
                SELECT COUNT(*) FROM bookings b 
                WHERE b.vehicle_id = c.car_id 
                AND b.status IN ('pending', 'confirmed', 'active')
            ) as active_bookings
        FROM cars c 
        WHERE c.car_id IS NOT NULL
        ORDER BY c.car_name ASC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare vehicles query: " . $conn->error);
    }

    $stmt->bind_param("ss", $current_datetime, $current_datetime);
    $stmt->execute();
    $result = $stmt->get_result();

    $vehicles = array();
    while ($row = $result->fetch_assoc()) {
        // Ensure all numeric values are properly formatted
        $row['rate_per_day'] = (float)$row['rate_per_day'];
        $row['hourly_rate'] = (float)$row['hourly_rate'];
        $row['status'] = (int)$row['status'];
        $row['is_available'] = (int)$row['is_available'];
        $row['active_bookings'] = (int)$row['active_bookings'];

        $vehicles[] = $row;
    }

    $stmt->close();
    $conn->close();

    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $vehicles,
        'message' => 'Vehicles loaded successfully',
        'count' => count($vehicles),
        'timestamp' => $current_datetime
    ]);

} catch (Exception $e) {
    error_log("Get Vehicles Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'data' => []
    ]);
}