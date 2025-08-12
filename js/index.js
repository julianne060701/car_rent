// GenSan Car Rentals - Main JavaScript File

// Load includes and initialize app
document.addEventListener('DOMContentLoaded', function() {
    loadHeader();
    loadFooter();
    initDatePickers();
    startTestimonialSlider();
    initScrollEffects();
    initLoadingAnimations();
});

// Load Header Include
function loadHeader() {
    const headerHTML = `
        <header class="header">
            <nav class="nav container">
                <div class="logo">GenSan Car Rentals</div>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#vehicles">Vehicles</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <div class="contact-info">
                    <span><i class="fas fa-phone"></i> (083) 555-0123</span>
                    <span><i class="fas fa-envelope"></i> info@gensanrentals.com</span>
                </div>
            </nav>
        </header>
    `;
    const headerContainer = document.getElementById('header-include');
    if (headerContainer) {
        headerContainer.innerHTML = headerHTML;
    }
}

// Load Footer Include
function loadFooter() {
    const footerHTML = `
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-section">
                        <h3>GenSan Car Rentals</h3>
                        <p>Your trusted partner for reliable and affordable car rentals in General Santos City and surrounding areas.</p>
                        <div style="margin-top: 1rem;">
                            <i class="fab fa-facebook" style="margin-right: 1rem; font-size: 1.5rem; cursor: pointer;"></i>
                            <i class="fab fa-instagram" style="margin-right: 1rem; font-size: 1.5rem; cursor: pointer;"></i>
                            <i class="fab fa-twitter" style="font-size: 1.5rem; cursor: pointer;"></i>
                        </div>
                    </div>
                    <div class="footer-section">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="#vehicles">Our Fleet</a></li>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#booking">Book Now</a></li>
                            <li><a href="#terms">Terms & Conditions</a></li>
                            <li><a href="#privacy">Privacy Policy</a></li>
                        </ul>
                    </div>
                    <div class="footer-section">
                        <h3>Contact Info</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Pioneer Avenue, General Santos City</p>
                        <p><i class="fas fa-phone"></i> (083) 555-0123</p>
                        <p><i class="fas fa-envelope"></i> info@gensanrentals.com</p>
                        <p><i class="fas fa-clock"></i> Open 24/7</p>
                    </div>
                    <div class="footer-section">
                        <h3>Service Areas</h3>
                        <ul>
                            <li>General Santos City</li>
                            <li>Koronadal City</li>
                            <li>Tacurong City</li>
                            <li>Kidapawan City</li>
                            <li>Digos City</li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2025 GenSan Car Rentals. All rights reserved.</p>
                </div>
            </div>
        </footer>
    `;
    const footerContainer = document.getElementById('footer-include');
    if (footerContainer) {
        footerContainer.innerHTML = footerHTML;
    }
}

// Initialize date pickers with validation
function initDatePickers() {
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const pickupDate = document.getElementById('pickup-date');
    const returnDate = document.getElementById('return-date');
    
    // Set default values and minimum dates
    if (pickupDate) {
        pickupDate.value = today.toISOString().split('T')[0];
        pickupDate.min = today.toISOString().split('T')[0];
    }
    
    if (returnDate) {
        returnDate.value = tomorrow.toISOString().split('T')[0];
        returnDate.min = tomorrow.toISOString().split('T')[0];
    }
    
    // Update return date minimum when pickup date changes
    if (pickupDate && returnDate) {
        pickupDate.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const nextDay = new Date(selectedDate);
            nextDay.setDate(nextDay.getDate() + 1);
            
            returnDate.min = nextDay.toISOString().split('T')[0];
            
            // Adjust return date if it's before the new minimum
            if (new Date(returnDate.value) <= selectedDate) {
                returnDate.value = nextDay.toISOString().split('T')[0];
            }
        });
    }
}

// Testimonial slider functionality
function startTestimonialSlider() {
    const testimonials = document.querySelectorAll('.testimonial');
    let currentTestimonial = 0;
    
    if (testimonials.length > 1) {
        setInterval(() => {
            testimonials[currentTestimonial].classList.remove('active');
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            testimonials[currentTestimonial].classList.add('active');
        }, 5000);
    }
}

// Modal functions
function openBookingModal() {
    const modal = document.getElementById('booking-modal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeBookingModal() {
    const modal = document.getElementById('booking-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Event Listeners
document.addEventListener('click', function(event) {
    // Handle booking modal close
    const modal = document.getElementById('booking-modal');
    if (event.target === modal) {
        closeBookingModal();
    }
    
    // Handle close button
    if (event.target.classList.contains('close')) {
        closeBookingModal();
    }
    
    // Handle book now buttons
    if (event.target.classList.contains('btn-book')) {
        event.preventDefault();
        openBookingModal();
        
        // Pre-fill vehicle type if available
        const vehicleCard = event.target.closest('.vehicle-card');
        if (vehicleCard) {
            const vehicleName = vehicleCard.querySelector('h3')?.textContent;
            console.log('Selected vehicle:', vehicleName);
        }
    }
    
    // Handle smooth scrolling for navigation
    if (event.target.matches('a[href^="#"]')) {
        event.preventDefault();
        const targetId = event.target.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});

// Form submission handling
document.addEventListener('submit', function(event) {
    if (event.target.classList.contains('booking-form') || 
        event.target.classList.contains('booking-modal-form')) {
        event.preventDefault();
        handleBookingSubmission(event.target);
    }
});

function handleBookingSubmission(form) {
    // Basic form validation
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.style.borderColor = '#e74c3c';
        } else {
            field.style.borderColor = '#ddd';
        }
    });
    
    if (!isValid) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Show loading state
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Processing...';
    submitButton.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        alert('Booking request submitted successfully! We will contact you shortly to confirm your reservation.');
        
        // Reset form and button
        form.reset();
        submitButton.textContent = originalText;
        submitButton.disabled = false;
        
        // Close modal if it's the modal form
        if (form.classList.contains('booking-modal-form')) {
            closeBookingModal();
        }
        
        // Reinitialize date pickers after form reset
        initDatePickers();
    }, 2000);
}

// Scroll effects
function initScrollEffects() {
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        if (header) {
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else {
                header.style.background = '#fff';
                header.style.backdropFilter = 'none';
            }
        }
    });
}

// Loading animations for elements
function initLoadingAnimations() {
    const cards = document.querySelectorAll('.vehicle-card, .service-item');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });
    
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
}

// Utility functions
function formatCurrency(amount) {
    return '₱' + amount.toLocaleString();
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return re.test(phone);
}

// Make functions globally available
window.openBookingModal = openBookingModal;
window.closeBookingModal = closeBookingModal;