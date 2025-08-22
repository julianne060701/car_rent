<?php
// FIXED save_booking.php with comprehensive error handling and debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("=== BOOKING SCRIPT STARTED ===");

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Only allow POST requests
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Only POST requests are allowed.']);
        exit;
    }

    error_log("POST request received");
    error_log("POST data: " . json_encode($_POST));

    // Check if the database connection file exists
    $db_paths = [
        'config/db.php',
        '../config/db.php',
        'db.php',
        'config.php'
    ];
    
    $db_file = null;
    foreach ($db_paths as $path) {
        if (file_exists($path)) {
            $db_file = $path;
            break;
        }
    }
    
    if (!$db_file) {
        error_log("Database config file not found. Searched paths: " . implode(', ', $db_paths));
        throw new Exception("Database configuration file not found. Please ensure db.php exists in one of these locations: " . implode(', ', $db_paths));
    }
    
    error_log("Using database config file: $db_file");
    require_once $db_file;

    // Check database connection
    if (!isset($conn)) {
        throw new Exception("Database connection variable \$conn not found. Check your db.php file.");
    }
    
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    error_log("Database connection successful");

    // Location pricing configuration (must match JavaScript)
    $locationPricing = [
        'store' => 0,
        'gensan-airport' => 500,
        'downtown-gensan' => 300,
        'kcc-mall' => 500,
        'robinsons-place' => 400,
        'sm-city-gensan' => 400
    ];

    // Function to calculate location charges
    function calculateLocationCharges($pickupLocation, $returnLocation, $locationPricing) {
        $pickupCharge = $locationPricing[$pickupLocation] ?? 0;
        $returnCharge = 0;
        
        // Only charge for return location if it's specified AND different from pickup
        if (!empty($returnLocation) && trim($returnLocation) !== '' && $returnLocation !== $pickupLocation) {
            $returnCharge = $locationPricing[$returnLocation] ?? 0;
        }
        
        return [
            'pickupCharge' => $pickupCharge,
            'returnCharge' => $returnCharge,
            'totalLocationCharge' => $pickupCharge + $returnCharge
        ];
    }

    // Function to sanitize user input
    function sanitize_input($data) {
        if ($data === null) return '';
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // FIXED: Sanitize all inputs with better null handling
    $customer_name = sanitize_input($_POST['customer_name'] ?? '');
    $customer_phone = sanitize_input($_POST['customer_phone'] ?? '');
    $customer_email = sanitize_input($_POST['customer_email'] ?? '');
    $license_number = sanitize_input($_POST['license_number'] ?? '');
    $pickup_date = sanitize_input($_POST['start_date'] ?? '');
    $return_date = sanitize_input($_POST['end_date'] ?? '');
    $pickup_time = sanitize_input($_POST['start_time'] ?? '');
    $return_time = sanitize_input($_POST['end_time'] ?? '');
    $pickup_location = sanitize_input($_POST['pickup_location'] ?? '');
    $return_location_raw = sanitize_input($_POST['return_location'] ?? '');
    $return_location = (!empty($return_location_raw) && trim($return_location_raw) !== '') ? $return_location_raw : '';
    $purpose = sanitize_input($_POST['purpose'] ?? '');
    $passengers = sanitize_input($_POST['passengers'] ?? '1');
    $selected_vehicle = sanitize_input($_POST['selected_vehicle'] ?? '');
    $rental_duration = !empty($_POST['rental_duration']) ? (int)sanitize_input($_POST['rental_duration']) : null;

    error_log("Sanitized input data:");
    error_log("Customer: $customer_name, $customer_email, $customer_phone");
    error_log("Vehicle: $selected_vehicle");
    error_log("Dates: $pickup_date to $return_date");
    error_log("Locations: pickup=$pickup_location, return=$return_location");

    // Handle file upload
    $uploaded_file_path = null;
    if (isset($_FILES['upload_image']) && $_FILES['upload_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_extension = pathinfo($_FILES['upload_image']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid() . '.' . $file_extension;
        $uploaded_file_path = $upload_dir . $new_filename;

        if (!move_uploaded_file($_FILES['upload_image']['tmp_name'], $uploaded_file_path)) {
            $uploaded_file_path = null;
            error_log("Failed to move uploaded file");
        } else {
            error_log("File uploaded successfully: $uploaded_file_path");
        }
    }

    // FIXED: Validate required fields with better error messages
    $missing_fields = [];
    if (empty($customer_name)) $missing_fields[] = 'Full Name';
    if (empty($customer_phone)) $missing_fields[] = 'Phone Number';
    if (empty($customer_email)) $missing_fields[] = 'Email Address';
    if (empty($pickup_date)) $missing_fields[] = 'Pickup Date';
    if (empty($return_date)) $missing_fields[] = 'Return Date';
    if (empty($pickup_time)) $missing_fields[] = 'Pickup Time';
    if (empty($return_time)) $missing_fields[] = 'Return Time';
    if (empty($pickup_location)) $missing_fields[] = 'Pickup Location';
    if (empty($selected_vehicle)) $missing_fields[] = 'Selected Vehicle';

    if (!empty($missing_fields)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
        ]);
        exit;
    }

    // Validate email format
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
        exit;
    }

    // Generate booking reference
    $year = date("Y");
    $sql_last_ref = "SELECT booking_reference FROM bookings WHERE booking_reference LIKE 'BK{$year}%' ORDER BY booking_id DESC LIMIT 1";
    $result = $conn->query($sql_last_ref);
    $last_number = 0;
    if ($result && $result->num_rows > 0) {
        $last_ref = $result->fetch_assoc()['booking_reference'];
        $last_number = (int)substr($last_ref, -4);
    }
    $new_number = $last_number + 1;
    $booking_reference = "BK" . $year . str_pad($new_number, 4, '0', STR_PAD_LEFT);
    error_log("Generated booking reference: $booking_reference");

    // FIXED: Get vehicle data with better error handling
    $sql_cars = "SELECT car_id, car_name, rate_per_day, hourly_rate, rate_6h, rate_8h, rate_12h, rate_24h, status FROM cars WHERE car_name = ? LIMIT 1";
    $stmt_cars = $conn->prepare($sql_cars);
    if (!$stmt_cars) {
        error_log("Vehicle query prepare failed: " . $conn->error);
        throw new Exception("Database error while preparing vehicle query: " . $conn->error);
    }
    
    $stmt_cars->bind_param("s", $selected_vehicle);
    if (!$stmt_cars->execute()) {
        error_log("Vehicle query execute failed: " . $stmt_cars->error);
        throw new Exception("Database error while executing vehicle query: " . $stmt_cars->error);
    }
    
    $result_cars = $stmt_cars->get_result();

    if ($result_cars->num_rows === 0) {
        error_log("Vehicle not found: $selected_vehicle");
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Selected vehicle not found: ' . $selected_vehicle]);
        exit;
    }

    $car_data = $result_cars->fetch_assoc();
    $car_id = $car_data['car_id'];
    $rate_per_day = floatval($car_data['rate_per_day']);
    $hourly_rate = floatval($car_data['hourly_rate']);
    $rate_6h = floatval($car_data['rate_6h']) ?: 0;
    $rate_8h = floatval($car_data['rate_8h']) ?: 0;
    $rate_12h = floatval($car_data['rate_12h']) ?: 0;
    $rate_24h = floatval($car_data['rate_24h']) ?: $rate_per_day;
    $stmt_cars->close();

    error_log("Vehicle data found - ID: $car_id, Rates: daily=$rate_per_day, hourly=$hourly_rate, 24h=$rate_24h");

    // FIXED: Check for overlapping bookings with better query
    $sql_check_overlap = "SELECT booking_id, booking_reference FROM bookings 
                          WHERE car_id = ? 
                          AND status IN ('pending', 'confirmed', 'active')
                          AND NOT (
                              CONCAT(end_date, ' ', end_time) <= CONCAT(?, ' ', ?) OR
                              CONCAT(start_date, ' ', start_time) >= CONCAT(?, ' ', ?)
                          )";
    $stmt_overlap = $conn->prepare($sql_check_overlap);
    if (!$stmt_overlap) {
        error_log("Overlap check prepare failed: " . $conn->error);
        throw new Exception("Database error while preparing overlap check: " . $conn->error);
    }
    
    $stmt_overlap->bind_param("issss", 
        $car_id, 
        $pickup_date, $pickup_time,
        $return_date, $return_time
    );
    
    if (!$stmt_overlap->execute()) {
        error_log("Overlap check execute failed: " . $stmt_overlap->error);
        throw new Exception("Database error while checking for conflicts: " . $stmt_overlap->error);
    }
    
    $result_overlap = $stmt_overlap->get_result();

    if ($result_overlap->num_rows > 0) {
        $conflict = $result_overlap->fetch_assoc();
        error_log("Booking conflict found: " . $conflict['booking_reference']);
        http_response_code(400);
        echo json_encode([
            'status' => 'error', 
            'message' => 'This vehicle is already booked for the selected dates.',
            'conflict_booking' => $conflict['booking_reference']
        ]);
        $stmt_overlap->close();
        exit;
    }
    $stmt_overlap->close();
    error_log("No booking conflicts found");

    // Calculate rental duration and cost
    $start_datetime_str = "{$pickup_date} {$pickup_time}";
    $end_datetime_str = "{$return_date} {$return_time}";

    $start_datetime = new DateTime($start_datetime_str);
    $end_datetime = new DateTime($end_datetime_str);

    // Validate dates
    if ($start_datetime < new DateTime() || $end_datetime < $start_datetime) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid pickup or return date/time.']);
        exit;
    }

    // Calculate total hours
    $interval = $end_datetime->getTimestamp() - $start_datetime->getTimestamp();
    $calculated_hours = abs($interval) / 3600;

    if ($calculated_hours < 8) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Minimum rental period is 8 hours.']);
        exit;
    }

    $total_hours = $calculated_hours;
    $rental_type = 'hourly';

    if ($rental_duration && $rental_duration >= 8) {
        $total_hours = $rental_duration;
    }

    if ($total_hours >= 24) {
        $rental_type = 'daily';
    }

    error_log("Calculated hours: $total_hours, Type: $rental_type");

    // FIXED: Calculate vehicle cost with proper rate logic
    $billable_hours = max($total_hours, 8);

    if ($billable_hours >= 24 && $rate_24h > 0) {
        $days = ceil($billable_hours / 24);
        $vehicle_cost = $rate_24h * $days;
        error_log("Using 24h rate: $rate_24h x $days days = $vehicle_cost");
    } elseif ($billable_hours >= 12 && $billable_hours < 24 && $rate_12h > 0) {
        $vehicle_cost = $rate_12h;
        error_log("Using 12h rate: $rate_12h");
    } elseif ($billable_hours >= 8 && $billable_hours < 12 && $rate_8h > 0) {
        $vehicle_cost = $rate_8h;
        error_log("Using 8h rate: $rate_8h");
    } elseif ($billable_hours >= 6 && $billable_hours < 8 && $rate_6h > 0) {
        $vehicle_cost = $rate_6h;
        error_log("Using 6h rate: $rate_6h");
    } else {
        if ($billable_hours >= 24) {
            $days = ceil($billable_hours / 24);
            $vehicle_cost = $rate_per_day * $days;
            error_log("Using daily rate: $rate_per_day x $days days = $vehicle_cost");
        } else {
            $effective_hourly_rate = $hourly_rate > 0 ? $hourly_rate : 250;
            $vehicle_cost = $effective_hourly_rate * ceil($billable_hours);
            error_log("Using hourly rate: $effective_hourly_rate x " . ceil($billable_hours) . " hours = $vehicle_cost");
        }
    }

    // Calculate location charges
    $location_charges = calculateLocationCharges($pickup_location, $return_location, $locationPricing);
    $total_cost = $vehicle_cost + $location_charges['totalLocationCharge'];

    error_log("Cost breakdown - Vehicle: $vehicle_cost, Location: {$location_charges['totalLocationCharge']}, Total: $total_cost");

    // FIXED: Get or create user with better error handling
    $sql_user = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
    $stmt_user = $conn->prepare($sql_user);
    if (!$stmt_user) {
        error_log("User query prepare failed: " . $conn->error);
        throw new Exception("Database error while preparing user query: " . $conn->error);
    }
    
    $stmt_user->bind_param("s", $customer_email);
    if (!$stmt_user->execute()) {
        error_log("User query execute failed: " . $stmt_user->error);
        throw new Exception("Database error while checking user: " . $stmt_user->error);
    }
    
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows === 0) {
        error_log("Creating new user for: $customer_email");
        $sql_create_user = "INSERT INTO users (name, email, phone, created_at) VALUES (?, ?, ?, NOW())";
        $stmt_create = $conn->prepare($sql_create_user);
        if (!$stmt_create) {
            error_log("Create user prepare failed: " . $conn->error);
            throw new Exception("Database error while preparing user creation: " . $conn->error);
        }
        
        $stmt_create->bind_param("sss", $customer_name, $customer_email, $customer_phone);

        if (!$stmt_create->execute()) {
            error_log("Create user execute failed: " . $stmt_create->error);
            throw new Exception("Error creating user account: " . $stmt_create->error);
        }

        $user_id = $conn->insert_id;
        $stmt_create->close();
        error_log("New user created with ID: $user_id");
    } else {
        $user_id = $result_user->fetch_assoc()['user_id'];
        error_log("Existing user found with ID: $user_id");
    }
    $stmt_user->close();

    // Determine final return location
    $final_return_location = !empty($return_location) ? $return_location : $pickup_location;

    // START TRANSACTION
    $conn->begin_transaction();
    error_log("Transaction started");

    try {
        // FIXED: Insert booking with comprehensive error handling
        $sql_insert = "INSERT INTO bookings (
            booking_reference, user_id, car_id, customer_name, customer_phone, 
            customer_email, license_number, start_date, end_date, start_time, 
            end_time, pickup_location, return_location, purpose, passengers, 
            vehicle_cost, location_charges, total_cost, rental_type, rental_duration, 
            total_hours, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

        $stmt_insert = $conn->prepare($sql_insert);
        if (!$stmt_insert) {
            error_log("Insert booking prepare failed: " . $conn->error);
            throw new Exception("Database error while preparing booking insertion: " . $conn->error);
        }

        $rental_duration_hours = $rental_duration ? (int)$rental_duration : null;

        $stmt_insert->bind_param(
            "siissssssssssidddsidi",
            $booking_reference, $user_id, $car_id, $customer_name,
            $customer_phone, $customer_email, $license_number, $pickup_date, 
            $return_date, $pickup_time, $return_time, $pickup_location, 
            $final_return_location, $purpose, $passengers, $vehicle_cost,
            $location_charges['totalLocationCharge'], $total_cost, $rental_type, 
            $rental_duration_hours, $total_hours
        );

        if (!$stmt_insert->execute()) {
            error_log("Insert booking execute failed: " . $stmt_insert->error);
            throw new Exception("Error saving booking: " . $stmt_insert->error);
        }

        $booking_id = $conn->insert_id;
        error_log("Booking inserted with ID: $booking_id");

        // Update vehicle status
        $sql_update_vehicle = "UPDATE cars SET status = 0 WHERE car_id = ?";
        $stmt_update = $conn->prepare($sql_update_vehicle);
        if (!$stmt_update) {
            error_log("Vehicle update prepare failed: " . $conn->error);
            throw new Exception("Database error while preparing vehicle update: " . $conn->error);
        }

        $stmt_update->bind_param("i", $car_id);
        if (!$stmt_update->execute()) {
            error_log("Vehicle update execute failed: " . $stmt_update->error);
            throw new Exception("Error updating vehicle availability: " . $stmt_update->error);
        }
        $stmt_update->close();

        // Commit transaction
        $conn->commit();
        error_log("Transaction committed successfully");

        // FIXED: Prepare comprehensive success response
        $response_data = [
            'status' => 'success', 
            'message' => 'Your booking has been successfully submitted!',
            'booking_reference' => $booking_reference,
            'booking_id' => $booking_id,
            'vehicle_cost' => number_format($vehicle_cost, 2),
            'location_charges' => number_format($location_charges['totalLocationCharge'], 2),
            'total_cost' => number_format($total_cost, 2),
            'total_hours' => round($total_hours, 2),
            'rental_type' => $rental_type,
            'vehicle' => $selected_vehicle,
            'pickup_date' => $pickup_date,
            'return_date' => $return_date,
            'pickup_time' => $pickup_time,
            'return_time' => $return_time,
            'pickup_location' => $pickup_location,
            'return_location' => $final_return_location,
            'same_location' => empty($return_location)
        ];

        error_log("Success response prepared: " . json_encode($response_data));
        http_response_code(200);
        echo json_encode($response_data);

        $stmt_insert->close();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Transaction rolled back due to error: " . $e->getMessage());
        throw $e;
    }

} catch (Exception $e) {
    error_log("=== BOOKING ERROR ===");
    error_log("Error: " . $e->getMessage());
    error_log("File: " . $e->getFile() . " Line: " . $e->getLine());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'debug_info' => [
            'file' => basename($e->getFile()),
            'line' => $e->getLine()
        ]
    ]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
        error_log("Database connection closed");
    }
}

error_log("=== BOOKING SCRIPT ENDED ===");
?>