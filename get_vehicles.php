<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
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

<<<<<<< HEAD
    // Get current date and time
    $current_datetime = date('Y-m-d H:i:s');
=======
    // Get parameters from request
    $start_datetime = isset($_REQUEST['start_datetime']) ? $_REQUEST['start_datetime'] : date('Y-m-d H:i:s');
    $end_datetime = isset($_REQUEST['end_datetime']) ? $_REQUEST['end_datetime'] : date('Y-m-d H:i:s', strtotime('+1 day'));
>>>>>>> e6161a9a20f7fdc42683062da3622c5667fe6f1b

    // Update car status based on active bookings
    // Status: 3 = Booked, 2 = Pending, 1 = Available, 0 = Unavailable
    $update_status_sql = "
        UPDATE cars c 
        SET c.status = CASE 
            WHEN EXISTS (
                SELECT 1 FROM bookings b 
                WHERE b.car_id = c.car_id 
                AND b.status IN ('pending', 'confirmed', 'active')
                AND CONCAT(b.start_date, ' ', b.start_time) <= ?
                AND CONCAT(b.end_date, ' ', b.end_time) >= ?
            ) THEN 3
            ELSE 1
        END
        WHERE c.status NOT IN (0)  -- Don't override manually set unavailable status
    ";

    $stmt_update = $conn->prepare($update_status_sql);
    if (!$stmt_update) {
        throw new Exception("Failed to prepare status update query: " . $conn->error);
    }
<<<<<<< HEAD

    $stmt_update->bind_param("ss", $current_datetime, $current_datetime);
=======
    
    $stmt_update->bind_param("ss", $end_datetime, $start_datetime);
>>>>>>> e6161a9a20f7fdc42683062da3622c5667fe6f1b
    $stmt_update->execute();
    $stmt_update->close();
<<<<<<< HEAD

    // Now fetch all vehicles with their current availability
=======
    
    // Fetch all vehicles with their current availability
>>>>>>> e2ee7a041240818db8c0f3a57e71afd860ea988f
    $sql = "
<<<<<<< HEAD
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

=======
    SELECT 
        c.car_id,
        c.car_name,
        c.brand,
        c.plate_number,
        GREATEST(COALESCE(c.rate_per_day, 0), COALESCE(c.rate_24h, 0), 1000) as rate_per_day,
        GREATEST(COALESCE(c.hourly_rate, 0), COALESCE(c.rate_8h, 0)/8, 125) as hourly_rate,
        COALESCE(c.rate_6h, 0) as rate_6h,
        COALESCE(c.rate_8h, 0) as rate_8h,
        COALESCE(c.rate_12h, 0) as rate_12h,
        COALESCE(c.rate_24h, 0) as rate_24h,
        c.status,
        c.car_image,
        CASE 
            WHEN c.status = 1 AND NOT EXISTS (
                SELECT 1 FROM bookings b 
                WHERE b.car_id = c.car_id 
                AND b.status IN ('pending', 'confirmed', 'active')
                AND CONCAT(b.start_date, ' ', b.start_time) <= ?
                AND CONCAT(b.end_date, ' ', b.end_time) >= ?
            ) THEN 1
            ELSE 0
        END as is_available,
        (
            SELECT COUNT(*) FROM bookings b 
            WHERE b.car_id = c.car_id 
            AND b.status IN ('pending', 'confirmed', 'active')
        ) as active_bookings,
        CASE c.status
            WHEN 1 THEN 'Available'
            WHEN 2 THEN 'Pending'
            WHEN 3 THEN 'Booked'
            WHEN 0 THEN 'Unavailable'
            ELSE 'Unknown'
        END as status_text
    FROM cars c 
    WHERE c.car_id IS NOT NULL
    ORDER BY c.status ASC, c.car_name ASC
";
    
>>>>>>> e6161a9a20f7fdc42683062da3622c5667fe6f1b
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Failed to prepare vehicles query: " . $conn->error);
    }
<<<<<<< HEAD

    $stmt->bind_param("ss", $current_datetime, $current_datetime);
=======
    
    $stmt->bind_param("ss", $end_datetime, $start_datetime);
>>>>>>> e6161a9a20f7fdc42683062da3622c5667fe6f1b
    $stmt->execute();
    $result = $stmt->get_result();

    $vehicles = array();
    while ($row = $result->fetch_assoc()) {
        // Ensure all numeric values are properly formatted
        $row['rate_per_day'] = (float)$row['rate_per_day'];
        $row['hourly_rate'] = (float)$row['hourly_rate'];
        $row['rate_6h'] = (float)$row['rate_6h'];
        $row['rate_8h'] = (float)$row['rate_8h'];
        $row['rate_12h'] = (float)$row['rate_12h'];
        $row['rate_24h'] = (float)$row['rate_24h'];
        $row['status'] = (int)$row['status'];
        $row['is_available'] = (int)$row['is_available'];
        $row['active_bookings'] = (int)$row['active_bookings'];
<<<<<<< HEAD

=======
        
        // Ensure minimum rates if still zero
        if ($row['rate_per_day'] <= 0) {
            $row['rate_per_day'] = 2000.00;
        }
        if ($row['hourly_rate'] <= 0) {
            $row['hourly_rate'] = 250.00;
        }
        
        // Add image URL if exists
        if (!empty($row['car_image'])) {
            $row['image_url'] = 'uploads/' . $row['car_image'];
        }
        
>>>>>>> e2ee7a041240818db8c0f3a57e71afd860ea988f
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
        'timestamp' => date('Y-m-d H:i:s'),
        'search_params' => [
            'start_datetime' => $start_datetime,
            'end_datetime' => $end_datetime
        ]
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