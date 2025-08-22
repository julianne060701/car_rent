let bookingData = {
    quickBooking: {},
    modalBooking: {}
};

// Store vehicles data globally
let vehiclesData = [];

// Vehicle pricing will be loaded dynamically from database
let vehiclePricing = {};

// Vehicle type mappings for features and descriptions
const vehicleTypeConfig = {
    'Vios': {
        passengers: 4,
        transmission: 'Automatic',
        feature3: 'Fuel Efficient',
        description: 'Perfect for city driving and business trips',
        color: 'blue',
        iconFeature3: 'gas-pump' 
    },
    'Innova': {
        passengers: 7,
        transmission: 'Manual',
        feature3: 'Large Cargo',
        description: 'Spacious family vehicle for group travels',
        color: 'yellow',
        iconFeature3: 'suitcase'
    },
    'City': {
        passengers: 4,
        transmission: 'CVT',
        feature3: 'Eco-Friendly',
        description: 'Reliable and fuel efficient sedan',
        color: 'blue',
        iconFeature3: 'leaf'
    },
    'Xpander': {
        passengers: 7,
        transmission: 'Automatic',
        feature3: 'Safety Features',
        description: 'Modern MPV with stylish design',
        color: 'yellow',
        iconFeature3: 'shield-alt'
    },
    // Default configuration for unknown vehicle types
    'default': {
        passengers: 4,
        transmission: 'Automatic',
        feature3: 'Modern Features',
        description: 'Quality vehicle for your travel needs',
        color: 'blue',
        iconFeature3: 'car'
    }
};

// Function to get vehicle configuration
function getVehicleConfig(carName) {
    // Try to match with existing configurations
    for (const [key, config] of Object.entries(vehicleTypeConfig)) {
        if (key !== 'default' && carName.toLowerCase().includes(key.toLowerCase())) {
            return config;
        }
    }
    // Return default if no match found
    return vehicleTypeConfig.default;
}

// Function to load vehicles from database
async function loadVehicles() {
    try {
        console.log('Loading vehicles from database...');

        const response = await fetch('get_vehicles.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.status === 'success') {
            vehiclesData = data.data;

            // Update vehiclePricing object with database data
            vehiclePricing = {};
            vehiclesData.forEach(vehicle => {
                vehiclePricing[vehicle.car_name] = {
                    dailyRate: vehicle.rate_per_day,
                   
                };
            });

            console.log('Vehicles loaded:', vehiclesData);
            console.log('Pricing updated:', vehiclePricing);

            // Render vehicles in the DOM
            renderVehicles();

            return vehiclesData;
        } else {
            throw new Error(data.message || 'Failed to load vehicles');
        }
    } catch (error) {
        console.error('Error loading vehicles:', error);
        showMessage('Unable to load vehicles. Please refresh the page and try again.', 'error');

        // Show fallback message in vehicles section
        const vehiclesContainer = document.querySelector('#vehicles .grid');
        const loadingElement = document.getElementById('vehicles-loading');

        // Hide loading spinner
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }

        if (vehiclesContainer) {
            vehiclesContainer.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Unable to Load Vehicles</h3>
                    <p class="text-gray-500 mb-4">Please refresh the page or contact us for assistance.</p>
                    <button onclick="loadVehicles()" class="btn btn-primary">
                        <i class="fas fa-refresh mr-2"></i>Try Again
                    </button>
                </div>
            `;
        }

        return [];
    }
}

// Function to render vehicles in the DOM
// Function to render vehicles in the DOM
function renderVehicles() {
    const vehiclesContainer = document.querySelector('#vehicles-grid');
    const loadingElement = document.getElementById('vehicles-loading');
    
    // Hide loading spinner in all cases
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }

    if (!vehiclesContainer || !vehiclesData || vehiclesData.length === 0) {
        console.error('No vehicles container found or no vehicles data');
        if (vehiclesContainer) {
            vehiclesContainer.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-car text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">No Vehicles Available</h3>
                    <p class="text-gray-500 mb-4">Please check back later or contact us for assistance.</p>
                    <button onclick="loadVehicles()" class="btn btn-primary">
                        <i class="fas fa-refresh mr-2"></i>Refresh
                    </button>
                </div>
            `;
        }
        return;
    }

    console.log('Rendering vehicles...');

    let vehiclesHtml = '';
    
   vehiclesData.forEach((vehicle) => {
        const config = getVehicleConfig(vehicle.car_name);
        const colorClass = config.color === 'yellow' ? 'yellow' : 'blue';
        const bgColor = colorClass === 'yellow' ? '#f59e0b' : '#3b82f6';

        // Format pricing
        const dailyRate = parseFloat(vehicle.rate_per_day);


        
        // ✅ Use DB image if available, otherwise fallback to placeholder
        const imageUrl = vehicle.car_image && vehicle.car_image.trim() !== ''
        ? `uploads/cars/${vehicle.car_image}`
        : 'assets/images/default-car.jpg';

        // Determine availability based on status field (1 = available, 0 = unavailable)
        const isAvailable = vehicle.status == 1;
        const availabilityClass = isAvailable ? 'text-green-600' : 'text-red-500';
        const availabilityIcon = isAvailable ? 'check-circle' : 'times-circle';
        const availabilityText = isAvailable ? 'Available' : 'Currently Booked';

        vehiclesHtml += `
            <div class="vehicle-card bg-white rounded-xl shadow-lg overflow-hidden ${!isAvailable ? 'opacity-75' : ''}" data-vehicle-id="${vehicle.car_id}">
                <div class="relative">
                  <img src="${imageUrl}" alt="${vehicle.car_name}" class="w-full h-48 object-cover">

                    <div class="absolute top-2 right-2 bg-white rounded-full px-2 py-1 text-xs font-medium ${availabilityClass}">
                        <i class="fas fa-${availabilityIcon} mr-1"></i>
                        ${availabilityText}
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-semibold text-gray-900">${vehicle.car_name}</h3>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">${vehicle.brand}</span>
                    </div>
                    <p class="text-gray-600 mb-4">${config.description}</p>
                    
                    <div class="features grid grid-cols-3 gap-2 text-sm text-gray-500 mb-4">
                        <span class="flex items-center">
                            <i class="fas fa-users mr-2 text-${colorClass}-500"></i> 
                            ${config.passengers} Passengers
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-cog mr-2 text-${colorClass}-500"></i> 
                            ${config.transmission}
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-${config.iconFeature3} mr-2 text-${colorClass}-500"></i> 
                            ${config.feature3}
                        </span>
                    </div>
                    
                    <div class="pricing mb-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-2xl font-bold text-gray-900">₱${dailyRate.toLocaleString()}/day</div>
                               
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-400">Plate:</div>
                                <div class="text-sm font-mono font-semibold text-gray-600">${vehicle.plate_number}</div>
                            </div>
                        </div>
                    </div>
                    
                    <button 
                        class="btn w-full ${isAvailable ? 'btn-primary' : 'bg-gray-400 text-white cursor-not-allowed'}" 
                        data-vehicle="${vehicle.car_name}"
                        onclick="${isAvailable ? `openBookingModalWithVehicle('${vehicle.car_name}')` : 'showUnavailableMessage()'}"                        onclick="${isAvailable ? `openBookingModalWithVehicle('${vehicle.car_name}')` : 'showUnavailableMessage()'}"
                        ${!isAvailable ? 'disabled' : ''}>
                        ${isAvailable ? 'Book Now' : 'Currently Unavailable'}
                    </button>
                </div>
            </div>
        `;
    });

    vehiclesContainer.innerHTML = vehiclesHtml;
    console.log('Vehicles rendered successfully');
}
// Function to show unavailable message
function showUnavailableMessage() {
    showMessage('This vehicle is currently booked. Please choose another vehicle or try different dates.', 'error');
}

// Image upload functionality
function initializeImageUpload() {
    const uploadContainer = document.getElementById('imageUploadContainer');
    const fileInput = document.getElementById('upload-image');
    const previewDiv = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');

    if (!uploadContainer || !fileInput || !previewDiv || !previewImage) return;

    // Click to upload
    uploadContainer.addEventListener('click', () => {
        fileInput.click();
    });

    // Drag and drop
    uploadContainer.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadContainer.classList.add('dragover');
    });

    uploadContainer.addEventListener('dragleave', () => {
        uploadContainer.classList.remove('dragover');
    });

    uploadContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadContainer.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // File input change
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            handleFile(e.target.files[0]);
        }
    });

    function handleFile(file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            showMessage('Please upload an image file (JPG, PNG, etc.)', 'error');
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showMessage('File size must be less than 10MB', 'error');
            return;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            previewDiv.style.display = 'block';
            uploadContainer.style.display = 'none';
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
    
    fileInput.value = '';
    previewDiv.style.display = 'none';
    uploadContainer.style.display = 'block';
}

// Modal functions
function openBookingModal() {
    const modal = document.getElementById('booking-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    loadBookingModal();
}

function openBookingModalWithVehicle(vehicleName) {
    // Check if vehicle exists and is available
    const vehicle = vehiclesData.find(v => v.car_name === vehicleName);
    if (!vehicle) {
        showMessage('Vehicle not found. Please refresh the page and try again.', 'error');
        return;
    }
    
    if (!vehicle.is_available) {
        showUnavailableMessage();
        return;
    }
    
    
    document.getElementById('selected-vehicle').value = vehicleName;
    openBookingModal();
    setTimeout(calculateRentalCost, 100);
}

function closeBookingModal() {
    document.getElementById('booking-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function closeMessageModal() {
    document.getElementById('message-modal').style.display = 'none';
}

// Show message function
function showMessage(message, type = 'info') {
    const messageModal = document.getElementById('message-modal');
    const messageContent = document.getElementById('message-content');
    
    messageContent.className = 'p-4 text-center';
    
    if (type === 'success') {
        messageContent.classList.add('text-green-600');
    } else if (type === 'error') {
        messageContent.classList.add('text-red-600');
    } else {
        messageContent.classList.add('text-gray-800');
    }
    
    // Use innerHTML instead of textContent to support HTML formatting
    messageContent.innerHTML = `
        <div class="mb-4">
            ${message}
        </div>
        <button onclick="closeMessageModal()" class="btn btn-primary">OK</button>
    `;
    
    messageModal.style.display = 'flex';
    if (type === 'success') {
        setTimeout(() => {
            if (messageModal.style.display === 'flex') {
                closeMessageModal();
            }
        }, 5000);
    }
}

function calculateRentalCost() {
    const selectedVehicle = document.getElementById('selected-vehicle').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    const startTime = document.getElementById('start-time').value;
    const endTime = document.getElementById('end-time').value;

    if (!selectedVehicle || !startDate || !endDate || !startTime || !endTime) {
        document.getElementById('cost-summary').classList.add('hidden');
        return;
    }

    const vehicle = vehiclePricing[selectedVehicle];
    if (!vehicle) {
        document.getElementById('cost-summary').classList.add('hidden');
        return;
    }

    const startDateTime = new Date(`${startDate} ${startTime}`);
    const endDateTime = new Date(`${endDate} ${endTime}`);
    const diffMs = endDateTime - startDateTime;
    const totalHours = Math.max(diffMs / (1000 * 60 * 60), 8);

    if (totalHours <= 0) {
        document.getElementById('cost-summary').classList.add('hidden');
        return;
    }

    let totalCost;
    let rateText;

    if (totalHours >= 24) {
        const days = Math.ceil(totalHours / 24);
        totalCost = vehicle.dailyRate * days;
        rateText = `₱${vehicle.dailyRate.toLocaleString()}/day × ${days} day${days > 1 ? 's' : ''}`;
    } else {
        totalCost = vehicle.hourlyRate * totalHours;
        rateText = `₱${vehicle.hourlyRate}/hour × ${Math.ceil(totalHours)} hours`;
    }

    document.getElementById('summary-vehicle').textContent = selectedVehicle;
    document.getElementById('summary-duration').textContent = `${Math.ceil(totalHours)} hours`;
    document.getElementById('summary-rate').textContent = rateText;
    document.getElementById('summary-total').textContent = `₱${totalCost.toLocaleString()}`;
    
    document.getElementById('cost-summary').classList.remove('hidden');
}

function saveBookingModal() {
    const form = document.getElementById('booking-modal-form');
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
        const form = document.getElementById('booking-modal-form');
        
        for (const [key, value] of Object.entries(savedData)) {
            const element = form.elements[key];
            if (element) {
                element.value = value;
            }
        }
        
        console.log("Modal booking data loaded:", savedData);
    }
}

// Form validation
function validateForm() {
    const form = document.getElementById('booking-modal-form');
    const requiredFields = [
        'customer_name', 'customer_phone', 'customer_email', 'license_number',
        'start_date', 'end_date', 'start_time', 'end_time', 'pickup_location'
    ];
    
    let isValid = true;
    
    // Clear previous error states
    form.querySelectorAll('.error-field').forEach(field => {
        field.classList.remove('error-field');
    });
    
    requiredFields.forEach(fieldName => {
        const field = form.elements[fieldName];
        if (!field || !field.value.trim()) {
            isValid = false;
            if (field) field.classList.add('error-field');
        }
    });
    
    // Validate email format
    const email = form.elements['customer_email'];
    if (email && email.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email.value)) {
            isValid = false;
            email.classList.add('error-field');
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
            startDate.classList.add('error-field');
            startTime.classList.add('error-field');
            showMessage("Pickup date and time cannot be in the past.", "error");
            return false;
        }
        
        if (endDateTime <= startDateTime) {
            isValid = false;
            endDate.classList.add('error-field');
            endTime.classList.add('error-field');
            showMessage("Return date and time must be after pickup date and time.", "error");
            return false;
        }
        
        const diffMs = endDateTime - startDateTime;
        const totalHours = diffMs / (1000 * 60 * 60);
        
        if (totalHours < 8) {
            isValid = false;
            endDate.classList.add('error-field');
            endTime.classList.add('error-field');
            showMessage("Minimum rental period is 8 hours.", "error");
            return false;
        }
    }
    
    if (!isValid) {
        showMessage("Please fill in all required fields correctly.", "error");
    }
    
    return isValid;
}

// Form submission with real AJAX call
function submitBookingToServer(formData) {
    const submitButton = document.querySelector('#booking-modal-form button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
    submitButton.disabled = true;

    // Log what we're sending for debugging
    console.log('Submitting booking data...');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    // Make AJAX request to save_booking.php
    fetch('save_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        // Check if response is OK
        if (!response.ok) {
            return response.text().then(text => {
                console.error('Error response body:', text);
                throw new Error(`HTTP error! status: ${response.status} - ${text}`);
            });
        }
        
        // Parse JSON response
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        
        if (data.status === 'success') {
            // Create success message with proper HTML formatting
            const successMessage = `
                <div class="text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-2"></i>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Booking Successful!</h3>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div><strong>Booking Reference:</strong> <span class="font-mono text-blue-600">${data.booking_reference}</span></div>
                        <div><strong>Vehicle:</strong> ${data.vehicle}</div>
                        <div><strong>Total Cost:</strong> <span class="text-green-600 font-semibold">₱${data.total_cost}</span></div>
                        <div><strong>Duration:</strong> ${data.total_hours} hours (${data.rental_type})</div>
                        <div><strong>Pickup Date:</strong> ${data.pickup_date}</div>
                        <div><strong>Return Date:</strong> ${data.return_date}</div>
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
            document.getElementById('booking-modal-form').reset();
            document.getElementById('cost-summary').classList.add('hidden');
            removeImage();
            closeBookingModal();
            
            // Refresh vehicle availability after successful booking
            setTimeout(() => {
                console.log('Refreshing vehicle list...');
                loadVehicles();
            }, 1000);
            
        } else {
            // Show error message from server
            const errorMessage = `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Booking Failed</h3>
                    <p class="text-red-600">${data.message || 'An error occurred while processing your booking.'}</p>
                </div>
            `;
            showMessage(errorMessage, "error");
        }
    })
    .catch(error => {
        console.error('Error submitting booking:', error);
        
        const networkErrorMessage = `
            <div class="text-center">
                <i class="fas fa-wifi text-4xl text-red-500 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Connection Error</h3>
                <p class="text-red-600 mb-2">Unable to submit booking. Please check your connection and try again.</p>
                <p class="text-xs text-gray-500">Error: ${error.message}</p>
            </div>
        `;
        showMessage(networkErrorMessage, "error");
    })
    .finally(() => {
        // Restore button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
    // Hide loading spinner initially and show it when loading vehicles
    const loadingElement = document.getElementById('vehicles-loading');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
    
    // Load vehicles from database
    loadVehicles();
    
    initializeImageUpload();

    // Set minimum dates to today
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput) startDateInput.min = today;
    if (endDateInput) endDateInput.min = today;

    // Form submission handler
    const modalForm = document.getElementById('booking-modal-form');
    if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            if (validateForm()) {
                const formData = new FormData(modalForm);
                
                // Log form data for debugging
                console.log('Form data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(key, value);
                }
                
                submitBookingToServer(formData);
            }
        });
    } else {
        console.error('Modal form not found!');
    }

    // Add event listeners for cost calculation
    ['start-date', 'end-date', 'start-time', 'end-time'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', calculateRentalCost);
        }
    });

    // Auto-update return location to match pickup if not specified
    const pickupLocation = document.getElementById('pickup-location-modal');
    const returnLocation = document.getElementById('return-location-modal');
    
    if (pickupLocation && returnLocation) {
        pickupLocation.addEventListener('change', function() {
            if (!returnLocation.value) {
                returnLocation.style.backgroundColor = '#f3f4f6';
                returnLocation.title = 'Return location will be same as pickup location';
            }
        });
        
        returnLocation.addEventListener('change', function() {
            if (this.value) {
                this.style.backgroundColor = '';
                this.title = '';
            }
        });
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const bookingModal = document.getElementById('booking-modal');
    const messageModal = document.getElementById('message-modal');
    
    if (event.target === bookingModal) {
        closeBookingModal();
    }
    
    if (event.target === messageModal) {
        closeMessageModal();
    }
};

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Refresh vehicles every 5 minutes to keep availability updated
setInterval(() => {
    console.log('Auto-refreshing vehicle availability...');
    loadVehicles();
}, 5 * 60 * 1000); // 5 minutes
function handleQuickBookingForm(event) {
    event.preventDefault();
    
    // Get form values
    const pickupLocation = document.getElementById('pickup-location').value;
    const pickupDate = document.getElementById('pickup-date').value;
    const pickupTime = document.getElementById('pickup-time').value;
    const returnDate = document.getElementById('return-date').value;
    const returnTime = document.getElementById('return-time').value;
    
    // Validate dates
   if (!pickupDate || !returnDate || !pickupTime || !returnTime) {
        showMessage('Please fill in all date and time fields', 'error');
        return;
    }
    
    const startDateTime = new Date(`${pickupDate} ${pickupTime}`);
    const endDateTime = new Date(`${returnDate} ${returnTime}`);
    const now = new Date();
    
    if (startDateTime < now) {
        showMessage('Pickup date and time cannot be in the past', 'error');
        return;
    }
    
    if (endDateTime <= startDateTime) {
        showMessage('Return date and time must be after pickup', 'error');
        return;
    }
    
    // Calculate duration in hours
    const durationHours = (endDateTime - startDateTime) / (1000 * 60 * 60);
    if (durationHours < 8) {
        showMessage('Minimum rental period is 8 hours', 'error');
        return;
    }
    
    // Store quick booking data
    bookingData.quickBooking = {
        pickupLocation,
        pickupDate,
        pickupTime,
        returnDate,
        returnTime
    };
    
    // Load vehicles with availability for these dates
    loadVehiclesWithAvailability(pickupDate, returnDate, pickupTime, returnTime);
}

async function loadVehiclesWithAvailability(pickupDate, returnDate, pickupTime, returnTime) {
    try {
        console.log('Loading vehicles with availability check...');
        const loadingElement = document.getElementById('vehicles-loading');
        if (loadingElement) {
            loadingElement.style.display = 'block';
        }
        // Construct query parameters
        const params = new URLSearchParams();
        params.append('start_datetime', `${pickupDate} ${pickupTime}`);
        params.append('end_datetime', `${returnDate} ${returnTime}`);
        
        const response = await fetch(`get_vehicles.php?${params.toString()}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.status === 'success') {
            vehiclesData = data.data;
            
            // Update vehiclePricing
            vehiclePricing = {};
            vehiclesData.forEach(vehicle => {
                vehiclePricing[vehicle.car_name] = {
                    dailyRate: vehicle.rate_per_day,
                    hourlyRate: vehicle.hourly_rate
                };
            });
            
            console.log('Vehicles loaded with availability:', vehiclesData);
            
            // Render vehicles in the DOM
            renderVehicles();
            
            // Scroll to vehicles section
            document.getElementById('vehicles').scrollIntoView({
                behavior: 'smooth'
            });
            
            return vehiclesData;
        } else {
            throw new Error(data.message || 'Failed to load vehicles');
        }
    } catch (error) {
        console.error('Error loading vehicles with availability:', error);
        showMessage('Unable to check vehicle availability. Please try again.', 'error');
        return [];
    }finally {
        const loadingElement = document.getElementById('vehicles-loading');
        if (loadingElement) {
            loadingElement.style.display = 'none';
        }
    }
}


function initializeQuickBookingForm() {
    const quickBookingForm = document.getElementById('quick-booking-form');
    if (quickBookingForm) {
        quickBookingForm.addEventListener('submit', handleQuickBookingForm);
    }
    
    // Set minimum dates to today
    const today = new Date().toISOString().split('T')[0];
    const pickupDate = document.getElementById('pickup-date');
    const returnDate = document.getElementById('return-date');
    
    if (pickupDate) pickupDate.min = today;
    if (returnDate) returnDate.min = today;
}
    function initializeQuickBookingForm() {
        const quickBookingForm = document.getElementById('quick-booking-form');
        if (quickBookingForm) {
            quickBookingForm.addEventListener('submit', handleQuickBookingForm);
        }
        
        // Set minimum dates to today
        const today = new Date().toISOString().split('T')[0];
        const pickupDate = document.getElementById('pickup-date');
        const returnDate = document.getElementById('return-date');
        
        if (pickupDate) pickupDate.min = today;
        if (returnDate) returnDate.min = today;
    }

// Update your DOMContentLoaded event listener
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing...');
        
        // Initialize quick booking form
        initializeQuickBookingForm();
        
        // Load vehicles initially
        loadVehicles();
        
        // Rest of your initialization code...
        initializeImageUpload();
        // ...
    });
