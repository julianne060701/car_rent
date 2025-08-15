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

                <div class="form-group">
                    <label for="rental-duration" class="block text-gray-700">Rental Duration</label>
                    <select id="rental-duration" name="rental_duration">
                        <option value="">Select Duration</option>
                        <option value="8">8 Hours</option>
                        <option value="12">12 Hours</option>
                        <option value="24">24 Hours</option>
                    </select>
                </div>

                <div class="flex items-end justify-center col-span-full md:col-span-1">
                    <button type="submit" class="btn btn-search flex-1 w-full md:w-auto">
                        <i class="fas fa-search mr-2"></i> Find Cars
                    </button>
                </div>
                 <div class="flex items-end justify-center col-span-full md:col-span-1">
                    <button type="button" class="btn btn-primary flex-1 w-full md:w-auto" onclick="saveBooking()">
                        <i class="fas fa-save mr-2"></i> Save Progress
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="featured-vehicles py-16" id="vehicles">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Our Popular Vehicles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="vehicle-card bg-white rounded-xl shadow-lg overflow-hidden" data-vehicle-id="1">
                    <img src="https://placehold.co/400x250/3b82f6/ffffff?text=Toyota+Vios" alt="Toyota Vios" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Toyota Vios</h3>
                        <p class="text-gray-600 mb-4">Perfect for city driving and business trips</p>
                        <div class="features grid grid-cols-3 gap-2 text-sm text-gray-500 mb-4">
                            <span class="flex items-center"><i class="fas fa-users mr-2 text-blue-500"></i> 4 Passengers</span>
                            <span class="flex items-center"><i class="fas fa-cog mr-2 text-blue-500"></i> Automatic</span>
                            <span class="flex items-center"><i class="fas fa-gas-pump mr-2 text-blue-500"></i> Fuel Efficient</span>
                        </div>
                        <div class="price text-2xl font-bold text-gray-900 mb-4">₱1,200/day</div>
                        <button class="btn btn-primary w-full" data-vehicle="Toyota Vios" onclick="openBookingModalWithVehicle('Toyota Vios')">Book Now</button>
                    </div>
                </div>
                <div class="vehicle-card bg-white rounded-xl shadow-lg overflow-hidden" data-vehicle-id="2">
                    <img src="https://placehold.co/400x250/f59e0b/ffffff?text=Toyota+Innova" alt="Toyota Innova" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Toyota Innova</h3>
                        <p class="text-gray-600 mb-4">Spacious family vehicle for group travels</p>
                        <div class="features grid grid-cols-3 gap-2 text-sm text-gray-500 mb-4">
                            <span class="flex items-center"><i class="fas fa-users mr-2 text-yellow-500"></i> 7 Passengers</span>
                            <span class="flex items-center"><i class="fas fa-cog mr-2 text-yellow-500"></i> Manual</span>
                            <span class="flex items-center"><i class="fas fa-suitcase mr-2 text-yellow-500"></i> Large Cargo</span>
                        </div>
                        <div class="price text-2xl font-bold text-gray-900 mb-4">₱2,000/day</div>
                        <button class="btn btn-primary w-full" data-vehicle="Toyota Innova" onclick="openBookingModalWithVehicle('Toyota Innova')">Book Now</button>
                    </div>
                </div>
                <div class="vehicle-card bg-white rounded-xl shadow-lg overflow-hidden" data-vehicle-id="3">
                    <img src="https://placehold.co/400x250/3b82f6/ffffff?text=Honda+City" alt="Honda City" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3>Honda City</h3>
                        <p>Reliable and fuel efficient sedan</p>
                        <div class="features grid grid-cols-3 gap-2 text-sm text-gray-500 mb-4">
                            <span class="flex items-center"><i class="fas fa-users mr-2 text-blue-500"></i> 4 Passengers</span>
                            <span class="flex items-center"><i class="fas fa-cog mr-2 text-blue-500"></i> CVT</span>
                            <span class="flex items-center"><i class="fas fa-leaf mr-2 text-blue-500"></i> Eco-Friendly</span>
                        </div>
                        <div class="price text-2xl font-bold text-gray-900 mb-4">₱1,300/day</div>
                        <button class="btn btn-primary w-full" data-vehicle="Honda City" onclick="openBookingModalWithVehicle('Honda City')">Book Now</button>
                    </div>
                </div>
                <div class="vehicle-card bg-white rounded-xl shadow-lg overflow-hidden" data-vehicle-id="4">
                    <img src="https://placehold.co/400x250/f59e0b/ffffff?text=Mitsubishi+Xpander" alt="Mitsubishi Xpander" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3>Mitsubishi Xpander</h3>
                        <p>Modern MPV with stylish design</p>
                        <div class="features grid grid-cols-3 gap-2 text-sm text-gray-500 mb-4">
                            <span class="flex items-center"><i class="fas fa-users mr-2 text-yellow-500"></i> 7 Passengers</span>
                            <span class="flex items-center"><i class="fas fa-cog mr-2 text-yellow-500"></i> Automatic</span>
                            <span class="flex items-center"><i class="fas fa-shield-alt mr-2 text-yellow-500"></i> Safety Features</span>
                        </div>
                        <div class="price text-2xl font-bold text-gray-900 mb-4">₱1,800/day</div>
                        <button class="btn btn-primary w-full" data-vehicle="Mitsubishi Xpander" onclick="openBookingModalWithVehicle('Mitsubishi Xpander')">Book Now</button>
                    </div>
                </div>
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
            <div class="testimonial-slider relative w-full overflow-hidden">
                <div id="testimonial-container" class="flex transition-transform duration-500">
                    <div class="testimonial flex-none w-full md:w-1/2 lg:w-1/3 p-4">
                        <div class="bg-white p-8 rounded-xl shadow-md text-center">
                            <p class="text-gray-700 mb-4 italic">"Excellent service! The Toyota Vios was clean and well-maintained. Perfect for our GenSan business trip. Highly recommended!"</p>
                            <div class="customer">
                                <strong class="block text-lg font-semibold text-gray-900">Maria Santos</strong>
                                <span class="text-gray-500">Business Traveler</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial flex-none w-full md:w-1/2 lg:w-1/3 p-4">
                        <div class="bg-white p-8 rounded-xl shadow-md text-center">
                            <p class="text-gray-700 mb-4 italic">"Great rates and very friendly staff. The Innova made our family vacation around South Cotabato so much easier and comfortable!"</p>
                            <div class="customer">
                                <strong class="block text-lg font-semibold text-gray-900">Juan Dela Cruz</strong>
                                <span class="text-gray-500">Tourist</span>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial flex-none w-full md:w-1/2 lg:w-1/3 p-4">
                        <div class="bg-white p-8 rounded-xl shadow-md text-center">
                            <p class="text-gray-700 mb-4 italic">"Professional service from start to finish. The booking process was smooth and the car was delivered on time. Will definitely rent again!"</p>
                            <div class="customer">
                                <strong class="block text-lg font-semibold text-gray-900">Anna Reyes</strong>
                                <span class="text-gray-500">Local Customer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
        
    <div id="booking-modal" class="modal hidden">
        <div class="modal-content w-full max-w-2xl">
            <span class="close" onclick="closeBookingModal()">&times;</span>
            <h2 class="text-2xl font-bold mb-4">Book Your Vehicle</h2>
            <form class="booking-modal-form" method="POST" action="#">
                <input type="hidden" id="selected-vehicle" name="selected_vehicle" value="">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="customer-name" class="block text-gray-700">Full Name *</label>
                        <input type="text" id="customer-name" name="customer_name" class="w-full mt-1 p-2 border rounded-md" required>
                    </div>
                    <div>
                        <label for="customer-phone" class="block text-gray-700">Phone Number *</label>
                        <input type="tel" id="customer-phone" name="customer_phone" class="w-full mt-1 p-2 border rounded-md" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="customer-email" class="block text-gray-700">Email Address *</label>
                        <input type="email" id="customer-email" name="customer_email" class="w-full mt-1 p-2 border rounded-md" required>
                    </div>
                    <div>
                        <label for="license-number" class="block text-gray-700">Driver's License No. *</label>
                        <input type="text" id="license-number" name="license_number" class="w-full mt-1 p-2 border rounded-md" required>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="modal-pickup-date" class="block text-gray-700">Pickup Date *</label>
                        <input type="date" id="modal-pickup-date" name="modal_pickup_date" class="w-full mt-1 p-2 border rounded-md" required>
                    </div>
                    <div>
                        <label for="modal-return-date" class="block text-gray-700">Return Date *</label>
                        <input type="date" id="modal-return-date" name="modal_return_date" class="w-full mt-1 p-2 border rounded-md" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="pickup-location-modal" class="block text-gray-700">Pickup Location *</label>
                    <select id="pickup-location-modal" name="pickup_location_modal" class="w-full mt-1 p-2 border rounded-md" required>
                        <option value="">Select Location</option>
                        <option value="gensan-airport">GenSan Airport</option>
                        <option value="downtown-gensan">Downtown GenSan</option>
                        <option value="kcc-mall">KCC Mall</option>
                        <option value="robinsons-place">Robinson's Place GenSan</option>
                        <option value="sm-city-gensan">SM City General Santos</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="special-requests" class="block text-gray-700">Special Requests</label>
                    <textarea id="special-requests" name="special_requests" rows="3" placeholder="Any special requirements or requests..." class="w-full mt-1 p-2 border rounded-md"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-full">Submit Booking Request</button>
            </form>
        </div>
    </div>

    <div id="message-modal" class="modal hidden">
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