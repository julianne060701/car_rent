// FIXED JavaScript with proper database integration and form handling
// Global variables
let vehiclesData = [];
let vehiclePricing = {};
let bookingData = { modalBooking: {} };

// Location pricing configuration (must match PHP)
const locationPricing = {
    'store': 0,
    'gensan-airport': 500,
    'downtown-gensan': 300,
    'kcc-mall': 500,
    'robinsons-place': 400,
    'sm-city-gensan': 400
};

// FIXED Vehicle selection functions
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
    
    const selectedVehicleInput = document.getElementById('selected-vehicle');
    if (selectedVehicleInput) {
        selectedVehicleInput.value = vehicleName;
    }
    
    openBookingModal();
    setTimeout(calculateRentalCost, 100);
}

function selectVehicleByName(vehicleName) {
    console.log('Selecting vehicle by name:', vehicleName);
    
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
    
    const selectedVehicleInput = document.getElementById('selected-vehicle');
    if (selectedVehicleInput) {
        selectedVehicleInput.value = vehicleName;
    }
    
    openBookingModal();
    setTimeout(calculateRentalCost, 100);
}

function showUnavailableMessage() {
    const unavailableMessage = `
        <div class="text-center">
            <i class="fas fa-ban text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Vehicle Unavailable</h3>
            <p class="text-red-600 mb-2">This vehicle is currently not available for booking.</p>
            <p class="text-sm text-gray-500">Please select another vehicle or try again later.</p>
        </div>
    `;
    showMessage(unavailableMessage, 'error');
}

// FIXED Modal functions with proper display control
function openBookingModal() {
    console.log('openBookingModal() called');
    
    const modal = document.getElementById('booking-modal');
    if (!modal) {
        console.error('Booking modal element not found!');
        alert('Booking modal not found. Please refresh the page and try again.');
        return;
    }
    
    console.log('Modal found, opening...');
    modal.style.display = 'flex';
    // Force browser to apply display change before adding show class
    modal.offsetHeight;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Set minimum dates
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput) startDateInput.min = today;
    if (endDateInput) endDateInput.min = today;
    
    loadBookingModal();
    setTimeout(calculateRentalCost, 100);
    console.log('Modal should now be visible');
}

function closeModal101(modalId) {
    console.log('closeModal101() called for:', modalId);
    
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); // Wait for animation to complete
        document.body.style.overflow = 'auto';
        console.log('Modal closed:', modalId);
    } else {
        console.error('Could not find modal to close:', modalId);
    }
}

// FIXED Show message function with proper HTML support
function showMessage(message, type = 'info') {
    const messageModal = document.getElementById('message-modal');
    const messageContent = document.getElementById('message-content');
    
    if (!messageModal || !messageContent) {
        console.error('Message modal elements not found');
        alert(message.replace(/<[^>]*>/g, '')); // Remove HTML tags for alert
        return;
    }
    
    let iconClass = 'fas fa-info-circle text-blue-600';
    if (type === 'success') {
        iconClass = 'fas fa-check-circle text-green-600';
    } else if (type === 'error') {
        iconClass = 'fas fa-exclamation-triangle text-red-600';
    }
    
    messageContent.innerHTML = `
        <div class="text-center">
            <i class="${iconClass}" style="font-size: 4rem; margin-bottom: 20px; display: block;"></i>
            <div style="margin-bottom: 20px;">${message}</div>
            <button onclick="closeModal101('message-modal')" class="btn btn-primary">OK</button>
        </div>
    `;
    
    messageModal.style.display = 'flex';
    messageModal.offsetHeight; // Force browser to apply display change
    messageModal.classList.add('show');
    
    if (type === 'success') {
        setTimeout(() => {
            if (messageModal.classList.contains('show')) {
                closeModal101('message-modal');
            }
        }, 5000);
    }
}

// FIXED Cost calculation with location charges
function calculateRentalCost() {
    const selectedVehicle = document.getElementById('selected-vehicle');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const startTime = document.getElementById('start-time');
    const endTime = document.getElementById('end-time');
    const pickupLocation = document.getElementById('pickup-location-modal');
    const returnLocation = document.getElementById('return-location-modal');

    if (!selectedVehicle || !startDate || !endDate || !startTime || !endTime) {
        console.log('Some required elements not found for cost calculation');
        const costSummary = document.getElementById('cost-summary');
        if (costSummary) costSummary.classList.add('hidden');
        return;
    }

    if (!selectedVehicle.value || !startDate.value || !endDate.value || !startTime.value || !endTime.value) {
        const costSummary = document.getElementById('cost-summary');
        if (costSummary) costSummary.classList.add('hidden');
        return;
    }

    // Get vehicle pricing
    let vehicle = vehiclePricing[selectedVehicle.value];
    
    if (!vehicle && vehiclesData.length > 0) {
        const vehicleData = vehiclesData.find(v => v.car_name === selectedVehicle.value);
        if (vehicleData) {
            vehicle = {
                dailyRate: parseFloat(vehicleData.rate_per_day) || parseFloat(vehicleData.rate_24h) || 2500,
                hourlyRate: parseFloat(vehicleData.hourly_rate) || 300
            };
        }
    }
    
    if (!vehicle) {
        vehicle = {
            dailyRate: 2500,
            hourlyRate: 300
        };
        console.warn('Vehicle pricing not found, using default rates for:', selectedVehicle.value);
    }

    const startDateTime = new Date(`${startDate.value} ${startTime.value}`);
    const endDateTime = new Date(`${endDate.value} ${endTime.value}`);
    const diffMs = endDateTime - startDateTime;
    const totalHours = Math.max(diffMs / (1000 * 60 * 60), 8);

    if (totalHours <= 0) {
        const costSummary = document.getElementById('cost-summary');
        if (costSummary) costSummary.classList.add('hidden');
        return;
    }

    let vehicleCost;
    let rateText;

    if (totalHours >= 24) {
        const days = Math.ceil(totalHours / 24);
        vehicleCost = vehicle.dailyRate * days;
        rateText = `₱${vehicle.dailyRate.toLocaleString()}/day × ${days} day${days > 1 ? 's' : ''}`;
    } else {
        vehicleCost = vehicle.hourlyRate * Math.ceil(totalHours);
        rateText = `₱${vehicle.hourlyRate}/hour × ${Math.ceil(totalHours)} hours`;
    }

    // Calculate location charges
    const pickupCharge = pickupLocation.value ? (locationPricing[pickupLocation.value] || 0) : 0;
    const returnCharge = (returnLocation.value && returnLocation.value !== pickupLocation.value) ? 
                        (locationPricing[returnLocation.value] || 0) : 0;
    const totalLocationCharge = pickupCharge + returnCharge;
    const totalCost = vehicleCost + totalLocationCharge;

    // Update cost summary elements
    const summaryVehicle = document.getElementById('summary-vehicle');
    const summaryDuration = document.getElementById('summary-duration');
    const summaryRate = document.getElementById('summary-rate');
    const summaryLocation = document.getElementById('summary-location');
    const summaryTotal = document.getElementById('summary-total');
    const costSummary = document.getElementById('cost-summary');

    if (summaryVehicle) summaryVehicle.textContent = selectedVehicle.value;
    if (summaryDuration) summaryDuration.textContent = `${Math.ceil(totalHours)} hours`;
    if (summaryRate) summaryRate.textContent = rateText;
    if (summaryLocation) summaryLocation.textContent = `₱${totalLocationCharge.toLocaleString()}`;
    if (summaryTotal) summaryTotal.textContent = `₱${totalCost.toLocaleString()}`;
    if (costSummary) costSummary.classList.remove('hidden');
}

// FIXED Form validation
function validateForm() {
    const form = document.getElementById('booking-form');
    if (!form) {
        console.error('Booking form not found');
        return false;
    }
    
    const requiredFields = [
        'customer_name', 'customer_phone', 'customer_email', 'license_number',
        'start_date', 'end_date', 'start_time', 'end_time', 'pickup_location'
    ];
    
    let isValid = true;
    
    // Clear previous error states
    form.querySelectorAll('.error-field').forEach(field => {
        field.classList.remove('error-field');
        field.style.borderColor = '';
    });
    
    requiredFields.forEach(fieldName => {
        const field = form.elements[fieldName];
        if (!field || !field.value.trim()) {
            isValid = false;
            if (field) {
                field.classList.add('error-field');
                field.style.borderColor = '#ef4444';
            }
        }
    });
    
    // Validate email format
    const email = form.elements['customer_email'];
    if (email && email.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            isValid = false;
            email.classList.add('error-field');
            email.style.borderColor = '#ef4444';
            showMessage("Please enter a valid email address.", "error");
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
            isValid = false;
            startDate.style.borderColor = '#ef4444';
            startTime.style.borderColor = '#ef4444';
            showMessage("Pickup date and time cannot be in the past.", "error");
            return false;
        }
        
        if (endDateTime <= startDateTime) {
            isValid = false;
            endDate.style.borderColor = '#ef4444';
            endTime.style.borderColor = '#ef4444';
            showMessage("Return date and time must be after pickup date and time.", "error");
            return false;
        }
        
        const diffMs = endDateTime - startDateTime;
        const totalHours = diffMs / (1000 * 60 * 60);
        
        if (totalHours < 8) {
            isValid = false;
            endDate.style.borderColor = '#ef4444';
            endTime.style.borderColor = '#ef4444';
            showMessage("Minimum rental period is 8 hours.", "error");
            return false;
        }
    }
    
    if (!isValid && !document.querySelector('.error-field')) {
        showMessage("Please fill in all required fields correctly.", "error");
    }
    
    return isValid;
}

// FIXED Form submission with comprehensive error handling
function submitBookingToServer(formData) {
    const submitButton = document.querySelector('#booking-form button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
    submitButton.disabled = true;

    console.log('=== SUBMITTING BOOKING DATA ===');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    fetch('save_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response body:', text);
                throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}...`);
            });
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error(`Expected JSON response but got: ${contentType}. Response: ${text.substring(0, 200)}...`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('=== SERVER RESPONSE ===');
        console.log(data);
        
        if (data.status === 'success') {
            const successMessage = `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-green-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Booking Successful!</h3>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg text-left space-y-2 text-sm">
                        <div><strong>Booking Reference:</strong> <span class="font-mono text-blue-600">${data.booking_reference || 'N/A'}</span></div>
                        <div><strong>Vehicle:</strong> ${data.vehicle || 'N/A'}</div>
                        <div><strong>Total Cost:</strong> <span class="text-green-600 font-semibold">₱${data.total_cost || 'N/A'}</span></div>
                        <div><strong>Duration:</strong> ${data.total_hours || 'N/A'} hours</div>
                        <div><strong>Pickup:</strong> ${data.pickup_date || 'N/A'} at ${data.pickup_time || 'N/A'}</div>
                        <div><strong>Return:</strong> ${data.return_date || 'N/A'} at ${data.return_time || 'N/A'}</div>
                    </div>
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            We will contact you within 24 hours to confirm your reservation.
                        </p>
                    </div>
                </div>
            `;
            
            showMessage(successMessage, "success");
            
            // Reset form and close modal
            document.getElementById('booking-form').reset();
            document.getElementById('cost-summary').classList.add('hidden');
            removeImage();
            closeModal101('booking-modal');
            
            // Refresh vehicle availability
            setTimeout(() => {
                if (typeof loadVehicles === 'function') {
                    console.log('Refreshing vehicle list...');
                    loadVehicles();
                }
            }, 1000);
            
        } else {
            const errorMessage = `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Booking Failed</h3>
                    <p class="text-red-600">${data.message || 'An unknown error occurred while processing your booking.'}</p>
                    ${data.conflict_booking ? `<p class="text-sm text-gray-600 mt-2">Conflicting booking: ${data.conflict_booking}</p>` : ''}
                </div>
            `;
            showMessage(errorMessage, "error");
        }
    })
    .catch(error => {
        console.error('=== BOOKING SUBMISSION ERROR ===');
        console.error('Error:', error);
        console.error('Stack:', error.stack);
        
        let errorMessage = `
            <div class="text-center">
                <i class="fas fa-wifi text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Connection Error</h3>
                <p class="text-red-600 mb-2">Unable to submit booking. Please check your connection and try again.</p>
                <p class="text-xs text-gray-500">Error: ${error.message}</p>
            </div>
        `;
        
        // Check for specific error types
        if (error.message.includes('HTTP 404')) {
            errorMessage = `
                <div class="text-center">
                    <i class="fas fa-file-times text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Server Error</h3>
                    <p class="text-red-600 mb-2">The booking submission file (save_booking.php) was not found.</p>
                    <p class="text-xs text-gray-500">Please check that save_booking.php exists in the correct directory.</p>
                </div>
            `;
        } else if (error.message.includes('HTTP 500')) {
            errorMessage = `
                <div class="text-center">
                    <i class="fas fa-server text-red-600" style="font-size: 4rem; margin-bottom: 16px; display: block;"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Database Error</h3>
                    <p class="text-red-600 mb-2">There was a server error processing your booking.</p>
                    <p class="text-xs text-gray-500">Please check the server logs for more details.</p>
                </div>
            `;
        }
        
        showMessage(errorMessage, "error");
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Image upload functions
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            showMessage('Please upload an image file (JPG, PNG, etc.)', 'error');
            input.value = '';
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showMessage('File size must be less than 10MB', 'error');
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
    document.getElementById('upload-image').click();
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
function saveBookingModal() {
    const form = document.getElementById('booking-form');
    if (!form) {
        console.error('Booking form not found');
        return;
    }
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    bookingData.modalBooking = data;
    showMessage("Your booking details have been saved!", "success");
    console.log("Modal booking saved:", data);
}

function loadBookingModal() {
    const savedData = bookingData.modalBooking;
    if (Object.keys(savedData).length > 0) {
        const form = document.getElementById('booking-form');
        if (!form) return;
        
        for (const [key, value] of Object.entries(savedData)) {
            const element = form.elements[key];
            if (element) {
                element.value = value;
            }
        }
        
        console.log("Modal booking data loaded:", savedData);
    }
}

// Load vehicles function
function loadVehicles() {
    console.log('Loading vehicles from database...');
    
    const loadingElement = document.getElementById('vehicles-loading');
    const resultsGrid = document.getElementById('results-grid');
    
    if (loadingElement) loadingElement.style.display = 'block';
    if (resultsGrid) resultsGrid.style.display = 'none';
    
    fetch('get_vehicles.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Vehicles loaded:', data);
            vehiclesData = data.vehicles || [];
            vehiclePricing = data.pricing || {};
            
            updateVehiclesDisplay(vehiclesData);
            
            if (loadingElement) loadingElement.style.display = 'none';
            if (resultsGrid) resultsGrid.style.display = 'grid';
        })
        .catch(error => {
            console.error('Error loading vehicles:', error);
            if (loadingElement) {
                loadingElement.innerHTML = `
                    <div class="text-center text-red-600">
                        <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                        <p>Error loading vehicles. Please refresh the page.</p>
                    </div>
                `;
            }
        });
}

function updateVehiclesDisplay(vehicles) {
    console.log('Vehicles display updated with', vehicles.length, 'vehicles');
}

// FIXED initialization function
function initializeBookingModal() {
    console.log('Initializing booking modal...');
    
    // Set minimum dates
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput) startDateInput.min = today;
    if (endDateInput) endDateInput.min = today;

    // FIXED form submission handler
    const modalForm = document.getElementById('booking-form');
    if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('=== FORM SUBMITTED ===');
            
            if (validateForm()) {
                console.log('Form validation passed');
                const formData = new FormData(modalForm);
                
                // Debug form data
                console.log('=== FORM DATA BEING SENT ===');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
                
                submitBookingToServer(formData);
            } else {
                console.log('Form validation failed');
            }
        });
    } else {
        console.error('Modal form not found! Available forms:', 
            Array.from(document.querySelectorAll('form')).map(f => f.id || f.className));
    }

    // Add event listeners for cost calculation
    ['start-date', 'end-date', 'start-time', 'end-time', 'pickup-location-modal', 'return-location-modal'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', calculateRentalCost);
        }
    });

    // Auto-update return location styling
    const pickupLocation = document.getElementById('pickup-location-modal');
    const returnLocation = document.getElementById('return-location-modal');
    
    if (pickupLocation && returnLocation) {
        pickupLocation.addEventListener('change', function() {
            if (!returnLocation.value) {
                returnLocation.style.backgroundColor = '#f3f4f6';
                returnLocation.title = 'Return location will be same as pickup location';
            }
            calculateRentalCost();
        });
        
        returnLocation.addEventListener('change', function() {
            if (this.value) {
                this.style.backgroundColor = '';
                this.title = '';
            } else {
                this.style.backgroundColor = '#f3f4f6';
                this.title = 'Return location will be same as pickup location';
            }
            calculateRentalCost();
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM LOADED, INITIALIZING ===');
    
    initializeBookingModal();
    
    // Load vehicles if function exists
    if (typeof loadVehicles === 'function') {
        loadVehicles();
    }
    
    console.log('=== INITIALIZATION COMPLETE ===');
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const bookingModal = document.getElementById('booking-modal');
    const messageModal = document.getElementById('message-modal');
    
    if (event.target === bookingModal) {
        closeModal101('booking-modal');
    }
    
    if (event.target === messageModal) {
        closeModal101('message-modal');
    }
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount);
}

function formatDateTime(dateString, timeString) {
    const date = new Date(`${dateString} ${timeString}`);
    return date.toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
}