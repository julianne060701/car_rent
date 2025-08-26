<?php
// save_booking.php - Complete working version
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/booking_errors.log');

// Start output buffering to prevent any accidental output
ob_start();

// Set JSON headers immediately
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_end_clean();
    http_response_code(200);
    exit;
}

// Function to log debug information
function logDebug($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message);
}

// Function to send JSON error response
function sendError($message, $code = 500) {
    ob_end_clean(); // Clear any output buffer
    logDebug("ERROR: $message");
    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Function to send JSON success response
function sendSuccess($data) {
    ob_end_clean(); // Clear any output buffer
    logDebug("SUCCESS: Booking created");
    http_response_code(200);
    echo json_encode(array_merge([
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s')
    ], $data));
    exit;
}

try {
    logDebug("=== BOOKING REQUEST STARTED ===");
    
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendError('Only POST requests are allowed', 405);
    }
    
    // Database connection
    $conn = null;
    $db_paths = [
        __DIR__ . '/config/db.php',
        __DIR__ . '/../config/db.php',
        './config/db.php',
        '../config/db.php'
    ];
    
    foreach ($db_paths as $path) {
        if (file_exists($path)) {
            logDebug("Found DB config at: $path");
            try {
                include_once $path;
                if (isset($conn) && $conn instanceof mysqli) {
                    logDebug("Database connection successful");
                    break;
                }
            } catch (Exception $e) {
                logDebug("Error including DB config: " . $e->getMessage());
                continue;
            }
        }
    }
    
    if (!$conn || !($conn instanceof mysqli)) {
        sendError("Database connection failed. Please check your database configuration.");
    }
    
    if ($conn->connect_error) {
        sendError("Database connection error: " . $conn->connect_error);
    }
    
    // Test database connection
    $test_query = $conn->query("SELECT 1");
    if (!$test_query) {
        sendError("Database connection test failed: " . $conn->error);
    }
    
    // Input sanitization function
    function clean($data) {
        global $conn;
        return mysqli_real_escape_string($conn, htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8'));
    }
    
    // Validate required fields
    $required_fields = [
        'customer_name' => 'Full Name',
        'customer_phone' => 'Phone Number',
        'customer_email' => 'Email Address',
        'start_date' => 'Pickup Date',
        'end_date' => 'Return Date',
        'start_time' => 'Pickup Time',
        'end_time' => 'Return Time',
        'pickup_location' => 'Pickup Location',
        'selected_vehicle' => 'Selected Vehicle'
    ];
    
    $data = [];
    $missing_fields = [];
    
    foreach ($required_fields as $field => $label) {
        $value = clean($_POST[$field] ?? '');
        if (empty($value)) {
            $missing_fields[] = $label;
        }
        $data[$field] = $value;
    }
    
    if (!empty($missing_fields)) {
        sendError('Missing required fields: ' . implode(', ', $missing_fields), 400);
    }
    
    // Optional fields
    $data['license_number'] = clean($_POST['license_number'] ?? '');
    $data['return_location'] = clean($_POST['return_location'] ?? '');
    $data['purpose'] = clean($_POST['purpose'] ?? '');
    $data['passengers'] = max(1, (int)($_POST['passengers'] ?? 1));
    
    logDebug("Input validation passed");
    
    // Validate email format
    if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
        sendError('Invalid email address format', 400);
    }
    
    // Validate phone number (basic check)
    if (strlen($data['customer_phone']) < 10) {
        sendError('Phone number must be at least 10 digits', 400);
    }
    
    // Get vehicle information
    $vehicle_query = "SELECT car_id, car_name, rate_per_day, rate_24h, rate_12h, rate_8h, rate_6h, hourly_rate FROM cars WHERE car_name = ? LIMIT 1";
    $stmt = $conn->prepare($vehicle_query);
    
    if (!$stmt) {
        sendError("Database query preparation failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $data['selected_vehicle']);
    
    if (!$stmt->execute()) {
        sendError("Vehicle lookup failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendError("Selected vehicle '{$data['selected_vehicle']}' not found", 404);
    }
    
    $vehicle = $result->fetch_assoc();
    $stmt->close();
    
    logDebug("Vehicle found: {$vehicle['car_name']} (ID: {$vehicle['car_id']})");
    
    // Date and time validation
    $pickup_datetime = new DateTime($data['start_date'] . ' ' . $data['start_time']);
    $return_datetime = new DateTime($data['end_date'] . ' ' . $data['end_time']);
    $current_datetime = new DateTime();
    
    // Check if pickup is in the past
    if ($pickup_datetime <= $current_datetime) {
        sendError('Pickup date and time must be in the future', 400);
    }
    
    // Check if return is after pickup
    if ($return_datetime <= $pickup_datetime) {
        sendError('Return date and time must be after pickup date and time', 400);
    }
    
    // Calculate rental duration
    $duration_seconds = $return_datetime->getTimestamp() - $pickup_datetime->getTimestamp();
    $duration_hours = $duration_seconds / 3600;
    
    // Minimum 8 hours rental
    if ($duration_hours < 8) {
        sendError('Minimum rental period is 8 hours', 400);
    }
    
    // Calculate rental cost
    $vehicle_cost = 0;
    $rental_type = 'hourly';
    
    // Use appropriate rate based on duration
    if ($duration_hours >= 24 && !empty($vehicle['rate_24h']) && $vehicle['rate_24h'] > 0) {
        $days = ceil($duration_hours / 24);
        $vehicle_cost = $vehicle['rate_24h'] * $days;
        $rental_type = 'daily';
    } elseif ($duration_hours >= 12 && !empty($vehicle['rate_12h']) && $vehicle['rate_12h'] > 0) {
        $vehicle_cost = $vehicle['rate_12h'];
        $rental_type = '12h';
    } elseif ($duration_hours >= 8 && !empty($vehicle['rate_8h']) && $vehicle['rate_8h'] > 0) {
        $vehicle_cost = $vehicle['rate_8h'];
        $rental_type = '8h';
    } elseif ($duration_hours >= 6 && !empty($vehicle['rate_6h']) && $vehicle['rate_6h'] > 0) {
        $vehicle_cost = $vehicle['rate_6h'];
        $rental_type = '6h';
    } else {
        // Use hourly rate or default
        $hourly_rate = !empty($vehicle['hourly_rate']) && $vehicle['hourly_rate'] > 0 
            ? $vehicle['hourly_rate'] 
            : (!empty($vehicle['rate_per_day']) ? $vehicle['rate_per_day'] / 24 : 300);
        $vehicle_cost = $hourly_rate * ceil($duration_hours);
        $rental_type = 'hourly';
    }
    
    // Location charges
    $location_rates = [
        'gensan-airport' => 500,
        'downtown-gensan' => 300,
        'kcc-mall' => 500,
        'robinsons-place' => 400,
        'sm-city-gensan' => 400,
        'store' => 0
    ];
    
    $pickup_charge = $location_rates[$data['pickup_location']] ?? 0;
    $return_charge = 0;
    
    if (!empty($data['return_location']) && $data['return_location'] !== $data['pickup_location']) {
        $return_charge = $location_rates[$data['return_location']] ?? 0;
    }
    
    $location_cost = $pickup_charge + $return_charge;
    $total_cost = $vehicle_cost + $location_cost;
    
    logDebug("Cost calculation: Vehicle=$vehicle_cost, Location=$location_cost, Total=$total_cost");
    
    // Generate unique booking reference
    $year = date("Y");
    $reference_query = "SELECT booking_reference FROM bookings WHERE booking_reference LIKE 'BK{$year}%' ORDER BY booking_id DESC LIMIT 1";
    $ref_result = $conn->query($reference_query);
    
    $last_number = 0;
    if ($ref_result && $ref_result->num_rows > 0) {
        $last_ref = $ref_result->fetch_assoc()['booking_reference'];
        $last_number = (int)substr($last_ref, -4);
    }
    
    $booking_reference = "BK" . $year . str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
    
    // Handle file upload
    $uploaded_document = null;
    if (isset($_FILES['upload_image']) && $_FILES['upload_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/documents/';
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                logDebug("Failed to create upload directory: $upload_dir");
                sendError("Failed to create upload directory");
            }
        }
        
        $file_info = $_FILES['upload_image'];
        $file_extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = uniqid('doc_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_info['tmp_name'], $upload_path)) {
                $uploaded_document = $upload_path;
                logDebug("File uploaded successfully: $upload_path");
            } else {
                logDebug("Failed to move uploaded file");
                sendError("Failed to save uploaded file");
            }
        } else {
            sendError("Invalid file type. Only JPG, PNG, PDF, and GIF files are allowed.");
        }
    }
    
    // Start database transaction
    $conn->begin_transaction();
    
    try {
        // Insert booking record
        $insert_query = "INSERT INTO bookings (
            car_id, customer_name, customer_phone, customer_email, license_number,
            booking_reference, start_date, end_date, start_time, end_time,
            pickup_location, return_location, purpose, passengers, status,
            total_cost, rental_type, total_hours, uploaded_document,
            pickup_time, return_time, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending',
            ?, ?, ?, ?, ?, ?, NOW(), NOW()
        )";
        
        $stmt = $conn->prepare($insert_query);
        
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        // Set return location to pickup location if not specified
        $final_return_location = !empty($data['return_location']) ? $data['return_location'] : $data['pickup_location'];
        
        $stmt->bind_param(
            "isssssssssssiddsssss",
            $vehicle['car_id'],          // car_id
            $data['customer_name'],      // customer_name
            $data['customer_phone'],     // customer_phone
            $data['customer_email'],     // customer_email
            $data['license_number'],     // license_number
            $booking_reference,          // booking_reference
            $data['start_date'],         // start_date
            $data['end_date'],           // end_date
            $data['start_time'],         // start_time
            $data['end_time'],           // end_time
            $data['pickup_location'],    // pickup_location
            $final_return_location,      // return_location
            $data['purpose'],            // purpose
            $data['passengers'],         // passengers
            $total_cost,                 // total_cost
            $rental_type,                // rental_type
            $duration_hours,             // total_hours
            $uploaded_document,          // uploaded_document
            $data['start_time'],         // pickup_time
            $data['end_time']            // return_time
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $booking_id = $conn->insert_id;
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        logDebug("Booking successfully created: ID=$booking_id, Reference=$booking_reference");
        
        // Send success response
        sendSuccess([
            'message' => 'Your booking request has been submitted successfully! We will contact you within 24 hours to confirm your reservation.',
            'booking_reference' => $booking_reference,
            'booking_id' => $booking_id,
            'vehicle' => $data['selected_vehicle'],
            'total_cost' => number_format($total_cost, 2),
            'vehicle_cost' => number_format($vehicle_cost, 2),
            'location_cost' => number_format($location_cost, 2),
            'total_hours' => round($duration_hours, 2),
            'rental_type' => $rental_type,
            'pickup_date' => $data['start_date'],
            'return_date' => $data['end_date'],
            'pickup_time' => $data['start_time'],
            'return_time' => $data['end_time'],
            'pickup_location' => $data['pickup_location'],
            'return_location' => $final_return_location
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        logDebug("Transaction rolled back: " . $e->getMessage());
        throw $e;
    }
    
} catch (Exception $e) {
    logDebug("FATAL ERROR: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    sendError("An error occurred while processing your booking: " . $e->getMessage());
} finally {
    // Close database connection
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    logDebug("=== BOOKING REQUEST ENDED ===");
}
?>