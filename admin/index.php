<!DOCTYPE html>
<html lang="en">

<head>
    <?php include('includes/header.php'); ?>
    <style>
        .calendar-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #e3e6f0;
        }
        
        .calendar-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .calendar-nav button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .calendar-nav button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .calendar-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
            background: #e3e6f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .calendar-day-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 18px 12px;
            text-align: center;
            font-weight: bold;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .calendar-day {
            background: #fff;
            padding: 15px 10px;
            min-height: 120px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            border: 2px solid transparent;
        }
        
        .calendar-day:hover {
            background: #f8f9fc;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }
        
        .calendar-day.other-month {
            color: #bbb;
            background: #f8f9fc;
            opacity: 0.6;
        }
        
        .calendar-day.today {
            background: linear-gradient(145deg, #e8f4fd, #d4edda);
            border: 3px solid #667eea;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        .calendar-day.high-demand {
            background: linear-gradient(145deg, #fff5f5, #fed7d7);
            border-left: 5px solid #e53e3e;
        }
        
        .calendar-day.available {
            background: linear-gradient(145deg, #f0fff4, #c6f6d5);
            border-left: 5px solid #38a169;
        }
        
        .calendar-day.partially-booked {
            background: linear-gradient(145deg, #fffaf0, #feebc8);
            border-left: 5px solid #d69e2e;
        }
        
        .day-number {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: #2d3748;
        }
        
        .availability-indicator {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 15px;
            margin: 3px 0;
            font-weight: 600;
            text-align: center;
        }
        
        .available-cars {
            background: #48bb78;
            color: white;
        }
        
        .limited-cars {
            background: #ed8936;
            color: white;
        }
        
        .no-cars {
            background: #e53e3e;
            color: white;
        }
        
        .car-count {
            font-size: 0.7rem;
            color: #718096;
            margin-top: 5px;
        }
        
        .rental-sidebar {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .car-fleet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }
        
        .car-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: #667eea;
        }
        
        .car-card.selected {
            border-color: #667eea;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .car-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }
        
        .car-details {
            padding: 20px;
        }
        
        .car-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .car-type {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 12px;
        }
        
        .car-features {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #4a5568;
        }
        
        .feature {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .car-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .availability-status {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .status-available {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-limited {
            background: #feebc8;
            color: #744210;
        }
        
        .status-unavailable {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-book {
            background: linear-gradient(45deg, #48bb78, #38a169);
            border: none;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
        }
        
        .btn-select {
            background: #667eea;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-select:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: rotate(45deg);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }
        
        .stats-label {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .rental-list {
            margin-top: 25px;
        }
        
        .rental-item {
            background: #f7fafc;
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 5px solid #667eea;
            transition: all 0.3s;
        }
        
        .rental-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .rental-car {
            font-weight: bold;
            color: #667eea;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }
        
        .rental-customer {
            color: #4a5568;
            font-size: 0.95rem;
            margin-bottom: 8px;
        }
        
        .rental-duration {
            color: #718096;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        @media (max-width: 768px) {
            .calendar-grid {
                font-size: 0.8rem;
            }
            
            .calendar-day {
                min-height: 100px;
                padding: 10px 6px;
            }
            
            .car-fleet-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>
        <?php include('includes/topbar.php'); ?>

        <!-- Begin Page Content -->
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Car Rental Booking System</h1>
                <button class="btn btn-primary" onclick="openRentalModal()">
                    <i class="fas fa-car"></i> New Rental
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-number">42</div>
                    <div class="stats-label">Total Fleet</div>
                </div>
                <div class="stats-card">
                    <div class="stats-number">28</div>
                    <div class="stats-label">Cars Rented</div>
                </div>
                <div class="stats-card">
                    <div class="stats-number">14</div>
                    <div class="stats-label">Available Now</div>
                </div>
                <div class="stats-card">
                    <div class="stats-number">$8,450</div>
                    <div class="stats-label">Monthly Revenue</div>
                </div>
            </div>

            <div class="row">
                <!-- Calendar Section -->
                <div class="col-lg-8">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <div class="calendar-nav">
                                <button onclick="changeMonth(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span class="calendar-title" id="calendarTitle">December 2024</span>
                                <button onclick="changeMonth(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                            <div class="calendar-nav">
                                <button onclick="goToToday()">
                                    <i class="fas fa-calendar-day"></i> Today
                                </button>
                            </div>
                        </div>
                        
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Calendar will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Sidebar Section -->
                <div class="col-lg-4">
                    <!-- Selected Date Info -->
                    <div class="rental-sidebar">
                        <h5 class="mb-3"><i class="fas fa-calendar-alt"></i> Selected Date</h5>
                        <p id="selectedDate" class="text-muted">Select a date to view availability</p>
                        
                        <!-- Today's Rentals -->
                        <div class="rental-list" id="rentalList">
                            <h6 class="mb-3"><i class="fas fa-car-side"></i> Today's Rentals</h6>
                            <div class="rental-item">
                                <div class="rental-car">BMW 3 Series</div>
                                <div class="rental-customer">John Smith</div>
                                <div class="rental-duration">
                                    <i class="fas fa-clock"></i> 3 days rental
                                </div>
                            </div>
                            <div class="rental-item">
                                <div class="rental-car">Toyota Camry</div>
                                <div class="rental-customer">Sarah Johnson</div>
                                <div class="rental-duration">
                                    <i class="fas fa-clock"></i> 1 week rental
                                </div>
                            </div>
                            <div class="rental-item">
                                <div class="rental-car">Ford Mustang</div>
                                <div class="rental-customer">Mike Davis</div>
                                <div class="rental-duration">
                                    <i class="fas fa-clock"></i> Weekend rental
                                </div>
                            </div>
                        </div>

                        <!-- Quick Booking Form -->
                        <div class="booking-form">
                            <h6 class="mb-3"><i class="fas fa-plus-circle"></i> Quick Rental</h6>
                            <div class="form-group">
                                <input type="text" class="form-control" placeholder="Customer Name" id="customerName">
                            </div>
                            <div class="form-group">
                                <input type="date" class="form-control" id="rentalDate">
                            </div>
                            <div class="form-group">
                                <select class="form-control" id="rentalDuration">
                                    <option>Select Duration</option>
                                    <option>1 Day</option>
                                    <option>3 Days</option>
                                    <option>1 Week</option>
                                    <option>2 Weeks</option>
                                    <option>1 Month</option>
                                </select>
                            </div>
                            <button class="btn-book" onclick="quickRental()">
                                <i class="fas fa-car"></i> Book Rental
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Car Fleet Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="calendar-container">
                        <h4 class="mb-4"><i class="fas fa-cars"></i> Available Fleet</h4>
                        <div class="car-fleet-grid" id="carFleet">
                            <!-- Cars will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End of Main Content -->

        <?php include('includes/footer.php'); ?>
    </div>

    <!-- Rental Modal -->
    <div class="modal fade" id="rentalModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-car-side"></i> New Car Rental</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rentalForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Customer Name</label>
                                    <input type="text" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Phone Number</label>
                                    <input type="tel" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Email</label>
                                    <input type="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Pickup Date</label>
                                    <input type="date" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Return Date</label>
                                    <input type="date" class="form-control" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label>Car Model</label>
                                    <select class="form-control" required>
                                        <option>BMW 3 Series</option>
                                        <option>Toyota Camry</option>
                                        <option>Ford Mustang</option>
                                        <option>Honda Civic</option>
                                        <option>Nissan Altima</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label>Special Requirements</label>
                            <textarea class="form-control" rows="3" placeholder="GPS, child seats, etc."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveRental()">
                        <i class="fas fa-save"></i> Save Rental
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentDate = new Date();
        let selectedDate = null;
        let selectedCar = null;

        // Sample car rental data
        const carFleet = [
            { id: 1, name: 'BMW 3 Series', type: 'Luxury Sedan', price: '$89/day', seats: 5, transmission: 'Auto', fuel: 'Petrol', available: 3, icon: '🚗' },
            { id: 2, name: 'Toyota Camry', type: 'Mid-size Sedan', price: '$59/day', seats: 5, transmission: 'Auto', fuel: 'Hybrid', available: 5, icon: '🚙' },
            { id: 3, name: 'Ford Mustang', type: 'Sports Car', price: '$129/day', seats: 4, transmission: 'Manual', fuel: 'Petrol', available: 2, icon: '🏎️' },
            { id: 4, name: 'Honda Civic', type: 'Compact Car', price: '$39/day', seats: 5, transmission: 'Auto', fuel: 'Petrol', available: 7, icon: '🚗' },
            { id: 5, name: 'Nissan Altima', type: 'Mid-size Sedan', price: '$49/day', seats: 5, transmission: 'CVT', fuel: 'Petrol', available: 4, icon: '🚙' },
            { id: 6, name: 'Jeep Wrangler', type: 'SUV', price: '$99/day', seats: 5, transmission: 'Manual', fuel: 'Petrol', available: 2, icon: '🚜' }
        ];

        // Sample rental data
        const rentals = {
            '2024-12-15': [
                { car: 'BMW 3 Series', customer: 'John Smith', duration: '3 days' },
                { car: 'Toyota Camry', customer: 'Sarah Johnson', duration: '1 week' },
                { car: 'Ford Mustang', customer: 'Mike Davis', duration: 'Weekend' }
            ],
            '2024-12-18': [
                { car: 'Honda Civic', customer: 'Emily Brown', duration: '5 days' },
                { car: 'Nissan Altima', customer: 'David Wilson', duration: '2 weeks' }
            ],
            '2024-12-20': [
                { car: 'Jeep Wrangler', customer: 'Lisa Anderson', duration: '1 week' }
            ]
        };

        function generateCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const today = new Date();
            
            document.getElementById('calendarTitle').textContent = 
                new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(currentDate);

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDate = new Date(firstDay);
            startDate.setDate(startDate.getDate() - firstDay.getDay());

            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';

            // Add day headers
            const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            dayHeaders.forEach(day => {
                const header = document.createElement('div');
                header.className = 'calendar-day-header';
                header.textContent = day;
                calendarGrid.appendChild(header);
            });

            // Add calendar days
            for (let week = 0; week < 6; week++) {
                for (let day = 0; day < 7; day++) {
                    const date = new Date(startDate);
                    date.setDate(startDate.getDate() + (week * 7) + day);
                    
                    const dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day';
                    
                    if (date.getMonth() !== month) {
                        dayElement.classList.add('other-month');
                    }
                    
                    if (date.toDateString() === today.toDateString()) {
                        dayElement.classList.add('today');
                    }
                    
                    const dateString = date.toISOString().split('T')[0];
                    const dayRentals = rentals[dateString] || [];
                    const availableCars = carFleet.length - dayRentals.length;
                    
                    if (dayRentals.length >= carFleet.length * 0.8) {
                        dayElement.classList.add('high-demand');
                    } else if (dayRentals.length > 0) {
                        dayElement.classList.add('partially-booked');
                    } else if (date >= today && date.getMonth() === month) {
                        dayElement.classList.add('available');
                    }
                    
                    let statusClass, statusText;
                    if (availableCars > 3) {
                        statusClass = 'available-cars';
                        statusText = `${availableCars} available`;
                    } else if (availableCars > 0) {
                        statusClass = 'limited-cars';
                        statusText = `${availableCars} left`;
                    } else {
                        statusClass = 'no-cars';
                        statusText = 'Fully booked';
                    }
                    
                    dayElement.innerHTML = `
                        <div class="day-number">${date.getDate()}</div>
                        ${date.getMonth() === month ? `<div class="availability-indicator ${statusClass}">${statusText}</div>` : ''}
                        ${dayRentals.length > 0 ? `<div class="car-count">${dayRentals.length} rental${dayRentals.length > 1 ? 's' : ''}</div>` : ''}
                    `;
                    
                    dayElement.onclick = () => selectDate(date);
                    calendarGrid.appendChild(dayElement);
                }
            }
        }

        function generateCarFleet() {
            const carFleetContainer = document.getElementById('carFleet');
            carFleetContainer.innerHTML = '';

            carFleet.forEach(car => {
                let statusClass, statusText;
                if (car.available > 3) {
                    statusClass = 'status-available';
                    statusText = `${car.available} Available`;
                } else if (car.available > 0) {
                    statusClass = 'status-limited';
                    statusText = `${car.available} Left`;
                } else {
                    statusClass = 'status-unavailable';
                    statusText = 'Unavailable';
                }

                const carCard = document.createElement('div');
                carCard.className = 'car-card';
                carCard.innerHTML = `
                    <div class="car-image">${car.icon}</div>
                    <div class="car-details">
                        <div class="car-name">${car.name}</div>
                        <div class="car-type">${car.type}</div>
                        <div class="car-features">
                            <div class="feature"><i class="fas fa-users"></i> ${car.seats}</div>
                            <div class="feature"><i class="fas fa-cogs"></i> ${car.transmission}</div>
                            <div class="feature"><i class="fas fa-gas-pump"></i> ${car.fuel}</div>
                        </div>
                        <div class="car-price">${car.price}</div>
                        <div class="availability-status ${statusClass}">${statusText}</div>
                        <button class="btn-select" onclick="selectCar(${car.id})" ${car.available === 0 ? 'disabled' : ''}>
                            ${car.available > 0 ? 'Select Car' : 'Unavailable'}
                        </button>
                    </div>
                `;
                
                carFleetContainer.appendChild(carCard);
            });
        }

        function selectDate(date) {
            selectedDate = date;
            const dateString = date.toISOString().split('T')[0];
            
            // Update selected date display
            document.getElementById('selectedDate').textContent = 
                date.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });

            // Update rental date input
            document.getElementById('rentalDate').value = dateString;

            // Update rental list
            const rentalList = document.getElementById('rentalList');
            const dayRentals = rentals[dateString] || [];
            
            if (dayRentals.length > 0) {
                rentalList.innerHTML = `
                    <h6 class="mb-3"><i class="fas fa-car-side"></i> Rentals for ${date.toLocaleDateString()}</h6>
                    ${dayRentals.map(rental => `
                        <div class="rental-item">
                            <div class="rental-car">${rental.car}</div>
                            <div class="rental-customer">${rental.customer}</div>
                            <div class="rental-duration">
                                <i class="fas fa-clock"></i> ${rental.duration}
                            </div>
                        </div>
                    `).join('')}
                `;
            } else {
                rentalList.innerHTML = `
                    <h6 class="mb-3"><i class="fas fa-car-side"></i> No rentals for ${date.toLocaleDateString()}</h6>
                    <p class="text-muted">All cars are available for booking on this date.</p>
                `;
            }
        }

        function selectCar(carId) {
            // Remove previous selection
            document.querySelectorAll('.car-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked car
            event.target.closest('.car-card').classList.add('selected');
            selectedCar = carFleet.find(car => car.id === carId);
            
            // Show selection feedback
            const carName = selectedCar.name;
            const message = document.createElement('div');
            message.className = 'alert alert-success alert-dismissible fade show mt-3';
            message.innerHTML = `
                <strong>Car Selected!</strong> ${carName} is ready for booking.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            event.target.closest('.calendar-container').appendChild(message);
            
            // Auto-remove alert after 3 seconds
            setTimeout(() => {
                if (message && message.parentNode) {
                    message.remove();
                }
            }, 3000);
        }

        function changeMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            generateCalendar();
        }

        function goToToday() {
            currentDate = new Date();
            generateCalendar();
            selectDate(new Date());
        }

        function openRentalModal() {
            // In a real application, you would open a modal here
            alert('Rental booking modal would open here. Integrate with your preferred modal library (Bootstrap, etc.).');
        }

        function quickRental() {
            const customerName = document.getElementById('customerName').value;
            const rentalDate = document.getElementById('rentalDate').value;
            const rentalDuration = document.getElementById('rentalDuration').value;
            
            if (!customerName || !rentalDate || rentalDuration === 'Select Duration') {
                alert('Please fill in all fields');
                return;
            }
            
            if (!selectedCar) {
                alert('Please select a car from the fleet below');
                return;
            }
            
            // In a real application, you would save this to a database
            const rentalInfo = `
                Customer: ${customerName}
                Car: ${selectedCar.name}
                Date: ${new Date(rentalDate).toLocaleDateString()}
                Duration: ${rentalDuration}
                Daily Rate: ${selectedCar.price}
            `;
            
            alert(`Rental booking created successfully!\n\n${rentalInfo}`);
            
            // Clear form
            document.getElementById('customerName').value = '';
            document.getElementById('rentalDate').value = '';
            document.getElementById('rentalDuration').value = 'Select Duration';
            
            // Remove car selection
            document.querySelectorAll('.car-card').forEach(card => {
                card.classList.remove('selected');
            });
            selectedCar = null;
        }

        function saveRental() {
            // Handle full rental form submission
            alert('Complete rental booking would be saved here with full customer details');
        }

        // Initialize calendar and car fleet on page load
        document.addEventListener('DOMContentLoaded', function() {
            generateCalendar();
            generateCarFleet();
            selectDate(new Date());
            
            // Set today's date as default in the quick rental form
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('rentalDate').value = today;
        });
    </script>
</body>

</html>