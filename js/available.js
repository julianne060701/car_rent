// Complete available.js with all functionality
console.log('Available.js loaded');

// Global variables
let vehiclesData = [];
let vehiclePricing = {};
let bookingData = { modalBooking: {} };
let currentModal = null;

// Location pricing configuration (must match PHP)
const locationPricing = {
    'store': 0,
    'gensan-airport': 500,
    'downtown-gensan': 300,
    'kcc-mall': 500,
    'robinsons-place': 400,
    'sm-city-gensan': 400
};

// Location display names
const locationNames = {
    'store': 'Store',
    'gensan-airport': 'GenSan Airport',
    'downtown-gensan': 'Downtown GenSan',
    'kcc-mall': 'KCC Mall',
    'robinsons-place': 'Robinson\'s Place GenSan',
    'sm-city-gensan': 'SM City General Santos'
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    initializePageFunctionality();
});

function initializePageFunctionality() {
    console.log('Initializing page functionality...');
    
    // Extract vehicle data from page
    extractVehicleData();
    
    // Initialize booking modal
    initializeBookingModal();
    
    // Initialize filters
    initializeFilters();
    
    // Set up event listeners
    setupEventListeners();
    
    console.log('Page initialization complete');
}

function extractVehicleData() {
    console.log('Extracting vehicle data...');
    
    const vehicleCards = document.querySelectorAll('.vehicle-card');
    vehiclesData = [];
    vehiclePricing = {};
    
    vehicleCards.forEach(card => {
        const carId = card.getAttribute('data-car-id');
        const carName = card.querySelector('h3').textContent.trim();
        const priceText = card.querySelector('.price-badge').textContent;
        const price = parseFloat(priceText.replace(/[^\d.]/g, ''));
        const capacity = parseInt(card.getAttribute('data-capacity'));
        const transmission = card.getAttribute('data-transmission');
        const isAvailable = card.getAttribute('data-available') === '1';
        
        const vehicleData = {
            car_id: carId,
            car_name: carName,
            rate_per_day: price,
            rate_24h: price,
            passenger_seater: capacity,
            transmission: transmission,
            is_available: isAvailable
        };
        
        vehiclesData.push(vehicleData);
        
        // Store pricing info
        vehiclePricing[carName] = {
            dailyRate: price,
            hourlyRate: Math.round(price / 24)
        };
    });
    
    console.log('Vehicle data extracted:', vehiclesData.length, 'vehicles');
}

// Vehicle selection functions
function selectVehicle(carId) {
    console.log('Selecting vehicle with ID:', carId);
    
    const vehicleCard = document.querySelector(`[data-car-id="${carId}"]`);
    if (!vehicleCard) {
        console.error('Vehicle card not found for ID:', carId);
        showMessage('Vehicle not found. Please refresh the page and try again.', 'error');
        return;
    }
    
    const vehicleName = vehicleCard.querySelector('h3').textContent.trim();
    const isAvailable = vehicleCard.getAttribute('data-available') === '1';
    
    if (!isAvailable) {
        showUnavailableMessage();
        return;
    }
    
    selectVehicleByName(vehicleName);
}

function selectVehicleByName(vehicleName) {
    console.log('Selecting vehicle by name:', vehicleName);
    
    // Check availability
    if (vehiclesData.length > 0) {
        const vehicle = vehiclesData.find(v => v.car_name === vehicleName);
        if (!vehicle) {
            showMessage('Vehicle not found. Please refresh the page and try again.', 'error');
            return;
        }
        
        if (!vehicle.is_available) {
            showUnavailableMessage();
            return;
        }
    }
    
    // Set selected vehicle
    const selectedVehicleInput = document.getElementById('selected-vehicle');
    if (selectedVehicleInput) {
        selectedVehicleInput.value = vehicleName;
        console.log('Set selected vehicle to:', vehicleName);
    }
    
    // Open booking modal
    openBookingModal();
}

function selectVehicleFromModal() {
    const selectedVehicle = document.getElementById('selected-vehicle');
    if (selectedVehicle && selectedVehicle.value) {
        console.log('Booking vehicle from modal:', selectedVehicle.value);
        // Modal is already open, just scroll to form or validate
        validateForm();
    } else {
        showMessage('No vehicle selected. Please try again.', 'error');
    }
}

function showUnavailableMessage() {
    const unavailableMessage = `
        <div class="text-center">
            <i class="fas fa-ban text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Vehicle Unavailable</h3>
            <p class="text-red-600 mb-2">This vehicle is currently not available for booking.</p>
            <p class="text-sm text-gray-500">Please select another vehicle or try different dates.</p>
        </div>
    `;
    showMessage(unavailableMessage, 'error');
}

// Modal functions
function openBookingModal() {
    console.log('Opening booking modal...');
    
    const modal = document.getElementById('booking-modal');
    if (!modal) {
        console.error('Booking modal not found!');
        alert('Booking form not available. Please refresh the page and try again.');
        return;
    }
    
    // Close any existing modals first
    closeAllModals();
    
    // Show modal
    modal.style.display = 'flex';
    modal.offsetHeight; // Force reflow
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    currentModal = 'booking-modal';
    
    // Set minimum dates
    setMinimumDates();
    
    // Load any saved data
    loadBookingModal();
    
    // Calculate cost if form has data
    setTimeout(() => {
        calculateRentalCost();
    }, 100);
    
    console.log('Booking modal opened');
}

function closeModal101(modalId) {
    console.log('Closing modal:', modalId);
    
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error('Modal not found:', modalId);
        return;
    }
    
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.style.display = 'none';
        if (currentModal === modalId) {
            document.body.style.overflow = 'auto';
            currentModal = null;
        }
    }, 300);
}

function closeAllModals() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.remove('show');
        modal.style.display = 'none';
    });
    document.body.style.overflow = 'auto';
    currentModal = null;
}

// Car details modal functions
function viewDetails(carData) {
    console.log('Viewing details for:', carData.car_name);
    
    const modal = document.getElementById('carModal');
    if (!modal) {
        console.error('Car details modal not found');
        return;
    }
    
    // Populate modal with car data
    populateCarModal(carData);
    
    // Show modal
    modal.style.display = 'flex';
    modal.offsetHeight;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function populateCarModal(car) {
    // Update modal content with car details
    const elements = {
        'modalTitle': car.car_name,
        'modalType': getVehicleType(car.passenger_seater),
        'modalPassengers': car.passenger_seater + ' passengers',
        'modalTransmission': car.transmission.charAt(0).toUpperCase() + car.transmission.slice(1),
        'modalDailyRate': '₱' + parseFloat(car.rate_24h || car.rate_per_day || 2500).toLocaleString(),
        'modal6Rate': '₱' + parseFloat(car.rate_6h || (car.rate_24h || car.rate_per_day || 2500) * 0.3).toLocaleString(),
        'modal8Rate': '₱' + parseFloat(car.rate_8h || (car.rate_24h || car.rate_per_day || 2500) * 0.4).toLocaleString(),
        'modal12Rate': '₱' + parseFloat(car.rate_12h || (car.rate_24h || car.rate_per_day || 2500) * 0.6).toLocaleString(),
        'modalDescription': car.description || 'Well-maintained vehicle with modern amenities and safety features. Perfect for both business and leisure travel in General Santos City.'
    };
    
    // Update text elements
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = elements[id];
        }
    });
    
    // Update image
    const modalImage = document.getElementById('modalImage');
    if (modalImage) {
        const imageSrc = car.car_image ? `uploads/cars/${car.car_image}` : '../assets/img/no-image.png';
        modalImage.src = imageSrc;
        modalImage.alt = car.car_name;
    }
    
    // Update availability badge
    const availabilityBadge = document.getElementById('modalAvailabilityBadge');
    if (availabilityBadge) {
        if (car.is_available) {
            availabilityBadge.innerHTML = '<span class="car-status-available">Available</span>';
        } else {
            availabilityBadge.innerHTML = '<span class="car-status-booked">Not Available</span>';
        }
    }
    
    // Update features
    const featuresContainer = document.getElementById('modalFeatures');
    if (featuresContainer) {
        featuresContainer.innerHTML = `
            <span class="feature-badge">Air Conditioning</span>
            <span class="feature-badge">GPS Navigation</span>
            <span class="feature-badge">Bluetooth</span>
            <span class="feature-badge">Insurance Included</span>
            <span class="feature-badge">24/7 Support</span>
            ${car.passenger_seater >= 7 ? '<span class="feature-badge">Family Size</span>' : ''}
            ${car.transmission === 'automatic' ? '<span class="feature-badge">Automatic</span>' : '<span class="feature-badge">Manual</span>'}
        `;
    }
    
    // Update rating
    const rating = 4.2 + (parseInt(car.car_id) % 10) * 0.1;
    const modalRating = document.getElementById('modalRating');
    const modalRatingText = document.getElementById('modalRatingText');
    
    if (modalRating) {
        modalRating.innerHTML = getStarRating(rating);
    }
    
    if (modalRatingText) {
        modalRatingText.textContent = `${rating.toFixed(1)} (${Math.floor(Math.random() * 100) + 50} reviews)`;
    }
    
    // Update book button
    const modalBookButton = document.getElementById('modalBookButton');
    if (modalBookButton) {
        if (car.is_available) {
            modalBookButton.disabled = false;
            modalBookButton.innerHTML = '<i class="fas fa-car mr-2"></i>Book This Vehicle';
            modalBookButton.onclick = () => {
                closeModal();
                selectVehicleByName(car.car_name);
            };
        } else {
            modalBookButton.disabled = true;
            modalBookButton.innerHTML = '<i class="fas fa-ban mr-2"></i>Not Available';
        }
    }
}

function closeModal() {
    const modal = document.getElementById('carModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
        document.body.style.overflow = 'auto';
    }
}

// Message modal functions
function showMessage(message, type = 'info') {
    const messageModal = document.getElementById('message-modal');
    const messageContent = document.getElementById('message-content');
    
    if (!messageModal || !messageContent) {
        console.error('Message modal elements not found');
        alert(message.replace(/<[^>]*>/g, ''));
        return;
    }
    
    let iconClass = 'fas fa-info-circle text-blue-600';
    let bgColor = 'bg-blue-50';
    
    if (type === 'success') {
        iconClass = 'fas fa-check-circle text-green-600';
        bgColor = 'bg-green-50';
    } else if (type === 'error') {
        iconClass = 'fas fa-exclamation-triangle text-red-600';
        bgColor = 'bg-red-50';
    }
    
    messageContent.innerHTML = `
        <div class="text-center ${bgColor} rounded-lg p-6">
            <i class="${iconClass}" style="font-size: 4rem; margin-bottom: 20px; display: block;"></i>
            <div style="margin-bottom: 24px;">${message}</div>
            <button onclick="closeModal101('message-modal')" class="btn btn-primary px-8 py-3 text-lg">OK</button>
        </div>
    `;
    
    messageModal.style.display = 'flex';
    messageModal.offsetHeight;
    messageModal.classList.add('show');
    currentModal = 'message-modal';
    
    // Auto-close success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            if (messageModal.classList.contains('show')) {
                closeModal101('message-modal');
            }
        }, 5000);
    }
}

// Cost calculation functions
function calculateRentalCost() {
    const selectedVehicle = document.getElementById('selected-vehicle');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const startTime = document.getElementById('start-time');
    const endTime = document.getElementById('end-time');
    const pickupLocation = document.getElementById('pickup-location-modal');
    const returnLocation = document.getElementById('return-location-modal');

    // Check if all required elements exist
    if (!selectedVehicle || !startDate || !endDate || !startTime || !endTime) {
        console.log('Cost calculation elements not found');
        hideCostSummary();
        return;
    }

    // Check if all required values are provided
    if (!selectedVehicle.value || !startDate.value || !endDate.value || !startTime.value || !endTime.value) {
        hideCostSummary();
        return;
    }

    try {
        // Get vehicle pricing
        let vehicle = vehiclePricing[selectedVehicle.value];
        
        if (!vehicle && vehiclesData.length > 0) {
            const vehicleData = vehiclesData.find(v => v.car_name === selectedVehicle.value);
            if (vehicleData) {
                vehicle = {
                    dailyRate: parseFloat(vehicleData.rate_per_day) || parseFloat(vehicleData.rate_24h) || 2500,
                    hourlyRate: parseFloat(vehicleData.hourly_rate) || Math.round((parseFloat(vehicleData.rate_per_day) || 2500) / 24)
                };
            }
        }
        
        if (!vehicle) {
            vehicle = {
                dailyRate: 2500,
                hourlyRate: 300
            };
            console.warn('Vehicle pricing not found, using defaults for:', selectedVehicle.value);
        }

        // Calculate duration
        const startDateTime = new Date(`${startDate.value} ${startTime.value}`);
        const endDateTime = new Date(`${endDate.value} ${endTime.value}`);
        const diffMs = endDateTime - startDateTime;
        const totalHours = Math.max(diffMs / (1000 * 60 * 60), 8);

        if (totalHours <= 0 || isNaN(totalHours)) {
            hideCostSummary();
            return;
        }

        // Calculate vehicle cost
        let vehicleCost;
        let rateText;

        if (totalHours >= 24) {
            const days = Math.ceil(totalHours / 24);
            vehicleCost = vehicle.dailyRate * days;
            rateText = `₱${vehicle.dailyRate.toLocaleString()}/day × ${days} day${days > 1 ? 's' : ''}`;
        } else {
            vehicleCost = vehicle.hourlyRate * Math.ceil(totalHours);
            rateText = `₱${vehicle.hourlyRate.toLocaleString()}/hour × ${Math.ceil(totalHours)} hours`;
        }

        // Calculate location charges
        const pickupCharge = pickupLocation && pickupLocation.value ? (locationPricing[pickupLocation.value] || 0) : 0;
        const returnCharge = (returnLocation && returnLocation.value && returnLocation.value !== pickupLocation.value) ? 
                            (locationPricing[returnLocation.value] || 0) : 0;
        const totalLocationCharge = pickupCharge + returnCharge;
        const totalCost = vehicleCost + totalLocationCharge;

        // Update cost summary
        updateCostSummary({
            vehicle: selectedVehicle.value,
            duration: Math.ceil(totalHours),
            rate: rateText,
            locationCharge: totalLocationCharge,
            totalCost: totalCost
        });

        showCostSummary();
        
    } catch (error) {
        console.error('Error calculating rental cost:', error);
        hideCostSummary();
    }
}

function updateCostSummary(data) {
    const elements = {
        'summary-vehicle': data.vehicle,
        'summary-duration': `${data.duration} hours`,
        'summary-rate': data.rate,
        'summary-location': `₱${data.locationCharge.toLocaleString()}`,
        'summary-total': `₱${data.totalCost.toLocaleString()}`
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = elements[id];
        }
    });
}

function showCostSummary() {
    const costSummary = document.getElementById('cost-summary');
    if (costSummary) {
        costSummary.classList.remove('hidden');
    }
}

function hideCostSummary() {
    const costSummary = document.getElementById('cost-summary');
    if (costSummary) {
        costSummary.classList.add('hidden');
    }
}

// Form validation and submission
function validateForm() {
    const form = document.getElementById('booking-form');
    if (!form) {
        console.error('Booking form not found');
        return false;
    }
    
    // Clear previous error states
    clearFormErrors(form);
    
    const requiredFields = [
        { name: 'customer_name', label: 'Full Name' },
        { name: 'customer_phone', label: 'Phone Number' },
        { name: 'customer_email', label: 'Email Address' },
        { name: 'license_number', label: 'License Number' },
        { name: 'start_date', label: 'Pickup Date' },
        { name: 'end_date', label: 'Return Date' },
        { name: 'start_time', label: 'Pickup Time' },
        { name: 'end_time', label: 'Return Time' },
        { name: 'pickup_location', label: 'Pickup Location' },
        { name: 'selected_vehicle', label: 'Selected Vehicle' }
    ];
    
    let isValid = true;
    let firstErrorField = null;
    
    // Check required fields
    requiredFields.forEach(field => {
        const element = form.elements[field.name];
        if (!element || !element.value.trim()) {
            isValid = false;
            if (element) {
                markFieldError(element);
                if (!firstErrorField) {
                    firstErrorField = element;
                }
            }
        }
    });
    
    if (!isValid) {
        showMessage('Please fill in all required fields.', 'error');
        if (firstErrorField) {
            firstErrorField.focus();
        }
        return false;
    }
    
    // Validate email
    const email = form.elements['customer_email'];
    if (email && email.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            markFieldError(email);
            showMessage('Please enter a valid email address.', 'error');
            email.focus();
            return false;
        }
    }
    
    // Validate phone number
    const phone = form.elements['customer_phone'];
    if (phone && phone.value) {
        const phoneRegex = /[\d\+\-\(\)\s]{10,}/;
        if (!phoneRegex.test(phone.value)) {
            markFieldError(phone);
            showMessage('Please enter a valid phone number.', 'error');
            phone.focus();
            return false;
        }
    }
    
    // Validate dates
    const startDate = form.elements['start_date'];
    const endDate = form.elements['end_date'];
    const startTime = form.elements['start_time'];
    const endTime = form.elements['end_time'];
    
    if (startDate.value && endDate.value && startTime.value && endTime.value) {
        const startDateTime = new Date(`${startDate.value} ${startTime.value}`);
        const endDateTime = new Date(`${endDate.value} ${endTime.value}`);
        const now = new Date();
        
        if (startDateTime < now) {
            markFieldError(startDate);
            markFieldError(startTime);
            showMessage('Pickup date and time cannot be in the past.', 'error');
            startDate.focus();
            return false;
        }
        
        if (endDateTime <= startDateTime) {
            markFieldError(endDate);
            markFieldError(endTime);
            showMessage('Return date and time must be after pickup date and time.', 'error');
            endDate.focus();
            return false;
        }
        
        const diffMs = endDateTime - startDateTime;
        const totalHours = diffMs / (1000 * 60 * 60);
        
        if (totalHours < 8) {
            markFieldError(endDate);
            markFieldError(endTime);
            showMessage('Minimum rental period is 8 hours.', 'error');
            endDate.focus();
            return false;
        }
    }
    
    return true;
}

function clearFormErrors(form) {
    form.querySelectorAll('.error-field').forEach(field => {
        field.classList.remove('error-field');
        field.style.borderColor = '';
    });
}

function markFieldError(field) {
    field.classList.add('error-field');
    field.style.borderColor = '#ef4444';
}

function handleFormSubmission(e) {
    e.preventDefault();
    console.log('Form submitted');
    
    if (!validateForm()) {
        console.log('Form validation failed');
        return;
    }
    
    console.log('Form validation passed, submitting...');
    const formData = new FormData(e.target);
    
    // Log form data for debugging
    console.log('Form data being submitted:');
    for (let [key, value] of formData.entries()) {
        if (key === 'upload_image') {
            console.log(`${key}: [File: ${value.name}, Size: ${value.size}]`);
        } else {
            console.log(`${key}: ${value}`);
        }
    }
    
    submitBookingToServer(formData);
}

function submitBookingToServer(formData) {
    const submitButton = document.querySelector('#booking-form button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Disable button and show loading
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
    submitButton.disabled = true;

    console.log('=== SUBMITTING BOOKING ===');
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout

    fetch('save_booking.php', {
        method: 'POST',
        body: formData,
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        return response.text();
    })
    .then(text => {
        console.log('Response text length:', text.length);
        console.log('Response text:', text);
        
        // Handle empty response
        if (!text.trim()) {
            throw new Error('Empty response from server. This usually indicates a PHP error. Check server error logs.');
        }
        
        // Check for HTML error page
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
            console.error('HTML response received instead of JSON');
            throw new Error('Server returned HTML error page instead of JSON. Check server logs for PHP errors.');
        }
        
        // Parse JSON
        let data;
        try {
            data = JSON.parse(text);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error(`Invalid JSON response: ${text.substring(0, 200)}...`);
        }
        
        console.log('Parsed response:', data);
        
        if (data.status === 'success') {
            handleBookingSuccess(data);
        } else {
            handleBookingError(data);
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        console.error('Booking submission error:', error);
        handleSubmissionError(error);
    })
    .finally(() => {
        // Re-enable button
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

function handleBookingSuccess(data) {
    const successMessage = `
        <div class="text-center">
            <div class="mb-6">
                <i class="fas fa-check-circle text-green-600" style="font-size: 5rem; margin-bottom: 20px; display: block;"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-3">Booking Successful!</h3>
                <p class="text-green-600 text-lg mb-4">Your reservation request has been submitted.</p>
            </div>
            
            <div class="bg-gray-50 p-6 rounded-lg text-left space-y-3 text-sm mb-6">
                <div class="flex justify-between">
                    <strong>Booking Reference:</strong> 
                    <span class="font-mono text-blue-600 text-lg">${data.booking_reference || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                    <strong>Vehicle:</strong> 
                    <span>${data.vehicle || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                    <strong>Total Cost:</strong> 
                    <span class="text-green-600 font-bold text-lg">₱${data.total_cost || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                    <strong>Duration:</strong> 
                    <span>${data.total_hours || 'N/A'} hours</span>
                </div>
                <div class="flex justify-between">
                    <strong>Pickup:</strong> 
                    <span>${data.pickup_date || 'N/A'} at ${data.pickup_time || 'N/A'}</span>
                </div>
                <div class="flex justify-between">
                    <strong>Return:</strong> 
                    <span>${data.return_date || 'N/A'} at ${data.return_time || 'N/A'}</span>
                </div>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg mb-6">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    We will contact you within 24 hours to confirm your reservation and provide pickup instructions.
                </p>
            </div>
            
            <div class="text-sm text-gray-600">
                <p>Please save your booking reference for your records.</p>
                <p>You will receive a confirmation email shortly.</p>
            </div>
        </div>
    `;
    
    showMessage(successMessage, 'success');
    
    // Reset form and close modal
    resetBookingForm();
    closeModal101('booking-modal');
}

function handleBookingError(data) {
    const errorMessage = `
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Booking Failed</h3>
            <p class="text-red-600 mb-4">${data.message || 'An unknown error occurred.'}</p>
            <p class="text-sm text-gray-500">Please try again or contact support if the problem persists.</p>
        </div>
    `;
    showMessage(errorMessage, 'error');
}

function handleSubmissionError(error) {
    let errorMessage = '';
    
    if (error.name === 'AbortError') {
        errorMessage = `
            <div class="text-center">
                <i class="fas fa-clock text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Request Timeout</h3>
                <p class="text-red-600 mb-2">The request took too long to complete.</p>
                <p class="text-sm text-gray-500">Please check your internet connection and try again.</p>
            </div>
        `;
    } else if (error.message.includes('HTML error page')) {
        errorMessage = `
            <div class="text-center">
                <i class="fas fa-code text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Server Error</h3>
                <p class="text-red-600 mb-2">The server encountered an error.</p>
                <p class="text-sm text-gray-500">Please try again later or contact support.</p>
            </div>
        `;
    } else if (error.message.includes('Failed to fetch') || error.message.includes('network')) {
        errorMessage = `
            <div class="text-center">
                <i class="fas fa-wifi text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Network Error</h3>
                <p class="text-red-600 mb-2">Unable to connect to the server.</p>
                <p class="text-sm text-gray-500">Please check your internet connection and try again.</p>
            </div>
        `;
    } else {
        errorMessage = `
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Submission Error</h3>
                <p class="text-red-600 mb-2">${error.message}</p>
                <p class="text-sm text-gray-500">Please try again or contact support if the problem persists.</p>
            </div>
        `;
    }
    
    showMessage(errorMessage, 'error');
}

// Image upload functions
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            showMessage('Please upload an image file (JPG, PNG, GIF) or PDF document.', 'error');
            input.value = '';
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showMessage('File size must be less than 10MB.', 'error');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('previewImage');
            const previewDiv = document.getElementById('imagePreview');
            const uploadContainer = document.getElementById('imageUploadContainer');
            
            if (previewImage && previewDiv && uploadContainer) {
                previewImage.src = e.target.result;
                previewDiv.classList.remove('hidden');
                uploadContainer.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    }
}

function changeImage() {
    const fileInput = document.getElementById('upload-image');
    if (fileInput) {
        fileInput.click();
    }
}

function removeImage() {
    const fileInput = document.getElementById('upload-image');
    const previewDiv = document.getElementById('imagePreview');
    const uploadContainer = document.getElementById('imageUploadContainer');
    
    if (fileInput) fileInput.value = '';
    if (previewDiv) previewDiv.classList.add('hidden');
    if (uploadContainer) uploadContainer.style.display = 'block';
}

// Utility functions
function getVehicleType(seater) {
    if (seater <= 4) return 'Sedan';
    if (seater <= 7) return 'SUV/MPV';
    return 'Van';
}

function getStarRating(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = (rating - fullStars) >= 0.5;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    
    let stars = '';
    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }
    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
    }
    
    return stars;
}

function setMinimumDates() {
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput) {
        startDateInput.min = today;
        
        // Set default to tomorrow if no value
        if (!startDateInput.value) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            startDateInput.value = tomorrow.toISOString().split('T')[0];
        }
    }
    
    if (endDateInput) {
        endDateInput.min = today;
        
        // Set default to day after pickup if no value
        if (!endDateInput.value && startDateInput && startDateInput.value) {
            const pickupDate = new Date(startDateInput.value);
            pickupDate.setDate(pickupDate.getDate() + 1);
            endDateInput.value = pickupDate.toISOString().split('T')[0];
        }
    }
}

function resetBookingForm() {
    const form = document.getElementById('booking-form');
    if (form) {
        form.reset();
        clearFormErrors(form);
        hideCostSummary();
        removeImage();
    }
}

function saveBookingModal() {
    const form = document.getElementById('booking-form');
    if (!form) return;
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        if (key !== 'upload_image') { // Don't save file data
            data[key] = value;
        }
    }
    
    bookingData.modalBooking = data;
    console.log('Booking data saved:', data);
}

function loadBookingModal() {
    const savedData = bookingData.modalBooking;
    if (Object.keys(savedData).length > 0) {
        const form = document.getElementById('booking-form');
        if (!form) return;
        
        for (const [key, value] of Object.entries(savedData)) {
            const element = form.elements[key];
            if (element && key !== 'upload_image') {
                element.value = value;
            }
        }
        
        console.log('Booking data loaded');
    }
}

function initializeBookingModal() {
    console.log('Initializing booking modal...');
    
    const form = document.getElementById('booking-form');
    if (form) {
        // Remove any existing listeners
        form.removeEventListener('submit', handleFormSubmission);
        form.addEventListener('submit', handleFormSubmission);
        
        // Add event listeners for cost calculation
        const costElements = ['start-date', 'end-date', 'start-time', 'end-time', 'pickup-location-modal', 'return-location-modal'];
        costElements.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', calculateRentalCost);
                element.addEventListener('input', calculateRentalCost);
            }
        });
        
        console.log('Booking modal initialized');
    }
}

function initializeFilters() {
    // Availability filters
    const showAvailable = document.getElementById('show-available');
    const showUnavailable = document.getElementById('show-unavailable');
    
    if (showAvailable) {
        showAvailable.addEventListener('change', applyFilters);
    }
    
    if (showUnavailable) {
        showUnavailable.addEventListener('change', applyFilters);
    }
    
    // Capacity filters
    const capacityFilters = document.querySelectorAll('.capacity-filter');
    capacityFilters.forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
    
    // Transmission filters
    const transmissionFilters = document.querySelectorAll('.transmission-filter');
    transmissionFilters.forEach(filter => {
        filter.addEventListener('change', applyFilters);
    });
}

function applyFilters() {
    const showAvailable = document.getElementById('show-available')?.checked ?? true;
    const showUnavailable = document.getElementById('show-unavailable')?.checked ?? false;
    
    const selectedCapacities = Array.from(document.querySelectorAll('.capacity-filter:checked')).map(cb => cb.value);
    const selectedTransmissions = Array.from(document.querySelectorAll('.transmission-filter:checked')).map(cb => cb.value);
    
    const vehicleCards = document.querySelectorAll('.vehicle-card');
    let visibleCount = 0;
    let availableCount = 0;
    
    vehicleCards.forEach(card => {
        const isAvailable = card.getAttribute('data-available') === '1';
        const capacity = parseInt(card.getAttribute('data-capacity'));
        const transmission = card.getAttribute('data-transmission');
        
        let showCard = true;
        
        // Availability filter
        if (!showAvailable && isAvailable) showCard = false;
        if (!showUnavailable && !isAvailable) showCard = false;
        
        // Capacity filter
        if (selectedCapacities.length > 0) {
            let matchesCapacity = false;
            selectedCapacities.forEach(range => {
                if (range === '1-4' && capacity >= 1 && capacity <= 4) matchesCapacity = true;
                if (range === '5-7' && capacity >= 5 && capacity <= 7) matchesCapacity = true;
                if (range === '8+' && capacity >= 8) matchesCapacity = true;
            });
            if (!matchesCapacity) showCard = false;
        }
        
        // Transmission filter
        if (selectedTransmissions.length > 0 && !selectedTransmissions.includes(transmission)) {
            showCard = false;
        }
        
        // Show/hide card
        if (showCard) {
            card.style.display = 'block';
            visibleCount++;
            if (isAvailable) availableCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update results count
    const resultsCount = document.getElementById('results-count');
    if (resultsCount) {
        const totalCards = vehicleCards.length;
        resultsCount.textContent = `Showing ${visibleCount} of ${totalCards} cars (${availableCount} available)`;
    }
}

function setupEventListeners() {
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const bookingModal = document.getElementById('booking-modal');
        const messageModal = document.getElementById('message-modal');
        const carModal = document.getElementById('carModal');
        
        if (event.target === bookingModal) {
            closeModal101('booking-modal');
        }
        
        if (event.target === messageModal) {
            closeModal101('message-modal');
        }
        
        if (event.target === carModal) {
            closeModal();
        }
    });
    
    // Handle escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            if (currentModal) {
                if (currentModal === 'booking-modal') {
                    closeModal101('booking-modal');
                } else if (currentModal === 'message-modal') {
                    closeModal101('message-modal');
                }
            } else {
                // Check for car modal
                const carModal = document.getElementById('carModal');
                if (carModal && carModal.style.display === 'flex') {
                    closeModal();
                }
            }
        }
    });
}

// Global functions for HTML onclick events
window.selectVehicle = selectVehicle;
window.selectVehicleByName = selectVehicleByName;
window.selectVehicleFromModal = selectVehicleFromModal;
window.openBookingModal = openBookingModal;
window.closeModal101 = closeModal101;
window.closeModal = closeModal;
window.viewDetails = viewDetails;
window.showMessage = showMessage;
window.previewImage = previewImage;
window.changeImage = changeImage;
window.removeImage = removeImage;
window.calculateRentalCost = calculateRentalCost;
window.applyFilters = applyFilters;

console.log('Available.js initialization complete');