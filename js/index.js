let bookingData = {
    quickBooking: {},
    modalBooking: {}
};

// Vehicle pricing data
const vehiclePricing = {
    'Toyota Vios': { dailyRate: 1200, hourlyRate: 50 },
    'Toyota Innova': { dailyRate: 2000, hourlyRate: 83 },
    'Honda City': { dailyRate: 1300, hourlyRate: 54 },
    'Mitsubishi Xpander': { dailyRate: 1800, hourlyRate: 75 }
};

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
    
    messageContent.innerHTML = `<p class="mb-4">${message}</p><button onclick="closeMessageModal()" class="btn btn-primary">OK</button>`;
    messageModal.style.display = 'flex';
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

    // Make AJAX request to save_booking.php
    fetch('save_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data);
        
        if (data.status === 'success') {
            showMessage(
                `Thank you! Your booking has been successfully submitted.<br>
                <strong>Booking Reference:</strong> ${data.booking_reference}<br>
                <strong>Total Cost:</strong> ₱${data.total_cost}<br>
                We will contact you within 24 hours to confirm your reservation.`, 
                "success"
            );
            
            // Reset form and close modal
            document.getElementById('booking-modal-form').reset();
            document.getElementById('cost-summary').classList.add('hidden');
            removeImage();
            closeBookingModal();
            
        } else {
            showMessage(data.message || 'An error occurred while processing your booking.', "error");
        }
    })
    .catch(error => {
        console.error('Error submitting booking:', error);
        showMessage('Network error: Unable to submit booking. Please check your connection and try again.', "error");
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    
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