let bookingData = {
    quickBooking: {},
    modalBooking: {}
};

// Store vehicles data globally
let vehiclesData = [];

// Vehicle pricing will be loaded dynamically from database
let vehiclePricing = {};

// Location pricing configuration
const locationPricing = {
    'store': 0,
    'gensan-airport': 500,
    'downtown-gensan': 300,
    'kcc-mall': 500,
    'robinsons-place': 400,
    'sm-city-gensan': 400
};

// Vehicle type mappings for features and descriptions
const vehicleTypeConfig = {
    'Vios': {
    },
    'Innova': {
    },
    'City': {
    },
    'Xpander': {
    },
    // Default configuration for unknown vehicle types
    'default': {
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

// Function to calculate location charges
function calculateLocationCharges(pickupLocation, returnLocation) {
    let pickupCharge = locationPricing[pickupLocation] || 0;
    let returnCharge = locationPricing[returnLocation] || 0;
    
    // Only charge for return location if it's different from pickup AND not empty
    if (returnLocation && returnLocation.toLowerCase() !== "store") {
        returnCharge = locationPricing[returnLocation] || 0;
    }
    
    return {
        pickupCharge: pickupCharge,
        returnCharge: returnCharge,
        totalLocationCharge: pickupCharge + returnCharge
    };
}
function initializeLocationHandling() {
    const pickupLocation = document.getElementById('pickup-location-modal');
    const returnLocation = document.getElementById('return-location-modal');
    
    if (pickupLocation && returnLocation) {
        pickupLocation.addEventListener('change', function() {
            // Only auto-fill if return location is empty
            if (!returnLocation.value || returnLocation.value === pickupLocation.value) {
                returnLocation.value = ''; // Clear it to show it's optional
                returnLocation.style.backgroundColor = '#f3f4f6';
                returnLocation.title = 'Leave empty to return at pickup location (no extra charge)';
            }
            calculateRentalCost();
        });
        
        returnLocation.addEventListener('change', function() {
            if (this.value) {
                this.style.backgroundColor = '';
                this.title = '';
            } else {
                this.style.backgroundColor = '#f3f4f6';
                this.title = 'Leave empty to return at pickup location (no extra charge)';
            }
            calculateRentalCost();
        });
        
        // Add placeholder option to return location select
        if (returnLocation.options.length > 0 && returnLocation.options[0].value !== '') {
            const placeholderOption = new Option('Same as pickup location (Free)', '');
            returnLocation.insertBefore(placeholderOption, returnLocation.firstChild);
            returnLocation.value = ''; // Set default to empty
        }
    }
}

// Function to get location display name
function getLocationDisplayName(locationKey) {
    const locationNames = {
        'store': 'Store (Main Office)',
        'gensan-airport': 'GenSan Airport',
        'downtown-gensan': 'Downtown GenSan',
        'kcc-mall': 'KCC Mall',
        'robinsons-place': "Robinson's Place GenSan",
        'sm-city-gensan': 'SM City General Santos'
    };
    
    return locationNames[locationKey] || locationKey;
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
                    hourlyRate: vehicle.hourly_rate,
                    rate6h: parseFloat(vehicle.rate_6h) || 0,
                    rate8h: parseFloat(vehicle.rate_8h) || 0,
                    rate12h: parseFloat(vehicle.rate_12h) || 0,
                    rate24h: parseFloat(vehicle.rate_per_day) || 0
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
function renderVehicles() {
    const vehiclesContainer = document.querySelector('#vehicles-grid');
    const loadingElement = document.getElementById('vehicles-loading');
    
    // Hide loading spinner
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
    
    console.log('Rendering vehicles...', vehiclesData);
    
    let vehiclesHtml = '';
    
    vehiclesData.forEach((vehicle) => {
        const config = getVehicleConfig(vehicle.car_name);
        const colorClass = config.color === 'yellow' ? 'yellow' : 'blue';
        const bgColor = colorClass === 'yellow' ? '#f59e0b' : '#3b82f6';
        
        // Format pricing - use the actual values from database
        let dailyRate = parseFloat(vehicle.rate_per_day) || parseFloat(vehicle.rate_24h) || 2000;
        let hourlyRate = parseFloat(vehicle.hourly_rate) || (parseFloat(vehicle.rate_8h) / 8) || 250;
        
        // If we have specific hour rates, use them
        const rate6h = parseFloat(vehicle.rate_6h) || 0;
        const rate8h = parseFloat(vehicle.rate_8h) || 0;
        const rate12h = parseFloat(vehicle.rate_12h) || 0;
        const rate24h = parseFloat(vehicle.rate_24h) || 0;
        
        // Use 24h rate as daily rate if available
        if (rate24h > 0) {
            dailyRate = rate24h;
        }
        
        // Calculate hourly rate from available data
        if (rate8h > 0) {
            hourlyRate = rate8h / 8;
        } else if (rate6h > 0) {
            hourlyRate = rate6h / 6;
        } else if (rate12h > 0) {
            hourlyRate = rate12h / 12;
        }
        
        console.log(`Vehicle: ${vehicle.car_name}, Daily: ${dailyRate}, Hourly: ${hourlyRate.toFixed(2)}`);
        
        // Use actual image if available, otherwise placeholder
        let imageUrl;
        if (vehicle.image_url && vehicle.image_url !== 'uploads/') {
            imageUrl = vehicle.image_url;
        } else {
            imageUrl = `https://placehold.co/400x250/${bgColor.replace('#', '')}/ffffff?text=${encodeURIComponent(vehicle.car_name)}`;
        }
        
        // Status mapping: 3 = Booked, 2 = Pending, 1 = Available, 0 = Unavailable
        const isAvailable = vehicle.status === 1 && vehicle.is_available === 1;
        let statusClass, statusIcon, statusText;
        
        switch (vehicle.status) {
            case 1:
                statusClass = 'text-green-600';
                statusIcon = 'check-circle';
                statusText = 'Available';
                break;
            case 2:
                statusClass = 'text-yellow-600';
                statusIcon = 'clock';
                statusText = 'Pending';
                break;
            case 3:
                statusClass = 'text-red-500';
                statusIcon = 'times-circle';
                statusText = 'Booked';
                break;
            case 0:
            default:
                statusClass = 'text-gray-500';
                statusIcon = 'ban';
                statusText = 'Unavailable';
                break;
        }
        
        vehiclesHtml += `
            <div class="vehicle-card bg-white rounded-xl shadow-lg overflow-hidden ${!isAvailable ? 'opacity-75' : ''}" data-vehicle-id="${vehicle.car_id}">
                <div class="relative">
                    <img src="${imageUrl}" alt="${vehicle.car_name}" class="w-full h-48 object-cover" onerror="this.src='https://placehold.co/400x250/${bgColor.replace('#', '')}/ffffff?text=${encodeURIComponent(vehicle.car_name)}'">
                    <div class="absolute top-2 right-2 bg-white rounded-full px-2 py-1 text-xs font-medium ${statusClass}">
                        <i class="fas fa-${statusIcon} mr-1"></i>
                        ${statusText}
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
                                <div class="text-2xl font-bold text-gray-900">₱${Math.round(dailyRate).toLocaleString()}/day</div>
                                <div class="text-sm text-gray-500">₱${Math.round(hourlyRate).toLocaleString()}/hour</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-400">Plate:</div>
                                <div class="text-sm font-mono font-semibold text-gray-600">${vehicle.plate_number}</div>
                            </div>
                        </div>
                        
                        ${(rate6h > 0 || rate8h > 0 || rate12h > 0) ? `
                        <div class="mt-2 text-xs text-gray-500">
                            ${rate8h > 0 ? `8hrs: ₱${rate8h.toLocaleString()}` : ''}
                            ${rate12h > 0 ? `${rate8h > 0 ? ' • ' : ''}12hrs: ₱${rate12h.toLocaleString()}` : ''}
                        </div>
                        ` : ''}
                    </div>
                    
                    <button 
                        class="btn w-full ${isAvailable ? 'btn-primary' : 'bg-gray-400 text-white cursor-not-allowed'}" 
                        data-vehicle="${vehicle.car_name}"
                        onclick="${isAvailable ? `openBookingModalWithVehicle('${vehicle.car_name}')` : 'showUnavailableMessage()'}"
                        ${!isAvailable ? 'disabled' : ''}>
                        ${isAvailable ? 'Book Now' : statusText}
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
    const pickupLocation = document.getElementById('pickup-location-modal').value;
    const returnLocation = document.getElementById('return-location-modal').value;

    console.log('Calculating cost for:', {
        selectedVehicle, startDate, endDate, startTime, endTime, 
        pickupLocation, returnLocation: returnLocation || 'same as pickup'
    });

    if (!selectedVehicle || !startDate || !endDate || !startTime || !endTime || !pickupLocation) {
        document.getElementById('cost-summary').classList.add('hidden');
        return;
    }

    // Find the vehicle data
    const vehicleData = vehiclesData.find(v => v.car_name === selectedVehicle);
    if (!vehicleData) {
        console.error('Vehicle data not found for:', selectedVehicle);
        document.getElementById('cost-summary').classList.add('hidden');
        return;
    }

    // Parse dates and times
    const startDateTime = new Date(`${startDate}T${startTime}`);
    const endDateTime = new Date(`${endDate}T${endTime}`);
    
    if (isNaN(startDateTime.getTime()) || isNaN(endDateTime.getTime())) {
        console.error('Invalid date/time values');
        document.getElementById('cost-summary').classList.add('hidden');
        return;
    }

    const diffMs = endDateTime - startDateTime;
    const totalHours = Math.max(diffMs / (1000 * 60 * 60), 8); // Minimum 8 hours

    if (totalHours <= 0) {
        console.error('Invalid duration:', totalHours);
        document.getElementById('cost-summary').classList.add('hidden');
        return;
    }

    // FIXED: Calculate location charges with proper return location handling
    // If return location is empty, treat it as same as pickup (no extra charge)
    const effectiveReturnLocation = returnLocation || pickupLocation;
    const locationCharges = calculateLocationCharges(pickupLocation, effectiveReturnLocation);
    
    console.log('Location charges calculated:', {
        pickup: pickupLocation,
        return: effectiveReturnLocation,
        charges: locationCharges
    });

    // Get available rates
    const rate6h = parseFloat(vehicleData.rate_6h) || 0;
    const rate8h = parseFloat(vehicleData.rate_8h) || 0;
    const rate12h = parseFloat(vehicleData.rate_12h) || 0;
    const rate24h = parseFloat(vehicleData.rate_24h) || parseFloat(vehicleData.rate_per_day) || 0;
    const dailyRate = parseFloat(vehicleData.rate_per_day) || rate24h || 2000;
    const hourlyRate = parseFloat(vehicleData.hourly_rate) || (rate8h / 8) || 250;

    let vehicleCost;
    let rateText;

    // Choose the best rate based on duration
    if (totalHours >= 24 && rate24h > 0) {
        const days = Math.ceil(totalHours / 24);
        vehicleCost = rate24h * days;
        rateText = `₱${rate24h.toLocaleString()}/24hrs × ${days} day${days > 1 ? 's' : ''}`;
    } else if (totalHours >= 12 && totalHours < 24 && rate12h > 0) {
        vehicleCost = rate12h;
        rateText = `₱${rate12h.toLocaleString()}/12hrs`;
    } else if (totalHours >= 8 && totalHours < 12 && rate8h > 0) {
        vehicleCost = rate8h;
        rateText = `₱${rate8h.toLocaleString()}/8hrs`;
    } else if (totalHours >= 6 && totalHours < 8 && rate6h > 0) {
        vehicleCost = rate6h;
        rateText = `₱${rate6h.toLocaleString()}/6hrs`;
    } else {
        // Fall back to hourly or daily calculation
        if (totalHours >= 24) {
            const days = Math.ceil(totalHours / 24);
            vehicleCost = dailyRate * days;
            rateText = `₱${dailyRate.toLocaleString()}/day × ${days} day${days > 1 ? 's' : ''}`;
        } else {
            vehicleCost = hourlyRate * Math.ceil(totalHours);
            rateText = `₱${hourlyRate.toLocaleString()}/hour × ${Math.ceil(totalHours)} hours`;
        }
    }

    // Calculate total cost including location charges
    const totalCost = vehicleCost + locationCharges.totalLocationCharge;

    console.log('Cost breakdown:', {
        vehicleCost,
        locationCharges: locationCharges.totalLocationCharge,
        totalCost
    });

    // Update UI with detailed breakdown
    document.getElementById('summary-vehicle').textContent = selectedVehicle;
    document.getElementById('summary-duration').textContent = `${Math.ceil(totalHours)} hours`;
    document.getElementById('summary-rate').textContent = rateText;
    
    // FIXED: Update the cost breakdown section with proper return location display
    const costBreakdownHtml = `
        <div class="space-y-2">
            <div class="flex justify-between">
                <span>Vehicle Rental:</span>
                <span class="font-semibold">₱${Math.round(vehicleCost).toLocaleString()}</span>
            </div>
            ${locationCharges.pickupCharge > 0 ? `
            <div class="flex justify-between text-sm">
                <span>Pickup (${getLocationDisplayName(pickupLocation)}):</span>
                <span>₱${locationCharges.pickupCharge.toLocaleString()}</span>
            </div>
            ` : ''}
            ${locationCharges.returnCharge > 0 ? `
            <div class="flex justify-between text-sm">
                <span>Return (${getLocationDisplayName(effectiveReturnLocation)}):</span>
                <span>₱${locationCharges.returnCharge.toLocaleString()}</span>
            </div>
            ` : ''}
            ${locationCharges.totalLocationCharge === 0 ? `
            <div class="flex justify-between text-sm text-green-600">
                <span>Location Charges:</span>
                <span>FREE ${!returnLocation ? '(Same pickup/return location)' : ''}</span>
            </div>
            ` : ''}
            <div class="border-t pt-2 flex justify-between font-bold text-lg">
                <span>Total:</span>
                <span class="text-green-600">₱${Math.round(totalCost).toLocaleString()}</span>
            </div>
        </div>
    `;
    
    // Update the cost summary section
    const costSummaryElement = document.getElementById('cost-summary');
    const summaryGrid = costSummaryElement.querySelector('.bg-white.p-4');
    
    if (summaryGrid) {
        summaryGrid.innerHTML = `
            <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                <div class="font-medium">Selected Vehicle:</div>
                <div class="text-blue-600 font-semibold">${selectedVehicle}</div>
                
                <div class="font-medium">Rental Duration:</div>
                <div class="text-blue-600 font-semibold">${Math.ceil(totalHours)} hours</div>
                
                <div class="font-medium">Rate:</div>
                <div class="text-blue-600 font-semibold">${rateText}</div>
                
                <div class="font-medium">Pickup Location:</div>
                <div class="text-blue-600 font-semibold">${getLocationDisplayName(pickupLocation)}</div>
                
                <div class="font-medium">Return Location:</div>
                <div class="text-blue-600 font-semibold">${returnLocation ? getLocationDisplayName(returnLocation) : 'Same as pickup'}</div>
            </div>
            ${costBreakdownHtml}
        `;
    }
    
    document.getElementById('cost-summary').classList.remove('hidden');
    
    const saveButton = document.getElementById('save-booking-btn');
    if (saveButton) {
        saveButton.style.display = 'block';
    }
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

// Quick booking form handler
function handleQuickBookingForm(event) {
    event.preventDefault();
    
    // Get form values
    const pickupLocation = document.getElementById('pickup-location').value;
    const pickupDate = document.getElementById('pickup-date').value;
    const pickupTime = document.getElementById('pickup-time').value;
    const returnDate = document.getElementById('return-date').value;
    const returnTime = document.getElementById('return-time').value;
    const returnLocation = document.getElementById('return-location').value;
    
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
    
    // Store quick booking data - FIXED: handle empty return location properly
    bookingData.quickBooking = {
        pickupLocation,
        pickupDate,
        pickupTime,
        returnDate,
        returnTime,
        returnLocation: returnLocation || '' // Keep empty if not specified
    };
    
    // Scroll to vehicles section
    document.getElementById('vehicles').scrollIntoView({ behavior: 'smooth' });
    
    // FIXED: Show message about location pricing with proper return location handling
    const effectiveReturnLocation = returnLocation || pickupLocation;
    const locationCharges = calculateLocationCharges(pickupLocation, effectiveReturnLocation);
    
    if (locationCharges.totalLocationCharge > 0) {
        let message = `Note: Additional charges will apply - `;
        if (locationCharges.pickupCharge > 0) {
            message += `Pickup: ₱${locationCharges.pickupCharge.toLocaleString()}`;
        }
        if (locationCharges.returnCharge > 0) {
            if (locationCharges.pickupCharge > 0) message += ', ';
            message += `Return: ₱${locationCharges.returnCharge.toLocaleString()}`;
        }
        message += ` (Total: ₱${locationCharges.totalLocationCharge.toLocaleString()})`;
        showMessage(message, 'info');
    } else if (!returnLocation) {
        showMessage('Great! No additional location charges since you\'re returning to the same location.', 'info');
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

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
    // Hide loading spinner initially and show it when loading vehicles
    const loadingElement = document.getElementById('vehicles-loading');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
    
    // Initialize quick booking form
    initializeQuickBookingForm();
    
    // ADDED: Initialize location handling
    initializeLocationHandling();
    
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
                
                // FIXED: Ensure return location is handled properly in form data
                const returnLocationValue = document.getElementById('return-location-modal').value;
                if (!returnLocationValue) {
                    // If return location is empty, set it to pickup location for the backend
                    const pickupLocationValue = document.getElementById('pickup-location-modal').value;
                    formData.set('return_location', pickupLocationValue);
                }
                
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
    ['start-date', 'end-date', 'start-time', 'end-time', 'pickup-location-modal', 'return-location-modal'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', calculateRentalCost);
        }
    });
    
    // Show location pricing info when user changes location
    const locationSelects = document.querySelectorAll('#pickup-location-modal, #return-location-modal, #pickup-location');
    locationSelects.forEach(select => {
        select.addEventListener('change', function() {
            const selectedValue = this.value;
            const charge = locationPricing[selectedValue] || 0;
            
            // Only show info for pickup location and non-empty return location
            if (charge > 0 && (this.id === 'pickup-location-modal' || (this.id === 'return-location-modal' && selectedValue))) {
                // Show tooltip or info about additional charge
                const infoDiv = document.createElement('div');
                infoDiv.className = 'text-xs text-orange-600 mt-1';
                infoDiv.textContent = `Additional ₱${charge.toLocaleString()} for this location`;
                
                // Remove any existing info
                const existingInfo = this.parentNode.querySelector('.text-orange-600');
                if (existingInfo) {
                    existingInfo.remove();
                }
                
                // Add new info
                this.parentNode.appendChild(infoDiv);
                
                // Remove info after 3 seconds
                setTimeout(() => {
                    if (infoDiv && infoDiv.parentNode) {
                        infoDiv.remove();
                    }
                }, 3000);
            }
        });
    });
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