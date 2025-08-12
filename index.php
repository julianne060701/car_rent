
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenSan Car Rentals - Your Trusted Car Rental in General Santos City</title>
    <meta name="description" content="Premium car rentals in General Santos City. Reliable, affordable, and well-maintained vehicles for business and leisure.">
    <meta name="keywords" content="car rental, General Santos City, GenSan, Philippines, vehicle rental">
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/index.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Include Header -->
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Explore General Santos City in Comfort</h1>
            <p>Premium car rentals for business, leisure, and everything in between</p>
            <div class="hero-buttons">
                <button class="btn btn-primary" onclick="openBookingModal()">Book Now</button>
                <a href="#vehicles" class="btn btn-secondary">View Fleet</a>
            </div>
        </div>
    </section>

   <!-- Quick Booking Bar -->
<section class="quick-booking">
    <div class="container">
        <form class="booking-form" method="POST" action="process_booking.php">
            <!-- Pickup Location -->
            <div class="form-group">
                <label for="pickup-location">Pickup Location</label>
                <select id="pickup-location" name="pickup_location" required>
                    <option value="">Select Location</option>
                    <option value="gensan-airport">GenSan Airport</option>
                    <option value="downtown-gensan">Downtown GenSan</option>
                    <option value="kcc-mall">KCC Mall</option>
                    <option value="robinsons-place">Robinson's Place GenSan</option>
                    <option value="sm-city-gensan">SM City General Santos</option>
                </select>
            </div>

            <!-- Pickup Date -->
            <div class="form-group">
                <label for="pickup-date">Pickup Date</label>
                <input type="date" id="pickup-date" name="pickup_date" required>
            </div>

            <!-- Pickup Time -->
            <div class="form-group">
                <label for="pickup-time">Pickup Time</label>
                <input type="time" id="pickup-time" name="pickup_time" value="09:00" required>
            </div>

            <!-- Return Date -->
            <div class="form-group">
                <label for="return-date">Return Date</label>
                <input type="date" id="return-date" name="return_date" required>
            </div>

            <!-- Return Time -->
            <div class="form-group">
                <label for="return-time">Return Time</label>
                <input type="time" id="return-time" name="return_time" value="09:00" required>
            </div>

            <!-- Rental Duration -->
            <div class="form-group">
                <label for="rental-duration">Rental Duration</label>
                <select id="rental-duration" name="rental_duration" required>
                    <option value="">Select Duration</option>
                    <option value="8">8 Hours</option>
                    <option value="12">12 Hours</option>
                    <option value="24">24 Hours</option>
                </select>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-search">
                <i class="fas fa-search"></i> Find Cars
            </button>
        </form>
    </div>
</section>


    <!-- Featured Vehicles -->
<section class="featured-vehicles" id="vehicles">
    <div class="container">
        <h2>Our Popular Vehicles</h2>
        <div class="vehicle-grid">
            <div class="vehicle-card" data-vehicle-id="1">
                <img src="img/cars/toyota-vios.jpg" alt="Toyota Vios">
                <div class="vehicle-info">
                    <h3>Toyota Vios</h3>
                    <p>Perfect for city driving and business trips</p>
                    <div class="features">
                        <span><i class="fas fa-users"></i> 4 Passengers</span>
                        <span><i class="fas fa-cog"></i> Automatic</span>
                        <span><i class="fas fa-gas-pump"></i> Fuel Efficient</span>
                    </div>
                    <div class="price">₱1,200/day</div>
                    <button class="btn btn-book" data-vehicle="Toyota Vios">Book Now</button>
                </div>
            </div>
            <div class="vehicle-card" data-vehicle-id="2">
                <img src="img/cars/toyota-innova.jpg" alt="Toyota Innova">
                <div class="vehicle-info">
                    <h3>Toyota Innova</h3>
                    <p>Spacious family vehicle for group travels</p>
                    <div class="features">
                        <span><i class="fas fa-users"></i> 7 Passengers</span>
                        <span><i class="fas fa-cog"></i> Manual</span>
                        <span><i class="fas fa-suitcase"></i> Large Cargo</span>
                    </div>
                    <div class="price">₱2,000/day</div>
                    <button class="btn btn-book" data-vehicle="Toyota Innova">Book Now</button>
                </div>
            </div>
            <div class="vehicle-card" data-vehicle-id="3">
                <img src="img/cars/honda-city.jpg" alt="Honda City">
                <div class="vehicle-info">
                    <h3>Honda City</h3>
                    <p>Reliable and fuel efficient sedan</p>
                    <div class="features">
                        <span><i class="fas fa-users"></i> 4 Passengers</span>
                        <span><i class="fas fa-cog"></i> CVT</span>
                        <span><i class="fas fa-leaf"></i> Eco-Friendly</span>
                    </div>
                    <div class="price">₱1,300/day</div>
                    <button class="btn btn-book" data-vehicle="Honda City">Book Now</button>
                </div>
            </div>
            <div class="vehicle-card" data-vehicle-id="4">
                <img src="img/cars/mitsubishi-xpander.jpg" alt="Mitsubishi Xpander">
                <div class="vehicle-info">
                    <h3>Mitsubishi Xpander</h3>
                    <p>Modern MPV with stylish design</p>
                    <div class="features">
                        <span><i class="fas fa-users"></i> 7 Passengers</span>
                        <span><i class="fas fa-cog"></i> Automatic</span>
                        <span><i class="fas fa-shield-alt"></i> Safety Features</span>
                    </div>
                    <div class="price">₱1,800/day</div>
                    <button class="btn btn-book" data-vehicle="Mitsubishi Xpander">Book Now</button>
                </div>
            </div>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="vehicles.php" class="btn btn-secondary">View All Vehicles</a>
        </div>
    </div>
</section>


    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <h2>Why Choose GenSan Car Rentals?</h2>
            <div class="services-grid">
                <div class="service-item">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Fully Insured</h3>
                    <p>All our vehicles come with comprehensive insurance coverage for your safety and peace of mind</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-clock"></i>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock customer support and roadside assistance whenever you need help</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Multiple Locations</h3>
                    <p>Convenient pickup and drop-off points across General Santos City and nearby areas</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-tools"></i>
                    <h3>Well Maintained</h3>
                    <p>Regular maintenance and thorough inspections ensure reliable and safe vehicles</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-dollar-sign"></i>
                    <h3>Best Rates</h3>
                    <p>Competitive pricing with no hidden fees. Get the best value for your money</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-user-tie"></i>
                    <h3>Professional Service</h3>
                    <p>Experienced staff ready to assist you with all your car rental needs</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2>What Our Customers Say</h2>
            <div class="testimonial-slider">
                <div class="testimonial active">
                    <p>"Excellent service! The Toyota Vios was clean and well-maintained. Perfect for our GenSan business trip. Highly recommended!"</p>
                    <div class="customer">
                        <strong>Maria Santos</strong>
                        <span>Business Traveler</span>
                    </div>
                </div>
                <div class="testimonial">
                    <p>"Great rates and very friendly staff. The Innova made our family vacation around South Cotabato so much easier and comfortable!"</p>
                    <div class="customer">
                        <strong>Juan Dela Cruz</strong>
                        <span>Tourist</span>
                    </div>
                </div>
                <div class="testimonial">
                    <p>"Professional service from start to finish. The booking process was smooth and the car was delivered on time. Will definitely rent again!"</p>
                    <div class="customer">
                        <strong>Anna Reyes</strong>
                        <span>Local Customer</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Booking Modal -->
    <div id="booking-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeBookingModal()">&times;</span>
            <h2>Book Your Vehicle</h2>
            <form class="booking-modal-form" method="POST" action="process_booking.php">
                <input type="hidden" id="selected-vehicle" name="selected_vehicle" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="customer-name">Full Name *</label>
                        <input type="text" id="customer-name" name="customer_name" required>
                    </div>
                    <div class="form-group">
                        <label for="customer-phone">Phone Number *</label>
                        <input type="tel" id="customer-phone" name="customer_phone" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="customer-email">Email Address *</label>
                        <input type="email" id="customer-email" name="customer_email" required>
                    </div>
                    <div class="form-group">
                        <label for="license-number">Driver's License No. *</label>
                        <input type="text" id="license-number" name="license_number" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="modal-pickup-date">Pickup Date *</label>
                        <input type="date" id="modal-pickup-date" name="modal_pickup_date" required>
                    </div>
                    <div class="form-group">
                        <label for="modal-return-date">Return Date *</label>
                        <input type="date" id="modal-return-date" name="modal_return_date" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pickup-location-modal">Pickup Location *</label>
                    <select id="pickup-location-modal" name="pickup_location_modal" required>
                        <option value="">Select Location</option>
                        <option value="gensan-airport">GenSan Airport</option>
                        <option value="downtown-gensan">Downtown GenSan</option>
                        <option value="kcc-mall">KCC Mall</option>
                        <option value="robinsons-place">Robinson's Place GenSan</option>
                        <option value="sm-city-gensan">SM City General Santos</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="special-requests">Special Requests</label>
                    <textarea id="special-requests" name="special_requests" rows="3" placeholder="Any special requirements or requests..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Booking Request</button>
            </form>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="js/index.js"></script>
</body>
</html>