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
    
    // Check database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Query to get all available vehicles
    $sql = "SELECT 
                car_id,
                car_name,
                brand,
                plate_number,
                rate_per_day,
                hourly_rate,
                status,
                created_at
            FROM cars 
            WHERE status = 1 
            ORDER BY car_name ASC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $vehicles = array();
    
    while ($row = $result->fetch_assoc()) {
        // Check if vehicle is currently available (not booked for today)
        $availability_sql = "SELECT COUNT(*) as booking_count 
                            FROM bookings 
                            WHERE vehicle_id = ? 
                            AND status IN ('confirmed', 'pending') 
                            AND start_date <= CURDATE() 
                            AND end_date >= CURDATE()";
        
        $stmt = $conn->prepare($availability_sql);
        $stmt->bind_param("i", $row['car_id']);
        $stmt->execute();
        $availability_result = $stmt->get_result();
        $availability_data = $availability_result->fetch_assoc();
        $stmt->close();
        
        // Determine availability status
        $is_available = $availability_data['booking_count'] == 0;
        
        // Add vehicle data with availability
        $vehicles[] = array(
            'car_id' => $row['car_id'],
            'car_name' => $row['car_name'],
            'brand' => $row['brand'],
            'plate_number' => $row['plate_number'],
            'rate_per_day' => floatval($row['rate_per_day']),
            'hourly_rate' => floatval($row['hourly_rate']),
            'status' => intval($row['status']),
            'is_available' => $is_available,
            'availability_text' => $is_available ? 'Available' : 'Currently Booked',
            'created_at' => $row['created_at']
        );
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $vehicles,
        'total_vehicles' => count($vehicles)
    ]);
    
} catch (Exception $e) {
    // Log error and return error response
    error_log("Get Vehicles Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'data' => []
    ]);
} finally {
    // Close database connection
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>