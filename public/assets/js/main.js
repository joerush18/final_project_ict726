/**
 * Car Service Portal - Main JavaScript
 * Handles client-side validation and interactivity
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    initMobileNav();
    
    // Form validation
    initFormValidation();
    
    // Date input restrictions
    initDateInputs();
    
    // Confirmation dialogs
    initConfirmDialogs();

    // Animated counters
    initCounters();

    // Reveal on scroll
    initReveal();
});

/**
 * Initialize mobile navigation toggle
 */
function initMobileNav() {
    const toggle = document.querySelector('.navbar-toggle');
    const menu = document.querySelector('.navbar-menu');
    
    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('active');
            const isExpanded = menu.classList.contains('active');
            toggle.setAttribute('aria-expanded', isExpanded);
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                menu.classList.remove('active');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[novalidate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                return false;
            }
        });
        
        // Real-time validation on blur
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(input);
            });
        });
    });
}

/**
 * Validate a single form field
 */
function validateField(field) {
    const errorElement = field.parentElement.querySelector('.error-message');
    let isValid = true;
    let errorMessage = '';
    
    // Remove previous error styling
    field.classList.remove('error');
    
    // Required field check
    if (field.hasAttribute('required') && !field.value.trim()) {
        isValid = false;
        errorMessage = 'This field is required';
    }
    
    // Email validation
    if (field.type === 'email' && field.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
    }
    
    // Password validation
    if (field.type === 'password' && field.value) {
        if (field.hasAttribute('minlength')) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (field.value.length < minLength) {
                isValid = false;
                errorMessage = `Password must be at least ${minLength} characters`;
            }
        }
    }
    
    // Confirm password validation
    if (field.id === 'confirm_password') {
        const passwordField = document.getElementById('password');
        if (passwordField && field.value !== passwordField.value) {
            isValid = false;
            errorMessage = 'Passwords do not match';
        }
    }
    
    // Number validation
    if (field.type === 'number' && field.value) {
        const min = field.getAttribute('min');
        const max = field.getAttribute('max');
        const value = parseFloat(field.value);
        
        if (min && value < parseFloat(min)) {
            isValid = false;
            errorMessage = `Value must be at least ${min}`;
        }
        if (max && value > parseFloat(max)) {
            isValid = false;
            errorMessage = `Value must be at most ${max}`;
        }
    }
    
    // Date validation
    if (field.type === 'date' && field.value) {
        const selectedDate = new Date(field.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (field.hasAttribute('min')) {
            const minDate = new Date(field.getAttribute('min'));
            if (selectedDate < minDate) {
                isValid = false;
                errorMessage = 'Date cannot be in the past';
            }
        }
    }
    
    // Display error
    if (!isValid) {
        field.classList.add('error');
        if (errorElement) {
            errorElement.textContent = errorMessage;
            errorElement.style.display = 'block';
        }
    } else {
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }
    }
    
    return isValid;
}

/**
 * Validate entire form
 */
function validateForm(form) {
    const fields = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isFormValid = true;
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isFormValid = false;
        }
    });
    
    // Special validation for password confirmation
    const confirmPassword = form.querySelector('#confirm_password');
    const password = form.querySelector('#password');
    if (confirmPassword && password) {
        if (confirmPassword.value !== password.value) {
            isFormValid = false;
            validateField(confirmPassword);
        }
    }
    
    // Scroll to first error
    if (!isFormValid) {
        const firstError = form.querySelector('.error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
    
    return isFormValid;
}

/**
 * Initialize date inputs with restrictions
 */
function initDateInputs() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set minimum date to today if not already set
        if (!input.getAttribute('min')) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);
        }
        
        // Prevent selecting past dates
        input.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                this.value = '';
                alert('Please select a future date');
            }
        });
    });
}

/**
 * Initialize confirmation dialogs
 */
function initConfirmDialogs() {
    const cancelLinks = document.querySelectorAll('a[onclick*="confirm"]');
    
    cancelLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const message = this.getAttribute('onclick')?.match(/confirm\('([^']+)'\)/)?.[1] || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Animate number counters on the home page
 */
function initCounters() {
    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    const animate = (el) => {
        const target = parseInt(el.getAttribute('data-count'), 10) || 0;
        const duration = 1200;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const value = Math.floor(progress * target);
            el.textContent = value.toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animate(entry.target);
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.6 });

    counters.forEach(counter => observer.observe(counter));
}

/**
 * Simple reveal animation on scroll
 */
function initReveal() {
    const elements = document.querySelectorAll('.feature-card, .garage-card, .service-card, .step-card, .testimonial-card, .stat-tile');
    if (!elements.length) return;

    elements.forEach(el => el.classList.add('reveal'));

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal-visible');
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    elements.forEach(el => observer.observe(el));
}

/**
 * Show/hide sections (for future use)
 */
function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    }
}

/**
 * Format currency (helper function)
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

/**
 * Format date (helper function)
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Add loading state to buttons
 */
function setButtonLoading(button, isLoading) {
    if (isLoading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }
}

// Add error styling to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    .error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
    }
`;
document.head.appendChild(style);
