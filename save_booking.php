<?php
// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Only allow POST requests
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Check if the database connection file exists and include it
        if (!file_exists('config/db.php')) {
            throw new Exception("db.php file not found. Make sure it's in the config directory.");
        }
        require_once 'config/db.php';
        
        // Ensure the database connection is valid
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        // Function to sanitize user input
        function sanitize_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        // Sanitize all inputs from the POST request
        $customer_name = sanitize_input($_POST['customer_name'] ?? '');
        $customer_phone = sanitize_input($_POST['customer_phone'] ?? '');
        $customer_email = sanitize_input($_POST['customer_email'] ?? '');
        $license_number = sanitize_input($_POST['license_number'] ?? '');
        $pickup_date = sanitize_input($_POST['start_date'] ?? '');
        $return_date = sanitize_input($_POST['end_date'] ?? '');
        $pickup_time = sanitize_input($_POST['start_time'] ?? '');
        $return_time = sanitize_input($_POST['end_time'] ?? '');
        $pickup_location = sanitize_input($_POST['pickup_location'] ?? '');
        $return_location = sanitize_input($_POST['return_location'] ?? '');
        $purpose = sanitize_input($_POST['purpose_request'] ?? '');
        $passengers = sanitize_input($_POST['passengers'] ?? '1');
        $selected_vehicle = sanitize_input($_POST['selected_vehicle'] ?? '');
        $rental_duration = !empty($_POST['rental_duration']) ? (int)sanitize_input($_POST['rental_duration']) : null;
        
        // Handle file upload
        $uploaded_file_path = null;
        if (isset($_FILES['upload-image']) && $_FILES['upload-image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/documents/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['upload-image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $uploaded_file_path = $upload_dir . $new_filename;
            
            if (!move_uploaded_file($_FILES['upload-image']['tmp_name'], $uploaded_file_path)) {
                $uploaded_file_path = null;
            }
        }
        
        // Validate required fields
        if (empty($customer_name) || empty($customer_phone) || empty($customer_email) || 
            empty($pickup_date) || empty($return_date) || empty($pickup_time) || 
            empty($return_time) || empty($pickup_location) || empty($selected_vehicle)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
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

        // Get vehicle data
        $sql_cars = "SELECT car_id, rate_per_day, hourly_rate, status FROM cars WHERE car_name = ? LIMIT 1";
        $stmt_cars = $conn->prepare($sql_cars);
        if (!$stmt_cars) {
            throw new Exception("Vehicle query prepare failed: " . $conn->error);
        }
        $stmt_cars->bind_param("s", $selected_vehicle);
        $stmt_cars->execute();
        $result_cars = $stmt_cars->get_result();

        if ($result_cars->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Selected vehicle not found: ' . $selected_vehicle]);
            exit;
        }

        $car_data = $result_cars->fetch_assoc();
        $car_id = $car_data['car_id'];
        $rate_per_day = $car_data['rate_per_day'];
        $hourly_rate = $car_data['hourly_rate'];
        $stmt_cars->close();

        // Check for overlapping bookings
        $sql_check_overlap = "SELECT booking_id, booking_reference FROM bookings 
                              WHERE car_id = ? 
                              AND status IN ('pending', 'confirmed', 'active')
                              AND (
                                  (CONCAT(start_date, ' ', start_time) <= CONCAT(?, ' ', ?) AND CONCAT(end_date, ' ', end_time) >= CONCAT(?, ' ', ?)) OR
                                  (CONCAT(start_date, ' ', start_time) <= CONCAT(?, ' ', ?) AND CONCAT(end_date, ' ', end_time) >= CONCAT(?, ' ', ?)) OR
                                  (CONCAT(start_date, ' ', start_time) >= CONCAT(?, ' ', ?) AND CONCAT(end_date, ' ', end_time) <= CONCAT(?, ' ', ?))
                              )";
        $stmt_overlap = $conn->prepare($sql_check_overlap);
        if (!$stmt_overlap) {
            throw new Exception("Overlap check prepare failed: " . $conn->error);
        }
        $stmt_overlap->bind_param("issssssssssss", 
            $car_id, 
            $pickup_date, $pickup_time, $pickup_date, $pickup_time,
            $return_date, $return_time, $return_date, $return_time,
            $pickup_date, $pickup_time, $return_date, $return_time
        );
        $stmt_overlap->execute();
        $result_overlap = $stmt_overlap->get_result();
        
        if ($result_overlap->num_rows > 0) {
            $conflict = $result_overlap->fetch_assoc();
            http_response_code(400);
            echo json_encode([
                'status' => 'error', 
                'message' => 'This vehicle is already booked for the selected dates. Conflicting booking: ' . $conflict['booking_reference'],
                'conflict_booking' => $conflict['booking_reference']
            ]);
            $stmt_overlap->close();
            exit;
        }
        $stmt_overlap->close();

        // Calculate rental duration and cost
        $start_datetime_str = "{$pickup_date} {$pickup_time}";
        $end_datetime_str = "{$return_date} {$return_time}";

        $start_datetime = new DateTime($start_datetime_str);
        $end_datetime = new DateTime($end_datetime_str);

        // Check if dates are in the past
        if ($start_datetime < new DateTime() || $end_datetime < $start_datetime) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Pickup date and time cannot be in the past or after the return date.']);
            exit;
        }

        // Calculate total hours between dates
        $interval = $end_datetime->getTimestamp() - $start_datetime->getTimestamp();
        $calculated_hours = abs($interval) / 3600;

        // Validate minimum rental period
        if ($calculated_hours < 8) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Minimum rental period is 8 hours.']);
            exit;
        }

        // Determine final rental hours and type
        $total_hours = $calculated_hours;
        $rental_type = 'hourly';
        
        if ($rental_duration && $rental_duration >= 8) {
            $total_hours = $rental_duration;
        }

        // Determine rental type based on duration
        if ($total_hours >= 24) {
            $rental_type = 'daily';
        }

        // Calculate total cost
        $billable_hours = max($total_hours, 8); // Minimum 8 hours
        
        if ($billable_hours >= 24) {
            // Use daily rate for 24+ hours
            $days = ceil($billable_hours / 24);
            $total_cost = $rate_per_day * $days;
        } else {
            // Use hourly rate
            $total_cost = $hourly_rate * $billable_hours;
        }

        // REMOVED USER CREATION - No more user table dependency

        // Use return_location or default to pickup_location if empty
        $final_return_location = !empty($return_location) ? $return_location : $pickup_location;

        // START TRANSACTION for booking insertion
        $conn->begin_transaction();

        try {
<<<<<<< HEAD
            // INSERT booking - REMOVED user_id field
            $sql_insert = "INSERT INTO bookings (
                booking_reference, vehicle_id, customer_name, customer_phone, 
                customer_email, license_number, start_date, end_date, start_time, 
                end_time, pickup_location, return_location, purpose, passengers,
                total_cost, rental_type, rental_duration_hours, total_hours,
                pickup_time, return_time, uploaded_document, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
=======
            // Fixed SQL INSERT statement
            $sql_insert = "INSERT INTO bookings (
                booking_reference, user_id, car_id, customer_name, customer_phone, 
                customer_email, license_number, start_date, end_date, start_time, 
                end_time, pickup_location, return_location, purpose, passengers, 
                total_cost, rental_type, rental_duration, total_hours, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
>>>>>>> e6161a9a20f7fdc42683062da3622c5667fe6f1b

            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert) {
                // Corrected bind_param string and parameters list
                $rental_duration_hours = $rental_duration ? (int)$rental_duration : null;
                
                $stmt_insert->bind_param(
<<<<<<< HEAD
                    "sissssssssssssidissss",
                    $booking_reference, $car_id, $customer_name,
                    $customer_phone, $customer_email, $license_number, $pickup_date, 
                    $return_date, $pickup_time, $return_time, $pickup_location, 
                    $final_return_location, $purpose_requests, $passengers, $total_cost,
                    $rental_type, $rental_duration_hours, $total_hours,
                    $pickup_time, $return_time, $uploaded_file_path
=======
                    "siisssssssssssidsid",
                    $booking_reference, $user_id, $car_id, $customer_name,
                    $customer_phone, $customer_email, $license_number, $pickup_date, 
                    $return_date, $pickup_time, $return_time, $pickup_location, 
                    $final_return_location, $purpose, $passengers, $total_cost,
                    $rental_type, $rental_duration_hours, $total_hours
>>>>>>> e6161a9a20f7fdc42683062da3622c5667fe6f1b
                );

                if ($stmt_insert->execute()) {
                    $booking_id = $conn->insert_id;
                    
                    // Update vehicle status
                    $sql_update_vehicle = "UPDATE cars SET status = 0 WHERE car_id = ?";
                    $stmt_update = $conn->prepare($sql_update_vehicle);
                    if (!$stmt_update) {
                        throw new Exception("Vehicle update prepare failed: " . $conn->error);
                    }
                    
                    $stmt_update->bind_param("i", $car_id);
                    if (!$stmt_update->execute()) {
                        throw new Exception("Error updating vehicle availability: " . $stmt_update->error);
                    }
                    $stmt_update->close();
                    
                    // Commit the transaction
                    $conn->commit();
                    
                    // Prepare success response data
                    $response_data = [
                        'status' => 'success', 
                        'message' => 'Your booking has been successfully submitted!',
                        'booking_reference' => $booking_reference,
                        'booking_id' => $booking_id,
                        'total_cost' => number_format($total_cost, 2),
                        'total_hours' => round($total_hours, 2),
                        'rental_type' => $rental_type,
                        'vehicle' => $selected_vehicle,
                        'pickup_date' => $pickup_date,
                        'return_date' => $return_date,
                        'pickup_time' => $pickup_time,
                        'return_time' => $return_time
                    ];
                    
                    http_response_code(200);
                    echo json_encode($response_data);
                    
                } else {
                    throw new Exception("Error saving your booking: " . $stmt_insert->error);
                }
                $stmt_insert->close();
            } else {
                throw new Exception("Database error occurred while preparing insert statement: " . $conn->error);
            }
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            throw $e;
        }
        
    } else {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    }
} catch (Exception $e) {
    error_log("Booking Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>