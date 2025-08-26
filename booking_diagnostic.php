<?php
// booking_diagnostic.php - Simple diagnostic tool to identify the issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

$response = [
    'status' => 'diagnostic',
    'checks' => [],
    'errors' => []
];

try {
    // 1. Check if this script is running
    $response['checks']['script_running'] = true;
    
    // 2. Check PHP version
    $response['checks']['php_version'] = PHP_VERSION;
    
    // 3. Check working directory
    $response['checks']['working_directory'] = getcwd();
    
    // 4. Check if config directory exists
    $config_paths = [
        __DIR__ . '/config/db.php',
        __DIR__ . '/../config/db.php', 
        './config/db.php',
        '../config/db.php'
    ];
    
    $response['checks']['config_paths_checked'] = [];
    $db_config_found = false;
    
    foreach ($config_paths as $path) {
        $exists = file_exists($path);
        $response['checks']['config_paths_checked'][] = [
            'path' => $path,
            'exists' => $exists,
            'readable' => $exists ? is_readable($path) : false
        ];
        
        if ($exists && !$db_config_found) {
            try {
                include_once $path;
                $response['checks']['db_config_loaded'] = true;
                $response['checks']['db_config_path'] = $path;
                $db_config_found = true;
            } catch (Exception $e) {
                $response['errors'][] = "Error loading DB config from $path: " . $e->getMessage();
            }
        }
    }
    
    // 5. Check database connection if config was loaded
    if ($db_config_found && isset($conn)) {
        if ($conn instanceof mysqli) {
            if ($conn->connect_error) {
                $response['errors'][] = "Database connection error: " . $conn->connect_error;
            } else {
                $response['checks']['database_connected'] = true;
                
                // Test query
                $test = $conn->query("SELECT 1");
                if ($test) {
                    $response['checks']['database_query_test'] = true;
                } else {
                    $response['errors'][] = "Database query test failed: " . $conn->error;
                }
                
                // Check if required tables exist
                $tables = ['cars', 'bookings'];
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    $response['checks']['table_' . $table] = ($result && $result->num_rows > 0);
                }
            }
        } else {
            $response['errors'][] = "Database connection variable exists but is not a mysqli object";
        }
    } else {
        $response['errors'][] = "Database config not found or connection variable not set";
    }
    
    // 6. Check uploads directory
    $upload_dir = 'uploads/';
    $response['checks']['uploads_directory'] = [
        'exists' => is_dir($upload_dir),
        'writable' => is_dir($upload_dir) ? is_writable($upload_dir) : false,
        'path' => realpath($upload_dir) ?: $upload_dir
    ];
    
    // 7. Check POST data if this is a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response['checks']['request_method'] = 'POST';
        $response['checks']['post_data_count'] = count($_POST);
        $response['checks']['files_count'] = count($_FILES);
        $response['checks']['post_keys'] = array_keys($_POST);
    } else {
        $response['checks']['request_method'] = $_SERVER['REQUEST_METHOD'];
    }
    
} catch (Exception $e) {
    $response['errors'][] = "Diagnostic error: " . $e->getMessage();
}

// Output the diagnostic results
echo json_encode($response, JSON_PRETTY_PRINT);
?>