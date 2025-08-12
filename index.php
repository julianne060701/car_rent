<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenSan Car Rental - General Santos City</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner-large"></div>
            <p>Processing your request...</p>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification" class="notification"></div>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="logo-text">
                        <h1>GenSan Car Rental</h1>
                        <p>Your trusted partner in General Santos</p>
                    </div>
                </div>
                <div class="contact-info">
                    <div class="contact-item" onclick="copyToClipboard('(083) 552-1234')">
                        <i class="fas fa-phone"></i>
                        <span>(083) 552-1234</span>
                    </div>
                    <div class="contact-item" onclick="copyToClipboard('info@gensancarrental.com')">
                        <i class="fas fa-envelope"></i>
                        <span>info@gensancarrental.com</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Success Message -->
    <div class="success-message" id="success-message">
        <strong>Booking Successful!</strong> Thank you for choosing GenSan Car Rental. We will contact you shortly to confirm your reservation.
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Explore General Santos & Beyond</h2>
            <p>Quality car rentals for your adventures in the Tuna Capital of the Philippines</p>
            
            <!-- Booking Form -->
            <div class="booking-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Pickup Date</label>
                        <i class="fas fa-calendar form-icon"></i>
                        <input type="date" id="pickup-date" required>
                        <div class="error-message">Please select a pickup date</div>
                    </div>
                    <div class="form-group">
                        <label>Return Date</label>
                        <i class="fas fa-calendar form-icon"></i>
                        <input type="date" id="return-date" required>
                        <div class="error-message">Please select a return date</div>
                    </div>
                    <div class="form-group">
                        <label>Pickup Time</label>
                        <i class="fas fa-clock form-icon"></i>
                        <select id="pickup-time">
                            <option value="08:00">08:00</option>
                            <option value="09:00" selected>09:00</option>
                            <option value="10:00">10:00</option>
                            <option value="11:00">11:00</option>
                            <option value="12:00">12:00</option>
                            <option value="13:00">13:00</option>
                            <option value="14:00">14:00</option>
                            <option value="15:00">15:00</option>
                            <option value="16:00">16:00</option>
                            <option value="17:00">17:00</option>
                            <option value="18:00">18:00</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Pickup Location</label>
                        <i class="fas fa-map-marker-alt form-icon"></i>
                        <select id="pickup-location">
                            <option value="Main Office - General Santos">Main Office - General Santos</option>
                            <option value="SM City General Santos">SM City General Santos</option>
                            <option value="Robinsons Place GenSan">Robinsons Place GenSan</option>
                            <option value="General Santos Airport">General Santos Airport</option>
                            <option value="KCC Mall of GenSan">KCC Mall of GenSan</option>
                            <option value="Fitmart General Santos">Fitmart General Santos</option>
                        </select>
                    </div>
                </div>
                <button class="search-btn" onclick="searchCars()">
                    <i class="fas fa-search"></i> Search Available Cars
                </button>
            </div>
        </div>
    </section>

    <!-- Cars Section -->
    <section class="cars-section">
        <div class="container">
            <h3 class="section-title">Choose Your Perfect Ride</h3>
            
            <div class="cars-grid" id="cars-grid">
                <!-- Cars will be populated by JavaScript -->
            </div>
        </div>
    </section>

    <!-- Popular Destinations -->
    <section class="destinations-section">
        <div class="container">
            <h3 class="section-title">Popular Destinations from GenSan</h3>
            
            <div class="destinations-grid" id="destinations-grid">
                <!-- Destinations will be populated by JavaScript -->
            </div>
        </div>
    </section>

    <!-- Booking Bar -->
    <div class="booking-bar" id="booking-bar">
        <div class="booking-bar-content">
            <div class="selected-car-info">
                <div class="selected-car-icon">
                    <i class="fas fa-car"></i>
                </div>
                <div>
                    <div id="selected-car-name" style="font-weight: bold; color: #2c3e50;"></div>
                    <div id="selected-pickup-location" style="color: #7f8c8d; font-size: 14px;"></div>
                </div>
            </div>
            
            <div class="booking-actions">
                <div style="text-align: right;">
                    <div style="color: #7f8c8d; font-size: 14px;">Total Price</div>
                    <div style="font-size: 24px; font-weight: bold; color: #2196F3;">
                        <span id="selected-car-price"></span>
                    </div>
                </div>
                <button class="book-now-btn" onclick="openBookingModal()">Book Now</button>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    <div id="booking-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeBookingModal()">&times;</span>
            <h3>Complete Your Booking</h3>
            
            <form class="booking-form-modal" onsubmit="submitBooking(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="customer_name" required>
                        <div class="error-message">Please enter your full name</div>
                    </div>
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="customer_phone" placeholder="09123456789" required>
                        <div class="error-message">Please enter a valid phone number</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="customer_email" required>
                    <div class="error-message">Please enter a valid email address</div>
                </div>
                
                <div class="form-group">
                    <label>Return Time</label>
                    <select name="return_time">
                        <option value="08:00">08:00</option>
                        <option value="09:00" selected>09:00</option>
                        <option value="10:00">10:00</option>
                        <option value="11:00">11:00</option>
                        <option value="12:00">12:00</option>
                        <option value="13:00">13:00</option>
                        <option value="14:00">14:00</option>
                        <option value="15:00">15:00</option>
                        <option value="16:00">16:00</option>
                        <option value="17:00">17:00</option>
                        <option value="18:00">18:00</option>
                    </select>
                </div>
                
                <div id="booking-summary" class="price-breakdown">
                    <h4 style="margin-bottom: 15px; color: #2c3e50;">Booking Summary</h4>
                    <div id="summary-details"></div>
                </div>
                
                <button type="submit" class="submit-btn" id="submit-btn">
                    Confirm Booking
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    
    <?php include('includes/footer.php'); ?>

    <script src="js/index.js"></script>
</body>
</html>