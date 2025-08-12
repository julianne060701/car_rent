const cars = [
    {
        id: 1,
        name: 'Toyota Vios',
        type: 'Sedan',
        price: 2500,
        passengers: 5,
        transmission: 'Manual',
        fuel: 'Gasoline',
        rating: 4.8,
        features: ['Air Conditioning', 'Power Steering', 'CD Player', 'USB Port'],
        available: true
    },
    {
        id: 2,
        name: 'Honda City',
        type: 'Sedan',
        price: 2800,
        passengers: 5,
        transmission: 'Automatic',
        fuel: 'Gasoline',
        rating: 4.9,
        features: ['Air Conditioning', 'Power Steering', 'Bluetooth', 'USB Port'],
        available: true
    },
    {
        id: 3,
        name: 'Toyota Innova',
        type: 'MPV',
        price: 4500,
        passengers: 8,
        transmission: 'Manual',
        fuel: 'Diesel',
        rating: 4.7,
        features: ['Air Conditioning', '8-Seater', 'Spacious', 'Perfect for Groups'],
        available: true
    },
    {
        id: 4,
        name: 'Mitsubishi Montero',
        type: 'SUV',
        price: 5500,
        passengers: 7,
        transmission: '4WD',
        fuel: 'Diesel',
        rating: 4.6,
        features: ['4WD', 'Air Conditioning', 'Perfect for Mountains', 'Rugged'],
        available: false
    },
    {
        id: 5,
        name: 'Nissan Navara',
        type: 'Pickup',
        price: 4800,
        passengers: 5,
        transmission: 'Manual',
        fuel: 'Diesel',
        rating: 4.5,
        features: ['4WD', 'Cargo Space', 'Tough', 'Off-road Ready'],
        available: true
    },
    {
        id: 6,
        name: 'Hyundai Accent',
        type: 'Sedan',
        price: 2300,
        passengers: 5,
        transmission: 'Manual',
        fuel: 'Gasoline',
        rating: 4.4,
        features: ['Air Conditioning', 'Fuel Efficient', 'Compact', 'City Driving'],
        available: true
    }
];

const destinations = [
    { name: 'T\'boli', distance: '45km', time: '1h', description: 'Cultural heritage and scenic views' },
    { name: 'Lake Sebu', distance: '85km', time: '2h', description: 'Beautiful lakes and waterfalls' },
    { name: 'Sarangani Bay', distance: '25km', time: '30min', description: 'Beach resorts and fishing' },
    { name: 'Tupi', distance: '35km', time: '45min', description: 'Pineapple plantations' },
    { name: 'Koronadal', distance: '28km', time: '35min', description: 'Provincial capital' },
    { name: 'Surallah', distance: '42km', time: '50min', description: 'Agricultural town' }
];

let selectedCar = null;
let rentalDays = 0;

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeDates();
    renderCars();
    renderDestinations();
    setupEventListeners();
});

function initializeDates() {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    document.getElementById('pickup-date').min = today.toISOString().split('T')[0];
    document.getElementById('return-date').min = tomorrow.toISOString().split('T')[0];
    
    // Set default dates
    document.getElementById('pickup-date').value = today.toISOString().split('T')[0];
    document.getElementById('return-date').value = tomorrow.toISOString().split('T')[0];
}

function setupEventListeners() {
    document.getElementById('pickup-date').addEventListener('change', function() {
        const pickupDate = new Date(this.value);
        const returnDate = new Date(this.value);
        returnDate.setDate(returnDate.getDate() + 1);
        
        document.getElementById('return-date').min = returnDate.toISOString().split('T')[0];
        
        if (document.getElementById('return-date').value <= this.value) {
            document.getElementById('return-date').value = returnDate.toISOString().split('T')[0];
        }
        
        updatePricing();
    });

    document.getElementById('return-date').addEventListener('change', updatePricing);
    document.getElementById('pickup-location').addEventListener('change', updateBookingBar);

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('booking-modal');
        if (event.target === modal) {
            closeBookingModal();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('booking-modal').style.display === 'block') {
            closeBookingModal();
        }
    });
}

function renderCars() {
    const carsGrid = document.getElementById('cars-grid');
    carsGrid.innerHTML = '';

    cars.forEach(car => {
        const carCard = document.createElement('div');
        carCard.className = `car-card ${!car.available ? 'unavailable' : ''}`;
        carCard.dataset.carId = car.id;

        const featuresHTML = car.features.slice(0, 3).map(feature => 
            `<span class="feature-tag">${feature}</span>`
        ).join('');

        carCard.innerHTML = `
            <div class="car-image">
                <i class="fas fa-car"></i>
            </div>
            <div class="car-details">
                <div class="car-header">
                    <div>
                        <div class="car-title">${car.name}</div>
                        <div class="car-type">${car.type}</div>
                    </div>
                    <div class="car-rating">
                        <span class="rating-stars">★</span>
                        <span>${car.rating}</span>
                    </div>
                </div>
                
                <div class="car-specs">
                    <div class="spec-item">
                        <i class="fas fa-users"></i>
                        <span>${car.passengers}</span>
                    </div>
                    <div class="spec-item">
                        <i class="fas fa-cog"></i>
                        <span>${car.transmission}</span>
                    </div>
                    <div class="spec-item">
                        <i class="fas fa-gas-pump"></i>
                        <span>${car.fuel}</span>
                    </div>
                </div>
                
                <div class="car-features">
                    ${featuresHTML}
                </div>
                
                <div class="car-footer">
                    <div>
                        <span class="car-price">₱${car.price.toLocaleString()}</span>
                        <span class="price-period">/day</span>
                    </div>
                    <button class="select-btn" onclick="selectCar(${car.id})" ${!car.available ? 'disabled' : ''}>
                        ${car.available ? 'Select' : 'Unavailable'}
                    </button>
                </div>
            </div>
        `;

        carsGrid.appendChild(carCard);
    });
}

function renderDestinations() {
    const destinationsGrid = document.getElementById('destinations-grid');
    destinationsGrid.innerHTML = '';

    destinations.forEach(destination => {
        const destinationCard = document.createElement('div');
        destinationCard.className = 'destination-card';
        destinationCard.onclick = () => showDestinationInfo(destination);

        destinationCard.innerHTML = `
            <div class="destination-name">${destination.name}</div>
            <div class="destination-info">
                <div>${destination.distance}</div>
                <div>${destination.time} drive</div>
            </div>
        `;

        destinationsGrid.appendChild(destinationCard);
    });
}

function searchCars() {
    const pickupDate = document.getElementById('pickup-date').value;
    const returnDate = document.getElementById('return-date').value;

    if (!pickupDate || !returnDate) {
        showNotification('Please select both pickup and return dates', 'error');
        return;
    }

    if (new Date(returnDate) <= new Date(pickupDate)) {
        showNotification('Return date must be after pickup date', 'error');
        return;
    }

    // Simulate search with loading
    showLoading();
    
    setTimeout(() => {
        hideLoading();
        document.querySelector('.cars-section').scrollIntoView({ behavior: 'smooth' });
        showNotification('Cars available for your selected dates!', 'success');
    }, 1500);
}

function selectCar(carId) {
    const car = cars.find(c => c.id === carId);
    if (!car || !car.available) return;

    // Remove selection from all cards
    document.querySelectorAll('.car-card').forEach(card => {
        card.classList.remove('selected');
        const btn = card.querySelector('.select-btn');
        if (btn && !btn.disabled) {
            btn.textContent = 'Select';
            btn.classList.remove('selected');
        }
    });

    // Select current card
    const selectedCard = document.querySelector(`[data-car-id="${carId}"]`);
    selectedCard.classList.add('selected');
    const btn = selectedCard.querySelector('.select-btn');
    btn.textContent = 'Selected';
    btn.classList.add('selected');

    selectedCar = car;
    updateBookingBar();
    updatePricing();

    showNotification(`${car.name} selected!`, 'success');
}

function calculateRentalDays() {
    const pickupDate = new Date(document.getElementById('pickup-date').value);
    const returnDate = new Date(document.getElementById('return-date').value);
    
    if (pickupDate && returnDate && returnDate > pickupDate) {
        const timeDiff = returnDate.getTime() - pickupDate.getTime();
        rentalDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
        return rentalDays;
    }
    return 1;
}

function updatePricing() {
    if (!selectedCar) return;

    const days = calculateRentalDays();
    const totalPrice = selectedCar.price * days;
    
    document.getElementById('selected-car-price').textContent = 
        `₱${totalPrice.toLocaleString()} (${days} day${days > 1 ? 's' : ''})`;
}

function updateBookingBar() {
    if (selectedCar) {
        document.getElementById('selected-car-name').textContent = selectedCar.name;
        document.getElementById('selected-pickup-location').textContent = 
            document.getElementById('pickup-location').value;
        
        updatePricing();
        
        const bookingBar = document.getElementById('booking-bar');
        bookingBar.classList.add('active');
        document.body.style.paddingBottom = '120px';
    }
}

function openBookingModal() {
    if (!selectedCar) {
        showNotification('Please select a car first', 'error');
        return;
    }

    const pickupDate = document.getElementById('pickup-date').value;
    const returnDate = document.getElementById('return-date').value;
    const pickupTime = document.getElementById('pickup-time').value;
    const pickupLocation = document.getElementById('pickup-location').value;

    if (!pickupDate || !returnDate) {
        showNotification('Please select pickup and return dates', 'error');
        return;
    }

    const days = calculateRentalDays();
    const dailyPrice = selectedCar.price;
    const subtotal = dailyPrice * days;
    const insurance = Math.floor(subtotal * 0.1); // 10% insurance
    const tax = Math.floor(subtotal * 0.12); // 12% tax
    const total = subtotal + insurance + tax;

    const summaryHTML = `
        <div class="price-row">
            <span>Car:</span>
            <span><strong>${selectedCar.name}</strong></span>
        </div>
        <div class="price-row">
            <span>Pickup:</span>
            <span>${formatDate(pickupDate)} at ${pickupTime}</span>
        </div>
        <div class="price-row">
            <span>Return:</span>
            <span>${formatDate(returnDate)}</span>
        </div>
        <div class="price-row">
            <span>Location:</span>
            <span>${pickupLocation}</span>
        </div>
        <div class="price-row">
            <span>Duration:</span>
            <span>${days} day${days > 1 ? 's' : ''}</span>
        </div>
        <div class="price-row">
            <span>Daily Rate:</span>
            <span>₱${dailyPrice.toLocaleString()}</span>
        </div>
        <div class="price-row">
            <span>Subtotal:</span>
            <span>₱${subtotal.toLocaleString()}</span>
        </div>
        <div class="price-row">
            <span>Insurance (10%):</span>
            <span>₱${insurance.toLocaleString()}</span>
        </div>
        <div class="price-row">
            <span>Tax (12%):</span>
            <span>₱${tax.toLocaleString()}</span>
        </div>
        <div class="price-row total">
            <span>Total Price:</span>
            <span>₱${total.toLocaleString()}</span>
        </div>
    `;

    document.getElementById('summary-details').innerHTML = summaryHTML;
    document.getElementById('booking-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeBookingModal() {
    document.getElementById('booking-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.querySelector('.booking-form-modal').reset();
    clearFormErrors();
}

function submitBooking(event) {
    event.preventDefault();
    
    if (!validateForm()) {
        return false;
    }

    const submitBtn = document.getElementById('submit-btn');
    submitBtn.innerHTML = '<span class="loading-spinner"></span>Processing...';
    submitBtn.disabled = true;

    // Simulate booking process
    setTimeout(() => {
        closeBookingModal();
        showSuccessMessage();
        resetBooking();
        
        submitBtn.innerHTML = 'Confirm Booking';
        submitBtn.disabled = false;
    }, 3000);
}

function validateForm() {
    clearFormErrors();
    let isValid = true;

    const name = document.querySelector('input[name="customer_name"]');
    const phone = document.querySelector('input[name="customer_phone"]');
    const email = document.querySelector('input[name="customer_email"]');

    // Name validation
    if (!name.value.trim()) {
        showFieldError(name, 'Please enter your full name');
        isValid = false;
    }

    // Phone validation
    const phoneRegex = /^(09|\+639)\d{9}$/;
    if (!phoneRegex.test(phone.value.replace(/\s+/g, ''))) {
        showFieldError(phone, 'Please enter a valid Philippine phone number');
        isValid = false;
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email.value)) {
        showFieldError(email, 'Please enter a valid email address');
        isValid = false;
    }

    return isValid;
}

function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    formGroup.classList.add('error');
    formGroup.querySelector('.error-message').textContent = message;
}

function clearFormErrors() {
    document.querySelectorAll('.form-group.error').forEach(group => {
        group.classList.remove('error');
    });
}

function showSuccessMessage() {
    document.getElementById('success-message').style.display = 'block';
    setTimeout(() => {
        document.getElementById('success-message').style.display = 'none';
    }, 5000);
}

function resetBooking() {
    selectedCar = null;
    document.querySelectorAll('.car-card').forEach(card => {
        card.classList.remove('selected');
        const btn = card.querySelector('.select-btn');
        if (btn && !btn.disabled) {
            btn.textContent = 'Select';
            btn.classList.remove('selected');
        }
    });
    
    document.getElementById('booking-bar').classList.remove('active');
    document.body.style.paddingBottom = '0';
}

function showDestinationInfo(destination) {
    showNotification(`${destination.name}: ${destination.description}`, 'info');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(() => {
        showNotification('Could not copy to clipboard', 'error');
    });
}

function showNotification(message, type = 'info') {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.className = `notification ${type}`;
    notification.classList.add('show');

    setTimeout(() => {
        notification.classList.remove('show');
    }, 4000);
}

function showLoading() {
    document.getElementById('loading-overlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        weekday: 'short', 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

// Scroll animations
function animateOnScroll() {
    const elements = document.querySelectorAll('.car-card, .destination-card');
    elements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < window.innerHeight - elementVisible) {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }
    });
}

// Initial setup for animations
window.addEventListener('load', () => {
    document.querySelectorAll('.car-card, .destination-card').forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    });
    
    setTimeout(animateOnScroll, 100);
});

window.addEventListener('scroll', animateOnScroll);