<?php
include('config/db.php');

// Get search parameters
$pickup_location = $_GET['pickup_location'] ?? '';
$pickup_date = $_GET['pickup_date'] ?? '';
$pickup_time = $_GET['pickup_time'] ?? '';
$return_date = $_GET['return_date'] ?? '';
$return_time = $_GET['return_time'] ?? '';

// Validate required parameters
if (empty($pickup_date) || empty($return_date)) {
    header('Location: index.php');
    exit;
}

// Combine date and time for comparison
$requested_start = $pickup_date . ' ' . $pickup_time;
$requested_end = $return_date . ' ' . $return_time;

// Query to get available cars
$sql = "
SELECT c.*, 
       CASE 
           WHEN EXISTS (
               SELECT 1 
               FROM bookings b 
               WHERE b.car_id = c.car_id 
               AND b.status IN ('pending', 'active', 'confirmed', 'ongoing', 'completed')
               AND (
                   (STR_TO_DATE(CONCAT(b.start_date, ' ', b.start_time), '%Y-%m-%d %H:%i:%s') <= STR_TO_DATE('$requested_start', '%Y-%m-%d %H:%i:%s')
                    AND STR_TO_DATE(CONCAT(b.end_date, ' ', b.end_time), '%Y-%m-%d %H:%i:%s') > STR_TO_DATE('$requested_start', '%Y-%m-%d %H:%i:%s'))
                   OR 
                   (STR_TO_DATE(CONCAT(b.start_date, ' ', b.start_time), '%Y-%m-%d %H:%i:%s') < STR_TO_DATE('$requested_end', '%Y-%m-%d %H:%i:%s')
                    AND STR_TO_DATE(CONCAT(b.end_date, ' ', b.end_time), '%Y-%m-%d %H:%i:%s') >= STR_TO_DATE('$requested_end', '%Y-%m-%d %H:%i:%s'))
                   OR
                   (STR_TO_DATE(CONCAT(b.start_date, ' ', b.start_time), '%Y-%m-%d %H:%i:%s') >= STR_TO_DATE('$requested_start', '%Y-%m-%d %H:%i:%s')
                    AND STR_TO_DATE(CONCAT(b.end_date, ' ', b.end_time), '%Y-%m-%d %H:%i:%s') <= STR_TO_DATE('$requested_end', '%Y-%m-%d %H:%i:%s'))
               )
           ) THEN 0
           ELSE 1
       END AS is_available
FROM cars c 
WHERE c.status = 'active'
ORDER BY is_available DESC, c.rate_per_day ASC
";


$result = $conn->query($sql);
$cars = [];
$available_count = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
        if ($row['is_available'] == 1) {
            $available_count++;
        }
    }
}

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
    <style>
     
    </style>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-gray-800">GenSan Car Rentals</a>
            <div class="space-x-4 hidden md:flex">
                <a href="index.php" class="text-gray-600 hover:text-blue-500 transition-colors">Home</a>
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
                    <option value="gensan-airport">GenSan Airport</option>
                    <option value="downtown-gensan">Downtown GenSan</option>
                    <option value="kcc-mall">KCC Mall</option>
                    <option value="robinsons-place">Robinson's Place GenSan</option>
                    <option value="sm-city-gensan">SM City General Santos</option>
                </select>
            </div>

            <div class="form-group">
                <label for="pickup-date" class="block text-gray-700">Pickup Date</label>
                <input type="date" id="pickup-date" name="pickup_date" required>
            </div>

            <div class="form-group">
                <label for="pickup-time" class="block text-gray-700">Pickup Time</label>
                <input type="time" id="pickup-time" name="pickup_time" value="09:00" required>
            </div>

            <div class="form-group">
                <label for="return-date" class="block text-gray-700">Return Date</label>
                <input type="date" id="return-date" name="return_date" required>
            </div>

            <div class="form-group">
                <label for="return-time" class="block text-gray-700">Return Time</label>
                <input type="time" id="return-time" name="return_time" value="09:00" required>
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
                                <span class="text-gray-600">Unavailable Cars</span>
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
                            <?php echo count($cars); ?> cars found (<?php echo $available_count; ?> available)
                        </h2>
                        <p class="text-gray-600 mt-1">Best matches for your search criteria</p>
                    </div>
                    <div class="flex gap-4 mt-4 md:mt-0">
                        <select class="sort-dropdown border border-gray-300 rounded-lg px-4 py-2" id="sort-options" onchange="sortResults()">
                            <option value="availability">Availability First</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="capacity">Passenger Capacity</option>
                        </select>
                    </div>
                </div>

                <!-- Results Grid -->
                <div id="results-grid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <?php if (empty($cars)): ?>
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-car text-6xl text-gray-400 mb-4"></i>
                            <h3 class="text-2xl font-bold text-gray-700 mb-2">No vehicles found</h3>
                            <p class="text-gray-600 mb-6">Please try different search criteria.</p>
                            <a href="index.php" class="btn-primary">
                                <i class="fas fa-search mr-2"></i>New Search
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($cars as $car): ?>
                            <div class="vehicle-card bg-white rounded-xl shadow-md overflow-hidden <?php echo $car['is_available'] ? '' : 'unavailable-card'; ?>" 
                                 data-available="<?php echo $car['is_available']; ?>"
                                 data-price="<?php echo $car['rate_per_day']; ?>"
                                 data-capacity="<?php echo $car['passenger_seater']; ?>"
                                 data-transmission="<?php echo strtolower($car['transmission']); ?>">
                                
                                <?php if (!$car['is_available']): ?>
                                    <div class="unavailable-overlay">
                                        <span class="unavailable-badge">Not Available</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="md:flex">
                                <div class="md:w-2/5">
                                    <?php 
                                        $image_src = !empty($car['car_image']) 
                                            ? 'uploads/cars/' . $car['car_image']   // inside admin/uploads/cars/
                                            : '../assets/img/no-image.png';         // go up one level to assets/img/
                                    ?>
                                    <img class="w-full h-48 md:h-full object-cover" 
                                        src="<?php echo $image_src; ?>" 
                                        alt="<?php echo htmlspecialchars($car['car_name']); ?>">
                                </div>
                                </div>


                                    <div class="md:w-3/5 p-6">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($car['car_name']); ?></h3>
                                                <p class="text-gray-600">
                                                    <?php echo getVehicleType($car['passenger_seater']); ?> • 
                                                    <?php echo ucfirst($car['transmission']); ?> • 
                                                    <?php echo $car['passenger_seater']; ?> passengers
                                                </p>
                                            </div>
                                            <div class="price-badge">₱<?php echo number_format($car['rate_per_day']); ?>/day</div>
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
                                            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($car['description']); ?></p>
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
                                                <button class="btn-primary flex-1" onclick="selectVehicle('<?php echo $car['car_id']; ?>')">
                                                    <i class="fas fa-car mr-2"></i>Select Vehicle
                                                </button>
                                                <button class="btn-secondary px-4" onclick="viewDetails('<?php echo $car['car_id']; ?>')">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="bg-gray-400 text-white px-6 py-3 rounded-lg flex-1" disabled>
                                                    <i class="fas fa-ban mr-2"></i>Unavailable
                                                </button>
                                                <button class="btn-secondary px-4" onclick="viewDetails('<?php echo $car['car_id']; ?>')">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 GenSan Car Rentals. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize filters
            updateResultsCount();
        });

        function selectVehicle(car_id) {
            // Store selected vehicle and booking details
            const urlParams = new URLSearchParams(window.location.search);
            const bookingData = {
                car_id: car_id,
                pickup_location: urlParams.get('pickup_location'),
                pickup_date: urlParams.get('pickup_date'),
                pickup_time: urlParams.get('pickup_time'),
                return_date: urlParams.get('return_date'),
                return_time: urlParams.get('return_time')
            };
            
            // Store in sessionStorage for the booking form
            sessionStorage.setItem('bookingData', JSON.stringify(bookingData));
            
            // Redirect to booking page or show booking modal
            window.location.href = 'booking.php?' + new URLSearchParams(bookingData).toString();
        }

        function viewDetails(car_id) {
            window.location.href = 'car_details.php?id=' + car_id;
        }

        function applyFilters() {
            const showAvailable = document.getElementById('show-available').checked;
            const showUnavailable = document.getElementById('show-unavailable').checked;
            
            const priceFilters = Array.from(document.querySelectorAll('.price-filter:checked')).map(cb => cb.value);
            const capacityFilters = Array.from(document.querySelectorAll('.capacity-filter:checked')).map(cb => cb.value);
            const transmissionFilters = Array.from(document.querySelectorAll('.transmission-filter:checked')).map(cb => cb.value);

            const cards = document.querySelectorAll('.vehicle-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const isAvailable = card.dataset.available === '1';
                const price = parseInt(card.dataset.price);
                const capacity = parseInt(card.dataset.capacity);
                const transmission = card.dataset.transmission;

                let show = true;

                // Availability filter
                if (!showAvailable && isAvailable) show = false;
                if (!showUnavailable && !isAvailable) show = false;

                // Price filter
                if (priceFilters.length > 0) {
                    let priceMatch = false;
                    priceFilters.forEach(filter => {
                        if (filter === '0-2000' && price <= 2000) priceMatch = true;
                        if (filter === '2000-3500' && price > 2000 && price <= 3500) priceMatch = true;
                        if (filter === '3500-5000' && price > 3500 && price <= 5000) priceMatch = true;
                        if (filter === '5000+' && price > 5000) priceMatch = true;
                    });
                    if (!priceMatch) show = false;
                }

                // Capacity filter
                if (capacityFilters.length > 0) {
                    let capacityMatch = false;
                    capacityFilters.forEach(filter => {
                        if (filter === '1-4' && capacity <= 4) capacityMatch = true;
                        if (filter === '5-7' && capacity >= 5 && capacity <= 7) capacityMatch = true;
                        if (filter === '8+' && capacity >= 8) capacityMatch = true;
                    });
                    if (!capacityMatch) show = false;
                }

                // Transmission filter
                if (transmissionFilters.length > 0) {
                    if (!transmissionFilters.includes(transmission)) show = false;
                }

                card.style.display = show ? 'block' : 'none';
                if (show) visibleCount++;
            });

            updateResultsCount(visibleCount);
        }

        function sortResults() {
            const sortBy = document.getElementById('sort-options').value;
            const container = document.getElementById('results-grid');
            const cards = Array.from(container.children);

            cards.sort((a, b) => {
                const aAvailable = parseInt(a.dataset.available);
                const bAvailable = parseInt(b.dataset.available);
                const aPrice = parseInt(a.dataset.price);
                const bPrice = parseInt(b.dataset.price);
                const aCapacity = parseInt(a.dataset.capacity);
                const bCapacity = parseInt(b.dataset.capacity);

                switch (sortBy) {
                    case 'availability':
                        if (aAvailable !== bAvailable) return bAvailable - aAvailable;
                        return aPrice - bPrice;
                    case 'price-low':
                        return aPrice - bPrice;
                    case 'price-high':
                        return bPrice - aPrice;
                    case 'capacity':
                        return bCapacity - aCapacity;
                    default:
                        return 0;
                }
            });

            cards.forEach(card => container.appendChild(card));
        }

        function updateResultsCount(count = null) {
            if (count === null) {
                const totalCards = document.querySelectorAll('.vehicle-card').length;
                const availableCards = document.querySelectorAll('.vehicle-card[data-available="1"]').length;
                document.getElementById('results-count').textContent = 
                    `${totalCards} cars found (${availableCards} available)`;
            } else {
                document.getElementById('results-count').textContent = `${count} cars shown`;
            }
        }

        // Add event listeners for real-time filtering
        document.addEventListener('DOMContentLoaded', function() {
            const filterInputs = document.querySelectorAll('input[type="checkbox"]');
            filterInputs.forEach(input => {
                input.addEventListener('change', applyFilters);
            });
        });
    </script>
</body>
</html>