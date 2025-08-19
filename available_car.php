<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - GenSan Car Rentals</title>
    <meta name="description" content="Available car rentals in General Santos City based on your search criteria.">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .search-summary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        .vehicle-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .vehicle-card:hover {
            transform: translateY(-5px);
            border-color: #3b82f6;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .filter-sidebar {
            background: #f8fafc;
            border-radius: 12px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: white;
            color: #3b82f6;
            border: 2px solid #3b82f6;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background: #3b82f6;
            color: white;
        }
        
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .price-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 18px;
        }
        
        .feature-badge {
            background: #e0f2fe;
            color: #0277bd;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .sort-dropdown {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px 16px;
        }
        
        .empty-state {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-gray-800">GenSan Car Rentals</a>
            <div class="space-x-4 hidden md:flex">
                <a href="index.php#home" class="text-gray-600 hover:text-blue-500 transition-colors">Home</a>
                <a href="index.php#vehicles" class="text-gray-600 hover:text-blue-500 transition-colors">Vehicles</a>
                <a href="index.php#services" class="text-gray-600 hover:text-blue-500 transition-colors">Services</a>
                <a href="index.php#about" class="text-gray-600 hover:text-blue-500 transition-colors">About Us</a>
                <a href="index.php#contact" class="text-gray-600 hover:text-blue-500 transition-colors">Contact</a>
            </div>
            <button class="md:hidden">
                <i class="fas fa-bars text-xl text-gray-800"></i>
            </button>
        </nav>
    </header>

    <!-- Search Summary Section -->
    <section class="search-summary text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Available Cars</h1>
                    <div class="flex flex-wrap gap-4 text-sm opacity-90">
                        <span><i class="fas fa-map-marker-alt mr-2"></i><span id="search-location">GenSan Airport</span></span>
                        <span><i class="fas fa-calendar mr-2"></i><span id="search-dates">Dec 20, 2024 - Dec 25, 2024</span></span>
                        <span><i class="fas fa-clock mr-2"></i><span id="search-times">09:00 - 09:00</span></span>
                    </div>
                </div>
                <button class="btn-secondary mt-4 md:mt-0" onclick="modifySearch()">
                    <i class="fas fa-edit mr-2"></i>Modify Search
                </button>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Filters Sidebar -->
            <aside class="lg:w-1/4">
                <div class="filter-sidebar p-6 sticky top-24">
                    <h3 class="text-xl font-bold mb-6 text-gray-800">Filter Results</h3>
                    
                    <!-- Price Range Filter -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-gray-700">Price Range (per day)</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="0-2000">
                                <span class="text-gray-600">₱0 - ₱2,000</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="2000-3500">
                                <span class="text-gray-600">₱2,000 - ₱3,500</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="3500-5000">
                                <span class="text-gray-600">₱3,500 - ₱5,000</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="5000+">
                                <span class="text-gray-600">₱5,000+</span>
                            </label>
                        </div>
                    </div>

                    <!-- Vehicle Type Filter -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-gray-700">Vehicle Type</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="sedan">
                                <span class="text-gray-600">Sedan</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="suv">
                                <span class="text-gray-600">SUV/MPV</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="van">
                                <span class="text-gray-600">Van</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="pickup">
                                <span class="text-gray-600">Pickup Truck</span>
                            </label>
                        </div>
                    </div>

                    <!-- Passenger Capacity -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-gray-700">Passenger Capacity</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="1-4">
                                <span class="text-gray-600">1-4 passengers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="5-7">
                                <span class="text-gray-600">5-7 passengers</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="8+">
                                <span class="text-gray-600">8+ passengers</span>
                            </label>
                        </div>
                    </div>

                    <!-- Transmission -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-gray-700">Transmission</h4>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="automatic">
                                <span class="text-gray-600">Automatic</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" class="mr-3 text-blue-500" value="manual">
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
                        <h2 class="text-2xl font-bold text-gray-800" id="results-count">12 cars available</h2>
                        <p class="text-gray-600 mt-1">Best matches for your search criteria</p>
                    </div>
                    <div class="flex gap-4 mt-4 md:mt-0">
                        <select class="sort-dropdown" id="sort-options" onchange="sortResults()">
                            <option value="recommended">Recommended</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                            <option value="rating">Customer Rating</option>
                            <option value="newest">Newest First</option>
                        </select>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="loading-state" class="text-center py-12 hidden">
                    <div class="loading-spinner"></div>
                    <p class="text-gray-600 mt-4">Searching for available vehicles...</p>
                </div>

                <!-- Results Grid -->
                <div id="results-grid" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Sample Vehicle Cards -->
                    <div class="vehicle-card bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="md:flex">
                            <div class="md:w-2/5">
                                <img class="w-full h-48 md:h-full object-cover" src="https://images.unsplash.com/photo-1549924231-f129b911e442?w=400" alt="Toyota Vios">
                            </div>
                            <div class="md:w-3/5 p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800">Toyota Vios</h3>
                                        <p class="text-gray-600">Sedan • Automatic • 4 passengers</p>
                                    </div>
                                    <div class="price-badge">₱2,500/day</div>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="feature-badge">A/C</span>
                                    <span class="feature-badge">GPS</span>
                                    <span class="feature-badge">Bluetooth</span>
                                    <span class="feature-badge">Insurance</span>
                                </div>
                                
                                <div class="flex items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 text-sm">4.9 (124 reviews)</span>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button class="btn-primary flex-1" onclick="selectVehicle('toyota-vios')">
                                        <i class="fas fa-car mr-2"></i>Select Vehicle
                                    </button>
                                    <button class="btn-secondary px-4" onclick="viewDetails('toyota-vios')">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vehicle-card bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="md:flex">
                            <div class="md:w-2/5">
                                <img class="w-full h-48 md:h-full object-cover" src="https://images.unsplash.com/photo-1580273916550-e323be2ae537?w=400" alt="Toyota Innova">
                            </div>
                            <div class="md:w-3/5 p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800">Toyota Innova</h3>
                                        <p class="text-gray-600">MPV • Automatic • 7 passengers</p>
                                    </div>
                                    <div class="price-badge">₱3,800/day</div>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="feature-badge">A/C</span>
                                    <span class="feature-badge">GPS</span>
                                    <span class="feature-badge">Bluetooth</span>
                                    <span class="feature-badge">Insurance</span>
                                    <span class="feature-badge">Family Size</span>
                                </div>
                                
                                <div class="flex items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 text-sm">4.8 (89 reviews)</span>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button class="btn-primary flex-1" onclick="selectVehicle('toyota-innova')">
                                        <i class="fas fa-car mr-2"></i>Select Vehicle
                                    </button>
                                    <button class="btn-secondary px-4" onclick="viewDetails('toyota-innova')">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vehicle-card bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="md:flex">
                            <div class="md:w-2/5">
                                <img class="w-full h-48 md:h-full object-cover" src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=400" alt="Honda City">
                            </div>
                            <div class="md:w-3/5 p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800">Honda City</h3>
                                        <p class="text-gray-600">Sedan • Automatic • 4 passengers</p>
                                    </div>
                                    <div class="price-badge">₱2,300/day</div>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="feature-badge">A/C</span>
                                    <span class="feature-badge">GPS</span>
                                    <span class="feature-badge">Bluetooth</span>
                                    <span class="feature-badge">Insurance</span>
                                    <span class="feature-badge">Fuel Efficient</span>
                                </div>
                                
                                <div class="flex items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="far fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 text-sm">4.6 (67 reviews)</span>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button class="btn-primary flex-1" onclick="selectVehicle('honda-city')">
                                        <i class="fas fa-car mr-2"></i>Select Vehicle
                                    </button>
                                    <button class="btn-secondary px-4" onclick="viewDetails('honda-city')">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="vehicle-card bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="md:flex">
                            <div class="md:w-2/5">
                                <img class="w-full h-48 md:h-full object-cover" src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=400" alt="Ford Everest">
                            </div>
                            <div class="md:w-3/5 p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800">Ford Everest</h3>
                                        <p class="text-gray-600">SUV • Automatic • 7 passengers</p>
                                    </div>
                                    <div class="price-badge">₱4,200/day</div>
                                </div>
                                
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span class="feature-badge">A/C</span>
                                    <span class="feature-badge">GPS</span>
                                    <span class="feature-badge">4WD</span>
                                    <span class="feature-badge">Insurance</span>
                                    <span class="feature-badge">Premium</span>
                                </div>
                                
                                <div class="flex items-center mb-4">
                                    <div class="flex text-yellow-400 mr-2">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-gray-600 text-sm">4.9 (156 reviews)</span>
                                </div>
                                
                                <div class="flex gap-3">
                                    <button class="btn-primary flex-1" onclick="selectVehicle('ford-everest')">
                                        <i class="fas fa-car mr-2"></i>Select Vehicle
                                    </button>
                                    <button class="btn-secondary px-4" onclick="viewDetails('ford-everest')">
                                        <i class="fas fa-info-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State (hidden by default) -->
                <div id="empty-state" class="empty-state hidden">
                    <i class="fas fa-car text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-700 mb-2">No vehicles available</h3>
                    <p class="text-gray-600 mb-6">Try adjusting your search criteria or dates to find available cars.</p>
                    <button class="btn-primary" onclick="modifySearch()">
                        <i class="fas fa-search mr-2"></i>Modify Search
                    </button>
                </div>

                <!-- Load More Button -->
                <div class="text-center mt-8">
                    <button class="btn-secondary px-8 py-3" onclick="loadMoreResults()">
                        <i class="fas fa-plus mr-2"></i>Load More Results
                    </button>
                </div>
            </main>
        </div>
    </div>

    <!-- Footer placeholder -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2024 GenSan Car Rentals. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Initialize search results page
        document.addEventListener('DOMContentLoaded', function() {
            // Get URL parameters from the search form
            const urlParams = new URLSearchParams(window.location.search);
            
            // Update search summary with actual values
            const location = urlParams.get('pickup_location') || 'GenSan Airport';
            const pickupDate = urlParams.get('pickup_date') || 'Dec 20, 2024';
            const returnDate = urlParams.get('return_date') || 'Dec 25, 2024';
            const pickupTime = urlParams.get('pickup_time') || '09:00';
            const returnTime = urlParams.get('return_time') || '09:00';
            
            // Update the display
            document.getElementById('search-location').textContent = formatLocation(location);
            document.getElementById('search-dates').textContent = `${formatDate(pickupDate)} - ${formatDate(returnDate)}`;
            document.getElementById('search-times').textContent = `${pickupTime} - ${returnTime}`;
        });

        function formatLocation(location) {
            const locationMap = {
                'gensan-airport': 'GenSan Airport',
                'downtown-gensan': 'Downtown GenSan',
                'kcc-mall': 'KCC Mall',
                'robinsons-place': 'Robinson\'s Place GenSan',
                'sm-city-gensan': 'SM City General Santos'
            };
            return locationMap[location] || location;
        }

        function formatDate(dateString) {
            if (!dateString) return 'Date not set';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric' 
            });
        }

        function modifySearch() {
            // Redirect back to home page with current search parameters
            window.location.href = 'index.php#home';
        }

        function selectVehicle(vehicleId) {
            // Store selected vehicle and redirect to booking
            sessionStorage.setItem('selectedVehicle', vehicleId);
            
            // Get current search parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            // Redirect to index.php and open booking modal
            window.location.href = `index.php?selected_vehicle=${vehicleId}&${urlParams.toString()}#booking`;
        }

        function viewDetails(vehicleId) {
            // Open detailed view modal or navigate to details page
            alert(`Viewing details for ${vehicleId}. This would open a detailed view modal.`);
        }

        function applyFilters() {
            // Collect filter values and update results
            const priceFilters = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'))
                .map(cb => cb.value);
            
            console.log('Applying filters:', priceFilters);
            
            // Show loading state
            document.getElementById('loading-state').classList.remove('hidden');
            document.getElementById('results-grid').classList.add('hidden');
            
            // Simulate API call
            setTimeout(() => {
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('results-grid').classList.remove('hidden');
                
                // Update results count
                document.getElementById('results-count').textContent = `8 cars available`;
            }, 1500);
        }

        function sortResults() {
            const sortBy = document.getElementById('sort-options').value;
            console.log('Sorting by:', sortBy);
            
            // Show loading state
            document.getElementById('loading-state').classList.remove('hidden');
            document.getElementById('results-grid').classList.add('hidden');
            
            // Simulate sorting
            setTimeout(() => {
                document.getElementById('loading-state').classList.add('hidden');
                document.getElementById('results-grid').classList.remove('hidden');
            }, 800);
        }

        function loadMoreResults() {
            // Simulate loading more results
            const button = event.target;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-plus mr-2"></i>Load More Results';
                button.disabled = false;
                
                // Update results count
                const currentCount = parseInt(document.getElementById('results-count').textContent.split(' ')[0]);
                document.getElementById('results-count').textContent = `${currentCount + 4} cars available`;
            }, 2000);
        }
    </script>
</body>
</html>