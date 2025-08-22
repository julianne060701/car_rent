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

        // Location pricing configuration (matches JavaScript)
        $locationPricing = [
            'store' => 0,
            'gensan-airport' => 500,
            'downtown-gensan' => 300,
            'kcc-mall' => 500,
            'robinsons-place' => 400,
            'sm-city-gensan' => 400
        ];

        // FIXED: Function to calculate location charges with proper return location handling
        function calculateLocationCharges($pickupLocation, $returnLocation, $locationPricing) {
            $pickupCharge = $locationPricing[$pickupLocation] ?? 0;
            $returnCharge = 0;
            
            // Only charge for return location if it's specified AND different from pickup
            // If returnLocation is empty/null, treat as same location (no extra charge)
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
        
        // FIXED: Handle return location properly
        $return_location_raw = sanitize_input($_POST['return_location'] ?? '');
        // If return location is provided and not empty, use it; otherwise leave empty for same-location logic
        $return_location = (!empty($return_location_raw) && trim($return_location_raw) !== '') ? $return_location_raw : '';
        
        $purpose = sanitize_input($_POST['purpose'] ?? '');
        $passengers = sanitize_input($_POST['passengers'] ?? '1');
        $selected_vehicle = sanitize_input($_POST['selected_vehicle'] ?? '');
        $rental_duration = !empty($_POST['rental_duration']) ? (int)sanitize_input($_POST['rental_duration']) : null;
        
        // Debug log for return location handling
        error_log("Return location handling: raw='" . $return_location_raw . "', processed='" . $return_location . "', pickup='" . $pickup_location . "'");
        
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

        // FIXED: Get vehicle data - include rate_24h if it exists
        $sql_cars = "SELECT car_id, rate_per_day, hourly_rate, rate_6h, rate_8h, rate_12h, rate_24h, status FROM cars WHERE car_name = ? LIMIT 1";
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
        $rate_per_day = floatval($car_data['rate_per_day']);
        $hourly_rate = floatval($car_data['hourly_rate']);
        $rate_6h = floatval($car_data['rate_6h']) ?: 0;
        $rate_8h = floatval($car_data['rate_8h']) ?: 0;
        $rate_12h = floatval($car_data['rate_12h']) ?: 0;
        $rate_24h = floatval($car_data['rate_24h']) ?: $rate_per_day; // Use rate_24h if available, fallback to rate_per_day
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

        // FIXED: Calculate vehicle cost with proper rate selection logic matching JavaScript
        $billable_hours = max($total_hours, 8); // Minimum 8 hours
        
        if ($billable_hours >= 24 && $rate_24h > 0) {
            // Use 24h rate for full days
            $days = ceil($billable_hours / 24);
            $vehicle_cost = $rate_24h * $days;
        } elseif ($billable_hours >= 12 && $billable_hours < 24 && $rate_12h > 0) {
            $vehicle_cost = $rate_12h;
        } elseif ($billable_hours >= 8 && $billable_hours < 12 && $rate_8h > 0) {
            $vehicle_cost = $rate_8h;
        } elseif ($billable_hours >= 6 && $billable_hours < 8 && $rate_6h > 0) {
            $vehicle_cost = $rate_6h;
        } else {
            // Fall back to hourly or daily calculation
            if ($billable_hours >= 24) {
                $days = ceil($billable_hours / 24);
                $vehicle_cost = $rate_per_day * $days;
            } else {
                // Use hourly rate, ensuring we have a valid rate
                $effective_hourly_rate = $hourly_rate > 0 ? $hourly_rate : ($rate_8h > 0 ? ($rate_8h / 8) : 250);
                $vehicle_cost = $effective_hourly_rate * ceil($billable_hours);
            }
        }

<<<<<<< HEAD
        // REMOVED USER CREATION - No more user table dependency
=======
        // FIXED: Calculate location charges with proper return location handling
        $location_charges = calculateLocationCharges($pickup_location, $return_location, $locationPricing);
        
        // Calculate total cost including location charges
        $total_cost = $vehicle_cost + $location_charges['totalLocationCharge'];

        // Log pricing calculation for debugging
        error_log("Pricing calculation: vehicle_cost=" . $vehicle_cost . ", location_charges=" . $location_charges['totalLocationCharge'] . ", total=" . $total_cost);

        // Get or create user
        $sql_user = "SELECT user_id FROM users WHERE email = ? LIMIT 1";
        $stmt_user = $conn->prepare($sql_user);
        if (!$stmt_user) {
            throw new Exception("User query prepare failed: " . $conn->error);
        }
        $stmt_user->bind_param("s", $customer_email);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        
        if ($result_user->num_rows === 0) {
            // Create new user if doesn't exist
            $sql_create_user = "INSERT INTO users (name, email, phone, created_at) VALUES (?, ?, ?, NOW())";
            $stmt_create = $conn->prepare($sql_create_user);
            if (!$stmt_create) {
                throw new Exception("Create user query prepare failed: " . $conn->error);
            }
            $stmt_create->bind_param("sss", $customer_name, $customer_email, $customer_phone);
            
            if (!$stmt_create->execute()) {
                throw new Exception("Error creating user account: " . $stmt_create->error);
            }
            
            $user_id = $conn->insert_id;
            $stmt_create->close();
        } else {
            $user_id = $result_user->fetch_assoc()['user_id'];
        }
        $stmt_user->close();
>>>>>>> e2ee7a041240818db8c0f3a57e71afd860ea988f

        // FIXED: Store return location properly - use pickup if return is empty
        $final_return_location = !empty($return_location) ? $return_location : $pickup_location;

        // START TRANSACTION for booking insertion
        $conn->begin_transaction();

        try {
<<<<<<< HEAD
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
=======
            // Updated SQL INSERT statement to include location charges
>>>>>>> e2ee7a041240818db8c0f3a57e71afd860ea988f
            $sql_insert = "INSERT INTO bookings (
                booking_reference, user_id, car_id, customer_name, customer_phone, 
                customer_email, license_number, start_date, end_date, start_time, 
                end_time, pickup_location, return_location, purpose, passengers, 
<<<<<<< HEAD
                total_cost, rental_type, rental_duration, total_hours, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
>>>>>>> e6161a9a20f7fdc42683062da3622c5667fe6f1b
=======
                vehicle_cost, location_charges, total_cost, rental_type, rental_duration, 
                total_hours, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
>>>>>>> e2ee7a041240818db8c0f3a57e71afd860ea988f

            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert) {
                $rental_duration_hours = $rental_duration ? (int)$rental_duration : null;
                
                $stmt_insert->bind_param(
<<<<<<< HEAD
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
=======
                    "siissssssssssidddsidi",
                    $booking_reference, $user_id, $car_id, $customer_name,
                    $customer_phone, $customer_email, $license_number, $pickup_date, 
                    $return_date, $pickup_time, $return_time, $pickup_location, 
                    $final_return_location, $purpose, $passengers, $vehicle_cost,
                    $location_charges['totalLocationCharge'], $total_cost, $rental_type, 
                    $rental_duration_hours, $total_hours
>>>>>>> e2ee7a041240818db8c0f3a57e71afd860ea988f
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
                    
                    // FIXED: Prepare success response data with proper location display
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
                        'same_location' => empty($return_location) // Indicate if using same location
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