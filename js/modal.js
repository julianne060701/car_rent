let currentModalCarId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize filters
    updateResultsCount();
});

function selectVehicle(car_id) {
    // Store selected vehicle and booking details
    const urlParams = new URLSearchParams(window.location.search);
    const bookingData = {
        car_id: car_id,
        pickup_location: urlParams.get('pickup_location'),
        pickup_date: urlParams.get('pickup_date'),
        pickup_time: urlParams.get('pickup_time'),
        return_date: urlParams.get('return_date'),
        return_time: urlParams.get('return_time')
    };
    
    // Store in sessionStorage for the booking form
    sessionStorage.setItem('bookingData', JSON.stringify(bookingData));
    
    // Redirect to booking page or show booking modal
    window.location.href = 'booking.php?' + new URLSearchParams(bookingData).toString();
}

function selectVehicleFromModal() {
    if (currentModalCarId) {
        selectVehicle(currentModalCarId);
    }
}

function viewDetails(carData) {
    currentModalCarId = carData.car_id;
    
    // Populate modal with car data
    document.getElementById('modalTitle').textContent = carData.car_name;
    
    // Set image
    const imageSrc = carData.car_image 
        ? 'uploads/cars/' + carData.car_image 
        : '../assets/img/no-image.png';
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalImage').alt = carData.car_name;
    
    // Set availability badge
    const availabilityBadge = document.getElementById('modalAvailabilityBadge');
    if (carData.is_available == 1) {
        availabilityBadge.innerHTML = '<span class="car-status-available">Available</span>';
    } else {
        if (carData.status === 'Available') {
            availabilityBadge.innerHTML = '<span class="car-status-booked">Booked</span>';
        } else {
            availabilityBadge.innerHTML = '<span class="car-status-maintenance">' + carData.status + '</span>';
        }
    }
    
    // Set vehicle information
    const vehicleType = getVehicleTypeJS(carData.passenger_seater);
    document.getElementById('modalType').textContent = vehicleType;
    document.getElementById('modalPassengers').textContent = carData.passenger_seater + ' passengers';
    document.getElementById('modalTransmission').textContent = carData.transmission;
    
    // Set pricing
    document.getElementById('modalDailyRate').textContent = '₱' + Number(carData.rate_24h).toLocaleString();
    document.getElementById('modal6Rate').textContent       = '₱' + Number(carData.rate_6h).toLocaleString();
    document.getElementById('modal8Rate').textContent       = '₱' + Number(carData.rate_8h).toLocaleString();
    document.getElementById('modal12Rate').textContent      = '₱' + Number(carData.rate_12h).toLocaleString();
    
    // Set features
    const features = ['A/C', 'GPS', 'Bluetooth', 'Insurance'];
    if (carData.passenger_seater >= 7) {
        features.push('Family Size');
    }
    
    const featuresHTML = features.map(feature => 
        `<span class="feature-badge">${feature}</span>`
    ).join('');
    document.getElementById('modalFeatures').innerHTML = featuresHTML;
    
    // Set rating
    const rating = 4.5 + ((carData.car_id % 10) / 20);
    const ratingStars = getStarRatingJS(rating);
    document.getElementById('modalRating').innerHTML = ratingStars;
    document.getElementById('modalRatingText').textContent = rating.toFixed(1) + ' (' + Math.floor(Math.random() * 185 + 15) + ' reviews)';
    
    // Set description
    const description = carData.description || 'This reliable and well-maintained vehicle is perfect for your transportation needs in General Santos City. Equipped with modern amenities and safety features, it provides comfort and peace of mind for both business and leisure trips.';
    document.getElementById('modalDescription').textContent = description;
    
    // Set book button state
    const bookButton = document.getElementById('modalBookButton');
    if (carData.is_available == 1) {
        bookButton.disabled = false;
        bookButton.className = 'btn-primary flex-1';
        bookButton.innerHTML = '<i class="fas fa-car mr-2"></i>Book This Vehicle';
    } else {
        bookButton.disabled = true;
        bookButton.className = 'bg-gray-400 text-white px-6 py-3 rounded-lg flex-1 cursor-not-allowed';
        bookButton.innerHTML = '<i class="fas fa-ban mr-2"></i>' + (carData.status === 'Available' ? 'Booked' : 'Unavailable');
    }
    
    // Show modal
    document.getElementById('carModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('carModal').classList.remove('active');
    document.body.style.overflow = '';
    currentModalCarId = null;
}

function getVehicleTypeJS(seater) {
    if (seater <= 4) return 'Sedan';
    if (seater <= 7) return 'SUV/MPV';
    return 'Van';
}

function getStarRatingJS(rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = (rating - fullStars) >= 0.5;
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    
    let stars = '<i class="fas fa-star"></i>'.repeat(fullStars);
    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    stars += '<i class="far fa-star"></i>'.repeat(emptyStars);
    
    return stars;
}

function applyFilters() {
    const showAvailable = document.getElementById('show-available').checked;
    const showUnavailable = document.getElementById('show-unavailable').checked;
    
    const priceFilters = Array.from(document.querySelectorAll('.price-filter:checked')).map(cb => cb.value);
    const capacityFilters = Array.from(document.querySelectorAll('.capacity-filter:checked')).map(cb => cb.value);
    const transmissionFilters = Array.from(document.querySelectorAll('.transmission-filter:checked')).map(cb => cb.value);

    const cards = document.querySelectorAll('.vehicle-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const isAvailable = card.dataset.available === '1';
        const price = parseInt(card.dataset.price);
        const capacity = parseInt(card.dataset.capacity);
        const transmission = card.dataset.transmission;

        let show = true;

        // Availability filter
        if (!showAvailable && isAvailable) show = false;
        if (!showUnavailable && !isAvailable) show = false;

        // Price filter
        if (priceFilters.length > 0) {
            let priceMatch = false;
            priceFilters.forEach(filter => {
                if (filter === '0-2000' && price <= 2000) priceMatch = true;
                if (filter === '2000-3500' && price > 2000 && price <= 3500) priceMatch = true;
                if (filter === '3500-5000' && price > 3500 && price <= 5000) priceMatch = true;
                if (filter === '5000+' && price > 5000) priceMatch = true;
            });
            if (!priceMatch) show = false;
        }

        // Capacity filter
        if (capacityFilters.length > 0) {
            let capacityMatch = false;
            capacityFilters.forEach(filter => {
                if (filter === '1-4' && capacity <= 4) capacityMatch = true;
                if (filter === '5-7' && capacity >= 5 && capacity <= 7) capacityMatch = true;
                if (filter === '8+' && capacity >= 8) capacityMatch = true;
            });
            if (!capacityMatch) show = false;
        }

        // Transmission filter
        if (transmissionFilters.length > 0) {
            if (!transmissionFilters.includes(transmission)) show = false;
        }

        card.style.display = show ? 'block' : 'none';
        if (show) visibleCount++;
    });

    updateResultsCount(visibleCount);
}

function updateResultsCount(count = null) {
    if (count === null) {
        const totalCards = document.querySelectorAll('.vehicle-card').length;
        const availableCards = document.querySelectorAll('.vehicle-card[data-available="1"]').length;
        document.getElementById('results-count').textContent = 
            `Showing ${totalCards} cars (${availableCards} available)`;
    } else {
        document.getElementById('results-count').textContent = `${count} cars shown`;
    }
}

// Add event listeners for real-time filtering
document.addEventListener('DOMContentLoaded', function() {
    const filterInputs = document.querySelectorAll('input[type="checkbox"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', applyFilters);
    });

    // Close modal when clicking outside
    document.getElementById('carModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});

// Prevent page scroll when modal is open
function toggleBodyScroll(disable) {
    if (disable) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
}