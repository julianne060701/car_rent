<?php
include('config/db.php');

// Get search parameters
$pickup_location = $_GET['pickup_location'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? '';
$pickup_time = $_GET['pickup_time'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$return_time = $_GET['return_time'] ?? '';

// Pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$cars_per_page = 4;
$offset = ($page - 1) * $cars_per_page;

// Validate required parameters
if (empty($pickup_date) || empty($return_date)) {
    header('Location: index.php');
    exit;
}

// CREATE THE MISSING VARIABLES - This is what was causing the undefined variable errors
$requested_start = $pickup_date . ' ' . $pickup_time;
$requested_end = $return_date . ' ' . $return_time;

// First, get all cars for counting
$sql_cars = "SELECT * FROM cars ORDER BY rate_per_day ASC";
$result_cars = $conn->query($sql_cars);
$all_cars = [];

if ($result_cars->num_rows > 0) {
    while ($row = $result_cars->fetch_assoc()) {
        $all_cars[] = $row;
    }
}

// Now check availability for each car
$cars = [];
$available_count = 0;

foreach ($all_cars as $car) {
    // Check conflicts with proper logic - Cars unavailable from booking start time only
    $sql_booking_check = "
        SELECT COUNT(*) as conflict_count
        FROM bookings 
        WHERE car_id = ? 
        AND status IN ('pending', 'approved', 'active') 
        AND CONCAT(start_date, ' ', start_time) <= ?
        AND CONCAT(end_date, ' ', end_time) > ?
    ";
    
    $stmt = $conn->prepare($sql_booking_check);
    $stmt->bind_param("iss", 
        $car['car_id'], 
        $requested_end,
        $requested_start
    );
    $stmt->execute();
    $booking_result = $stmt->get_result();
    $booking_row = $booking_result->fetch_assoc();
    
    // Determine availability - Handle both numeric (1) and string ('Available') status
    $car_is_available = ($car['status'] == 1 || $car['status'] == '1' || strtolower($car['status']) == 'available');
    $is_available = $car_is_available && ($booking_row['conflict_count'] == 0);
    
    // Add availability flag to car data
    $car['is_available'] = $is_available ? 1 : 0;
    
    // Add to cars array
    $cars[] = $car;
    
    if ($is_available) {
        $available_count++;
    }
    
    $stmt->close();
}

// Calculate pagination
$total_cars = count($cars);
$total_pages = ceil($total_cars / $cars_per_page);
$paginated_cars = array_slice($cars, $offset, $cars_per_page);

// Function to format location name
function formatLocationName($location) {
    $locationMap = [
        'gensan-airport' => 'GenSan Airport',
        'downtown-gensan' => 'Downtown GenSan',
        'kcc-mall' => 'KCC Mall',
        'robinsons-place' => 'Robinson\'s Place GenSan',
        'sm-city-gensan' => 'SM City General Santos'
    ];
    return $locationMap[$location] ?? $location;
}

// Function to get vehicle type based on seating
function getVehicleType($seater) {
    if ($seater <= 4) return 'Sedan';
    if ($seater <= 7) return 'SUV/MPV';
    return 'Van';
}

// Function to get star rating display
function getStarRating($rating) {
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    
    $stars = str_repeat('<i class="fas fa-star"></i>', $fullStars);
    if ($hasHalfStar) {
        $stars .= '<i class="fas fa-star-half-alt"></i>';
    }
    $stars .= str_repeat('<i class="far fa-star"></i>', $emptyStars);
    
    return $stars;
}

// Function to build pagination URL
function buildPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenSan Car Rentals - Your Trusted Car Rental in General Santos City</title>
    <meta name="description" content="Premium car rentals in General Santos City. Reliable, affordable, and well-maintained vehicles for business and leisure.">
    <meta name="keywords" content="car rental, General Santos City, GenSan, Philippines, vehicle rental">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="css/available.css">
</head>
<body class="bg-gray-100">

<header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-gray-800">GenSan Car Rentals</a>
            <div class="space-x-4 hidden md:flex">
                <a href="#home" class="text-gray-600 hover:text-blue-500 transition-colors">Home</a>
                <a href="#vehicles" class="text-gray-600 hover:text-blue-500 transition-colors">Vehicles</a>
                <a href="#services" class="text-gray-600 hover:text-blue-500 transition-colors">Services</a>
                <a href="#about" class="text-gray-600 hover:text-blue-500 transition-colors">About Us</a>
                <a href="#contact" class="text-gray-600 hover:text-blue-500 transition-colors">Contact</a>
            </div>
            <button class="md:hidden">
                <i class="fas fa-bars text-xl text-gray-800"></i>
            </button>
        </nav>
    </header>

    <section class="hero" id="home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Explore General Santos City in Comfort</h1>
            <p class="text-lg md:text-xl mb-8">Premium car rentals for business, leisure, and everything in between</p>
            <div class="hero-buttons space-x-4">
                <button class="btn btn-primary" onclick="openBookingModal()">Book Now</button>
                <a href="#vehicles" class="btn btn-secondary">View Fleet</a>
            </div>
        </div>
    </section>

    <section class="quick-booking">
        <div class="container mx-auto px-4">
            <!-- Main Form -->
            <form id="quick-booking-form" class="booking-form" action="available_car.php" method="GET">
                
                <div class="form-group">
                    <label for="pickup-location" class="block text-gray-700">Pickup Location</label>
                    <select id="pickup-location" name="pickup_location" required>
                        <option value="">Select Location</option>
                        <option value="gensan-airport" <?php echo ($pickup_location == 'gensan-airport') ? 'selected' : ''; ?>>GenSan Airport</option>
                        <option value="downtown-gensan" <?php echo ($pickup_location == 'downtown-gensan') ? 'selected' : ''; ?>>Downtown GenSan</option>
                        <option value="kcc-mall" <?php echo ($pickup_location == 'kcc-mall') ? 'selected' : ''; ?>>KCC Mall</option>
                        <option value="robinsons-place" <?php echo ($pickup_location == 'robinsons-place') ? 'selected' : ''; ?>>Robinson's Place GenSan</option>
                        <option value="sm-city-gensan" <?php echo ($pickup_location == 'sm-city-gensan') ? 'selected' : ''; ?>>SM City General Santos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pickup-date" class="block text-gray-700">Pickup Date</label>
                    <input type="date" id="pickup-date" name="pickup_date" value="<?php echo $pickup_date; ?>" required>
                </div>

                <div class="form-group">
                    <label for="pickup-time" class="block text-gray-700">Pickup Time</label>
                    <input type="time" id="pickup-time" name="pickup_time" value="<?php echo $pickup_time ?: '09:00'; ?>" required>
                </div>

                <div class="form-group">
                    <label for="return-date" class="block text-gray-700">Return Date</label>
                    <input type="date" id="return-date" name="return_date" value="<?php echo $return_date; ?>" required>
                </div>

                <div class="form-group">
                    <label for="return-time" class="block text-gray-700">Return Time</label>
                    <input type="time" id="return-time" name="return_time" value="<?php echo $return_time ?: '09:00'; ?>" required>
                </div>

                <div class="flex items-end justify-center col-span-full md:col-span-1">
                    <button type="submit" class="btn btn-search flex-1 w-full md:w-auto">
                        <i class="fas fa-search mr-2"></i> Find Cars
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div class="container mx-auto px-4 pb-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <aside class="lg:w-1/4">
                <div class="filter-sidebar p-6 sticky top-24">
                    <h3 class="text-xl font-bold mb-6 text-gray-800">Filter Results</h3>
                    
                    <!-- Availability Filter -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-gray-700">Availability</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" id="show-available" checked>
                                <span class="text-gray-600">Available Cars (<?php echo $available_count; ?>)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" id="show-unavailable">
                                <span class="text-gray-600">Unavailable Cars (<?php echo count($cars) - $available_count; ?>)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Passenger Capacity -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-gray-700">Passenger Capacity</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500 capacity-filter" value="1-4">
                                <span class="text-gray-600">1-4 passengers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500 capacity-filter" value="5-7">
                                <span class="text-gray-600">5-7 passengers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500 capacity-filter" value="8+">
                                <span class="text-gray-600">8+ passengers</span>
                            </label>
                        </div>
                    </div>

                    <!-- Transmission -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-gray-700">Transmission</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500 transmission-filter" value="automatic">
                                <span class="text-gray-600">Automatic</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500 transmission-filter" value="manual">
                                <span class="text-gray-600">Manual</span>
                            </label>
                        </div>
                    </div>

                    <button class="btn-primary w-full" onclick="applyFilters()">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="lg:w-3/4">
                <!-- Results Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800" id="results-count">
                            Showing <?php echo count($paginated_cars); ?> of <?php echo $total_cars; ?> cars (<?php echo $available_count; ?> available)
                        </h2>
                        <p class="text-gray-600 mt-1">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?> • 
                            Search from <?php echo date('M j, Y', strtotime($pickup_date)); ?> 
                            to <?php echo date('M j, Y', strtotime($return_date)); ?>
                            <?php if ($pickup_location): ?>
                                • Pickup: <?php echo formatLocationName($pickup_location); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
<!-- Results Grid -->
<div id="results-grid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <?php if (empty($paginated_cars)): ?>
        <div class="col-span-full text-center py-12">
            <i class="fas fa-car text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-700 mb-2">No vehicles found</h3>
            <p class="text-gray-600 mb-6">Please try different search criteria.</p>
            <a href="index.php" class="btn-primary">
                <i class="fas fa-search mr-2"></i>New Search
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($paginated_cars as $car): ?>
            <div class="vehicle-card bg-white rounded-xl shadow-md overflow-hidden <?php echo $car['is_available'] ? '' : 'unavailable-card'; ?>" 
                 data-available="<?php echo $car['is_available']; ?>"
                 data-price="<?php echo $car['rate_per_day']; ?>"
                 data-capacity="<?php echo $car['passenger_seater']; ?>"
                 data-transmission="<?php echo strtolower($car['transmission']); ?>"
                 data-car-id="<?php echo $car['car_id']; ?>">
                
                <!-- Car Image Container with Fixed Size -->
                <div class="car-image-container relative">
                    <?php if (!$car['is_available']): ?>
                        <div class="unavailable-overlay">
                            <span class="unavailable-badge">Not Available</span>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                        $image_src = !empty($car['car_image']) 
                            ? 'uploads/cars/' . $car['car_image']   // inside admin/uploads/cars/
                            : '../assets/img/no-image.png';         // go up one level to assets/img/
                    ?>
                    <img class="car-image" 
                        src="<?php echo $image_src; ?>" 
                        alt="<?php echo htmlspecialchars($car['car_name']); ?>"
                        loading="lazy">
                </div>

                <!-- Card Content -->
                <div class="card-content p-6">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($car['car_name']); ?></h3>
                            <div class="flex items-center gap-2 mt-1">
                                <p class="text-gray-600">
                                    <?php echo getVehicleType($car['passenger_seater']); ?> • 
                                    <?php echo ucfirst($car['transmission']); ?> • 
                                    <?php echo $car['passenger_seater']; ?> passengers
                                </p>
                                <?php if ($car['is_available']): ?>
                                    <span class="car-status-available">Available</span>
                                <?php else: ?>
                                    <?php if ($car['status'] == 'Available'): ?>
                                        <span class="car-status-booked">Booked</span>
                                    <?php else: ?>
                                        <span class="car-status-maintenance"><?php echo ucfirst($car['status']); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="price-badge">₱<?php echo number_format($car['rate_24h']); ?>/day</div>
                    </div>
                    
                    <div class="flex flex-wrap gap-2 mb-4">
                        <span class="feature-badge">A/C</span>
                        <span class="feature-badge">GPS</span>
                        <span class="feature-badge">Bluetooth</span>
                        <span class="feature-badge">Insurance</span>
                        <?php if ($car['passenger_seater'] >= 7): ?>
                            <span class="feature-badge">Family Size</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($car['description'])): ?>
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($car['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 mr-2">
                            <?php 
                            // Simulate rating based on car_id for demo
                            $rating = 4.5 + (($car['car_id'] % 10) / 20);
                            echo getStarRating($rating);
                            ?>
                        </div>
                        <span class="text-gray-600 text-sm"><?php echo number_format($rating, 1); ?> (<?php echo rand(15, 200); ?> reviews)</span>
                    </div>
                    
                    <div class="flex gap-3">
                        <?php if ($car['is_available']): ?>
                            <!-- Updated to use selectVehicleByName since we have the car name readily available -->
                            <button class="btn-primary flex-1" onclick="selectVehicleByName('<?php echo htmlspecialchars($car['car_name'], ENT_QUOTES); ?>')">
                                <i class="fas fa-car mr-2"></i>Book Now 
                            </button>
                            <button class="btn-secondary px-4" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($car)); ?>)">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            
                        <?php else: ?>
                            <button class="bg-gray-400 text-white px-6 py-3 rounded-lg flex-1 cursor-not-allowed" disabled>
                                <i class="fas fa-ban mr-2"></i>
                                <?php echo ($car['status'] == 'Available') ? 'Booked' : 'Unavailable'; ?>
                            </button>
                            <button class="btn-secondary px-4" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($car)); ?>)">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <!-- Previous Page -->
                        <?php if ($page > 1): ?>
                            <a href="<?php echo buildPaginationUrl($page - 1); ?>" class="flex items-center">
                                <i class="fas fa-chevron-left mr-1"></i> Previous
                            </a>
                        <?php else: ?>
                            <span class="disabled flex items-center">
                                <i class="fas fa-chevron-left mr-1"></i> Previous
                            </span>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        // Show first page if we're not starting from 1
                        if ($start_page > 1): ?>
                            <a href="<?php echo buildPaginationUrl(1); ?>">1</a>
                            <?php if ($start_page > 2): ?>
                                <span class="disabled">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Page range -->
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="<?php echo buildPaginationUrl($i); ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <!-- Show last page if we're not ending at the last page -->
                        <?php if ($end_page < $total_pages): ?>
                            <?php if ($end_page < $total_pages - 1): ?>
                                <span class="disabled">...</span>
                            <?php endif; ?>
                            <a href="<?php echo buildPaginationUrl($total_pages); ?>"><?php echo $total_pages; ?></a>
                        <?php endif; ?>

                        <!-- Next Page -->
                        <?php if ($page < $total_pages): ?>
                            <a href="<?php echo buildPaginationUrl($page + 1); ?>" class="flex items-center">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        <?php else: ?>
                            <span class="disabled flex items-center">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- Car Details Modal -->
    <div id="carModal" class="modal-overlay">
        <div class="modal-content">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="flex justify-between items-start mb-6">
                    <h2 id="modalTitle" class="text-2xl font-bold text-gray-800"></h2>
                    <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Car Image -->
                    <div class="order-1 md:order-1">
                        <div class="relative">
                            <img id="modalImage" class="w-full h-64 object-cover rounded-lg" alt="">
                            <div id="modalAvailabilityBadge" class="absolute top-4 right-4"></div>
                        </div>
                    </div>

                    <!-- Car Details -->
                    <div class="order-2 md:order-2 space-y-4">
                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Vehicle Information</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Type:</span>
                                    <span id="modalType" class="font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Passengers:</span>
                                    <span id="modalPassengers" class="font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Transmission:</span>
                                    <span id="modalTransmission" class="font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fuel Type:</span>
                                    <span id="modalFuelType" class="font-medium">Gasoline</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Pricing</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Daily Rate:</span>
                                    <span id="modalDailyRate" class="font-bold text-blue-600"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">6 hours Rate:</span>
                                    <span id="modal6Rate" class="font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">8 hours Rate:</span>
                                    <span id="modal8Rate" class="font-medium"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">12 hours Rate:</span>
                                    <span id="modal12Rate" class="font-medium"></span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Features & Amenities</h3>
                            <div id="modalFeatures" class="flex flex-wrap gap-2">
                                <!-- Features will be populated dynamically -->
                            </div>
                        </div>

                        <div>
                            <h3 class="font-semibold text-gray-800 mb-2">Customer Rating</h3>
                            <div class="flex items-center gap-2">
                                <div id="modalRating" class="flex text-yellow-400"></div>
                                <span id="modalRatingText" class="text-sm text-gray-600"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Description</h3>
                    <p id="modalDescription" class="text-gray-600 text-sm leading-relaxed"></p>
                </div>

                <!-- Terms & Conditions -->
                <div class="mt-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Terms & Conditions</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Valid driver's license required</li>
                        <li>• Security deposit: ₱5,000 - ₱15,000 (depending on vehicle)</li>
                        <li>• Fuel: Return with same fuel level</li>
                        <li>• Late return fee: ₱500/hour</li>
                        <li>• Damage assessment will be charged separately</li>
                        <li>• 24/7 roadside assistance included</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4 mt-8 pt-6 border-t">
                    <button id="modalBookButton" class="btn-primary flex-1" onclick="selectVehicleFromModal()">
                        <i class="fas fa-car mr-2"></i>Book This Vehicle
                    </button>
                    <button class="btn-secondary px-6" onclick="closeModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
  <!-- FIXED BOOKING MODAL with larger size -->
<div id="booking-modal" class="modal booking-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal101('booking-modal')">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="p-8">
            <h2 class="text-4xl font-bold mb-6 text-gray-800 pr-12">Book Your Vehicle</h2>
            
            <form class="space-y-8" id="booking-form">
                <input type="hidden" id="selected-vehicle" name="selected_vehicle" value="">
                
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3><i class="fas fa-user mr-3"></i>Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="customer-name" class="block text-gray-700 font-medium mb-2">Full Name *</label>
                            <input type="text" id="customer-name" name="customer_name" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                        <div>
                            <label for="customer-phone" class="block text-gray-700 font-medium mb-2">Phone Number *</label>
                            <input type="tel" id="customer-phone" name="customer_phone" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                        <div>
                            <label for="customer-email" class="block text-gray-700 font-medium mb-2">Email Address *</label>
                            <input type="email" id="customer-email" name="customer_email" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                        <div>
                            <label for="license-number" class="block text-gray-700 font-medium mb-2">Driver's License No. *</label>
                            <input type="text" id="license-number" name="license_number" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Rental Details Section -->
                <div class="form-section">
                    <h3><i class="fas fa-calendar-alt mr-3"></i>Rental Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="start-date" class="block text-gray-700 font-medium mb-2">Pickup Date *</label>
                            <input type="date" id="start-date" name="start_date" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                        <div>
                            <label for="end-date" class="block text-gray-700 font-medium mb-2">Return Date *</label>
                            <input type="date" id="end-date" name="end_date" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                        <div>
                            <label for="start-time" class="block text-gray-700 font-medium mb-2">Pickup Time *</label>
                            <input type="time" id="start-time" name="start_time" value="09:00" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                        <div>
                            <label for="end-time" class="block text-gray-700 font-medium mb-2">Return Time *</label>
                            <input type="time" id="end-time" name="end_time" value="09:00" 
                                   class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="pickup-location-modal" class="block text-gray-700 font-medium mb-2">Pickup Location *</label>
                            <select id="pickup-location-modal" name="pickup_location" 
                                    class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg" 
                                    required>
                                <option value="">Select Location</option>
                                <option value="gensan-airport">GenSan Airport</option>
                                <option value="downtown-gensan">Downtown GenSan</option>
                                <option value="kcc-mall">KCC Mall</option>
                                <option value="robinsons-place">Robinson's Place GenSan</option>
                                <option value="sm-city-gensan">SM City General Santos</option>
                            </select>
                        </div>
                        <div>
                            <label for="return-location-modal" class="block text-gray-700 font-medium mb-2">Return Location</label>
                            <select id="return-location-modal" name="return_location" 
                                    class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg">
                                <option value="">Same as pickup</option>
                                <option value="store">Store</option>
                                <option value="gensan-airport">GenSan Airport</option>
                                <option value="downtown-gensan">Downtown GenSan</option>
                                <option value="kcc-mall">KCC Mall</option>
                                <option value="robinsons-place">Robinson's Place GenSan</option>
                                <option value="sm-city-gensan">SM City General Santos</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Document Upload Section -->
                <div class="form-section">
                    <h3><i class="fas fa-upload mr-3"></i>Upload License/ID Copy</h3>
                    <div class="image-upload-container" id="imageUploadContainer" onclick="document.getElementById('upload-image').click()">
                        <input type="file" id="upload-image" name="upload_image" accept="image/*" class="image-upload-input" onchange="previewImage(this)">
                        <div class="image-upload-content">
                            <i class="fas fa-cloud-upload-alt text-6xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600 text-xl font-medium mb-3">Upload Driver's License or Valid ID</p>
                            <p class="text-gray-500 text-base mb-6">Drag and drop your image here, or click to browse</p>
                            <div class="text-sm text-gray-400">
                                <span class="bg-gray-100 px-3 py-2 rounded">JPG, PNG, PDF up to 10MB</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="image-preview hidden" id="imagePreview">
                        <img id="previewImage" src="" alt="Preview" class="max-w-full h-auto">
                        <div class="mt-6 space-x-4">
                            <button type="button" class="btn btn-secondary" onclick="changeImage()">
                                <i class="fas fa-edit mr-2"></i> Change
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="removeImage()">
                                <i class="fas fa-trash mr-2"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
                  <!-- Cost Summary -->
                  <div id="cost-summary" class="cost-summary hidden">
                    <h3 class="text-xl font-semibold text-blue-800 mb-4"><i class="fas fa-calculator mr-2"></i>Cost Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="font-medium">Vehicle:</span>
                            <span id="summary-vehicle" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="font-medium">Duration:</span>
                            <span id="summary-duration" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="font-medium">Rate:</span>
                            <span id="summary-rate" class="font-semibold">-</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="font-medium">Location Charges:</span>
                            <span id="summary-location" class="font-semibold">₱0</span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-t-2 border-blue-500">
                            <span class="text-lg font-bold">Total Cost:</span>
                            <span id="summary-total" class="text-lg font-bold text-green-600">₱0</span>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex gap-6 pt-8 border-t">
                    <button type="submit" class="btn btn-primary flex-1 py-4 text-xl">
                        <i class="fas fa-paper-plane mr-3"></i>Submit Booking Request
                    </button>
                    <button type="button" class="btn btn-secondary flex-1 py-4 text-xl" onclick="closeModal101('booking-modal')">
                        <i class="fas fa-times mr-3"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div id="message-modal" class="modal message-modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeModal101('message-modal')">
            <i class="fas fa-times"></i>
        </button>
        <div id="message-content" class="p-8 text-center">
            <!-- Message content will be populated dynamically -->
        </div>
    </div>
</div>
    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 GenSan Car Rentals. All rights reserved.</p>
        </div>
    </footer>
    <script src="js/available.js"></script>
    <script src="js/modal.js"></script>
    
</body>
</html>