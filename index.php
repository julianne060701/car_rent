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
         <form id="quick-booking-form" class="booking-form">
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

    <!-- Featured Vehicles Section - This will be populated by JavaScript -->
    <section class="featured-vehicles py-16" id="vehicles">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Our Popular Vehicles</h2>
            
            <!-- Loading State -->
            <div id="vehicles-loading" class="text-center py-12">
                <div class="loading-spinner"></div>
                <p class="text-gray-600 mt-4">Loading available vehicles...</p>
            </div>
            
            <!-- Vehicles Grid - Will be populated by JavaScript -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8" id="vehicles-grid">
                <!-- Vehicle cards will be inserted here by JavaScript -->
            </div>
            
            <div class="text-center mt-8">
                <a href="#vehicles" class="btn btn-secondary">View All Vehicles</a>
            </div>
        </div>
    </section>

    <section class="services bg-gray-50 py-16" id="services">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Why Choose GenSan Car Rentals?</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="service-item bg-white p-6 rounded-xl shadow-md text-center">
                    <i class="fas fa-shield-alt text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Fully Insured</h3>
                    <p class="text-gray-600">All our vehicles come with comprehensive insurance coverage for your safety and peace of mind</p>
                </div>
                <div class="service-item bg-white p-6 rounded-xl shadow-md text-center">
                    <i class="fas fa-clock text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">24/7 Support</h3>
                    <p class="text-gray-600">Round-the-clock customer support and roadside assistance whenever you need help</p>
                </div>
                <div class="service-item bg-white p-6 rounded-xl shadow-md text-center">
                    <i class="fas fa-map-marker-alt text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Multiple Locations</h3>
                    <p class="text-gray-600">Convenient pickup and drop-off points across General Santos City and nearby areas</p>
                </div>
                <div class="service-item bg-white p-6 rounded-xl shadow-md text-center">
                    <i class="fas fa-tools text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Well Maintained</h3>
                    <p class="text-gray-600">Regular maintenance and thorough inspections ensure reliable and safe vehicles</p>
                </div>
                <div class="service-item bg-white p-6 rounded-xl shadow-md text-center">
                    <i class="fas fa-dollar-sign text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Best Rates</h3>
                    <p class="text-gray-600">Competitive pricing with no hidden fees. Get the best value for your money</p>
                </div>
                <div class="service-item bg-white p-6 rounded-xl shadow-md text-center">
                    <i class="fas fa-user-tie text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">Professional Service</h3>
                    <p class="text-gray-600">Experienced staff ready to assist you with all your car rental needs</p>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials py-16" id="testimonials">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">What Our Customers Say</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <p class="text-gray-700 mb-4 italic">"Excellent service! The Toyota Vios was clean and well-maintained. Perfect for our GenSan business trip. Highly recommended!"</p>
                    <div class="customer">
                        <strong class="block text-lg font-semibold text-gray-900">Maria Santos</strong>
                        <span class="text-gray-500">Business Traveler</span>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <p class="text-gray-700 mb-4 italic">"Great rates and very friendly staff. The Innova made our family vacation around South Cotabato so much easier and comfortable!"</p>
                    <div class="customer">
                        <strong class="block text-lg font-semibold text-gray-900">Juan Dela Cruz</strong>
                        <span class="text-gray-500">Tourist</span>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-xl shadow-md text-center">
                    <p class="text-gray-700 mb-4 italic">"Professional service from start to finish. The booking process was smooth and the car was delivered on time. Will definitely rent again!"</p>
                    <div class="customer">
                        <strong class="block text-lg font-semibold text-gray-900">Anna Reyes</strong>
                        <span class="text-gray-500">Local Customer</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php include 'includes/footer.php'; ?>
        
    <div id="booking-modal" class="modal">
    <div class="modal-content w-full max-w-4xl">
        <span class="close" onclick="closeBookingModal()">&times;</span>
        <h2 class="text-3xl font-bold mb-6 text-gray-800">Book Your Vehicle</h2>
        
        <form class="booking-modal-form" id="booking-modal-form" method="post" action="save_booking.php" enctype="multipart/form-data">
            <input type="hidden" id="selected-vehicle" name="selected_vehicle" value="">
            
            <!-- Personal Information Section -->
            <div class="form-section">
                <h3><i class="fas fa-user mr-2"></i>Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="customer-name" class="block text-gray-700 font-medium mb-1">Full Name *</label>
                        <input type="text" id="customer-name" name="customer_name" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="customer-phone" class="block text-gray-700 font-medium mb-1">Phone Number *</label>
                        <input type="tel" id="customer-phone" name="customer_phone" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="customer-email" class="block text-gray-700 font-medium mb-1">Email Address *</label>
                        <input type="email" id="customer-email" name="customer_email" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="license-number" class="block text-gray-700 font-medium mb-1">Driver's License No. *</label>
                        <input type="text" id="license-number" name="license_number" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                </div>
            </div>

            <!-- Rental Details Section -->
            <div class="form-section">
                <h3><i class="fas fa-calendar-alt mr-2"></i>Rental Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="start-date" class="block text-gray-700 font-medium mb-1">Pickup Date *</label>
                        <input type="date" id="start-date" name="start_date" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="end-date" class="block text-gray-700 font-medium mb-1">Return Date *</label>
                        <input type="date" id="end-date" name="end_date" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="start-time" class="block text-gray-700 font-medium mb-1">Pickup Time *</label>
                        <input type="time" id="start-time" name="start_time" value="09:00" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                    <div>
                        <label for="end-time" class="block text-gray-700 font-medium mb-1">Return Time *</label>
                        <input type="time" id="end-time" name="end_time" value="09:00" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="pickup-location-modal" class="block text-gray-700 font-medium mb-1">Pickup Location *</label>
                        <select id="pickup-location-modal" name="pickup_location" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <option value="">Select Location</option>
                            <option value="gensan-airport">GenSan Airport</option>
                            <option value="downtown-gensan">Downtown GenSan</option>
                            <option value="kcc-mall">KCC Mall</option>
                            <option value="robinsons-place">Robinson's Place GenSan</option>
                            <option value="sm-city-gensan">SM City General Santos</option>
                        </select>
                    </div>
                    <div>
                        <label for="return-location-modal" class="block text-gray-700 font-medium mb-1">Return Location</label>
                        <select id="return-location-modal" name="return_location" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Same as pickup</option>
                            <option value="gensan-airport">GenSan Airport</option>
                            <option value="downtown-gensan">Downtown GenSan</option>
                            <option value="kcc-mall">KCC Mall</option>
                            <option value="robinsons-place">Robinson's Place GenSan</option>
                            <option value="sm-city-gensan">SM City General Santos</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Additional Information Section -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle mr-2"></i>Additional Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="passengers" class="block text-gray-700 font-medium mb-1">Number of Passengers</label>
                        <input type="number" id="passengers" name="passengers" min="1" max="8" value="1" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="rental-duration-modal" class="block text-gray-700 font-medium mb-1">Rental Duration (Optional)</label>
                        <select id="rental-duration-modal" name="rental_duration" class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Auto Calculate</option>
                            <option value="8">8 Hours</option>
                            <option value="12">12 Hours</option>
                            <option value="24">24 Hours</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="purpose" class="block text-gray-700 font-medium mb-1">Purpose of Rental</label>
                    <textarea id="purpose" name="purpose" rows="3" placeholder="Business trip, vacation, etc." class="w-full mt-1 p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                </div>
            </div>

            <!-- Document Upload Section -->
            <div class="form-section">
                <h3><i class="fas fa-upload mr-2"></i>Upload License/ID Copy</h3>
                <div class="image-upload-container" id="imageUploadContainer">
                    <input type="file" id="upload-image" name="upload-image" accept="image/*" class="image-upload-input">
                    <div class="image-upload-content">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600 text-lg font-medium mb-2">Upload Driver's License or Valid ID</p>
                        <p class="text-gray-500 text-sm mb-4">Drag and drop your image here, or click to browse</p>
                        <div class="text-xs text-gray-400">
                            <span class="bg-gray-100 px-2 py-1 rounded">JPG, PNG, PDF up to 10MB</span>
                        </div>
                    </div>
                </div>
                
                <div class="image-preview" id="imagePreview" style="display: none;">
                    <img id="previewImage" src="" alt="Preview">
                    <div class="image-preview-actions">
                        <button type="button" class="btn-change" onclick="changeImage()">
                            <i class="fas fa-edit mr-1"></i> Change
                        </button>
                        <button type="button" class="btn-remove" onclick="removeImage()">
                            <i class="fas fa-trash mr-1"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Cost Summary -->
            <div id="cost-summary" class="form-section hidden">
                <h3><i class="fas fa-calculator mr-2"></i>Rental Summary</h3>
                <div class="bg-white p-4 rounded-lg border">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="font-medium">Selected Vehicle:</div>
                        <div id="summary-vehicle" class="text-blue-600 font-semibold">-</div>
                        
                        <div class="font-medium">Rental Duration:</div>
                        <div id="summary-duration" class="text-blue-600 font-semibold">-</div>
                        
                        <div class="font-medium">Rate:</div>
                        <div id="summary-rate" class="text-blue-600 font-semibold">-</div>
                        
                        <div class="border-t pt-2 font-bold text-lg">Total Cost:</div>
                        <div id="summary-total" class="border-t pt-2 font-bold text-xl text-green-600">₱/div>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-4 mt-6">
                <button type="submit" class="btn btn-primary flex-1 py-3 text-lg">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Booking Request
                </button>
                <button type="button" class="btn btn-secondary flex-1 py-3 text-lg" onclick="saveBookingModal()">
                    <i class="fas fa-save mr-2"></i>Save for Later
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Message Modal -->
    <div id="message-modal" class="modal">
        <div class="modal-content w-full max-w-sm">
            <span class="close" onclick="closeMessageModal()">&times;</span>
            <div id="message-content" class="p-4 text-center"></div>
        </div>
    </div>

 <script src="js/index.js"></script>
    <script>
        // Initialize Firebase
        window.onload = function() {
            // Ensure Firebase is initialized
            if (typeof initializeFirebase === 'function') {
                initializeFirebase(); 
                 
            }
        };
    </script>
</body>
    
</html> 