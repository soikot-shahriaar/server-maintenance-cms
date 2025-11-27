/**
 * Server Maintenance Log CMS - JavaScript Enhancements
 */

// DOM Content Loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initFormValidation();
    initTableSorting();
    initSearchEnhancements();
    initTooltips();
    initAutoSave();
});

/**
 * Form Validation Enhancements
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearFieldError);
        });
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    
    // Remove existing error
    clearFieldError(e);
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'This field is required');
        return false;
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            showFieldError(field, 'Please enter a valid email address');
            return false;
        }
    }
    
    // Password validation
    if (field.type === 'password' && value) {
        if (value.length < 6) {
            showFieldError(field, 'Password must be at least 6 characters long');
            return false;
        }
    }
    
    // Confirm password validation
    if (field.name === 'confirm_password' && value) {
        const passwordField = document.querySelector('input[name="password"]');
        if (passwordField && value !== passwordField.value) {
            showFieldError(field, 'Passwords do not match');
            return false;
        }
    }
    
    return true;
}

function clearFieldError(e) {
    const field = e.target;
    const errorElement = field.parentNode.querySelector('.field-error');
    if (errorElement) {
        errorElement.remove();
    }
    field.classList.remove('error');
}

function showFieldError(field, message) {
    field.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.color = '#e74c3c';
    errorElement.style.fontSize = '0.8rem';
    errorElement.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorElement);
}

function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!validateField({ target: input })) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Table Sorting Enhancements
 */
function initTableSorting() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th a');
        headers.forEach(header => {
            header.addEventListener('click', function(e) {
                // Add loading indicator
                const indicator = document.createElement('span');
                indicator.textContent = ' ⏳';
                header.appendChild(indicator);
            });
        });
    });
}

/**
 * Search Enhancements
 */
function initSearchEnhancements() {
    const searchInput = document.getElementById('search');
    if (searchInput) {
        // Auto-focus search input
        searchInput.focus();
        
        // Search suggestions (basic implementation)
        searchInput.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            if (value.length > 2) {
                // Could implement autocomplete here
                console.log('Searching for:', value);
            }
        });
        
        // Clear search button
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.textContent = '✕';
        clearButton.className = 'btn btn-sm btn-secondary';
        clearButton.style.marginLeft = '0.5rem';
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
        });
        
        if (searchInput.value) {
            searchInput.parentNode.appendChild(clearButton);
        }
    }
}

/**
 * Tooltips
 */
function initTooltips() {
    const elements = document.querySelectorAll('[title]');
    
    elements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const element = e.target;
    const title = element.getAttribute('title');
    
    if (!title) return;
    
    // Create tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = title;
    tooltip.style.cssText = `
        position: absolute;
        background: #2c3e50;
        color: white;
        padding: 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
    `;
    
    document.body.appendChild(tooltip);
    
    // Position tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
    
    // Store reference
    element._tooltip = tooltip;
    
    // Remove title to prevent default tooltip
    element.setAttribute('data-title', title);
    element.removeAttribute('title');
}

function hideTooltip(e) {
    const element = e.target;
    
    if (element._tooltip) {
        element._tooltip.remove();
        element._tooltip = null;
    }
    
    // Restore title
    const title = element.getAttribute('data-title');
    if (title) {
        element.setAttribute('title', title);
        element.removeAttribute('data-title');
    }
}

/**
 * Auto-save for forms (draft functionality)
 */
function initAutoSave() {
    const forms = document.querySelectorAll('.maintenance-form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        const formId = form.getAttribute('action') || 'default';
        
        // Load saved data
        loadFormData(form, formId);
        
        // Save on input
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                saveFormData(form, formId);
            });
        });
        
        // Clear saved data on successful submit
        form.addEventListener('submit', function() {
            clearFormData(formId);
        });
    });
}

function saveFormData(form, formId) {
    const data = new FormData(form);
    const formData = {};
    
    for (let [key, value] of data.entries()) {
        if (key !== 'csrf_token') {
            formData[key] = value;
        }
    }
    
    localStorage.setItem('form_' + formId, JSON.stringify(formData));
    
    // Show save indicator
    showSaveIndicator();
}

function loadFormData(form, formId) {
    const savedData = localStorage.getItem('form_' + formId);
    
    if (savedData) {
        const data = JSON.parse(savedData);
        
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field && field.type !== 'hidden') {
                field.value = data[key];
            }
        });
        
        // Show restore indicator
        showRestoreIndicator();
    }
}

function clearFormData(formId) {
    localStorage.removeItem('form_' + formId);
}

function showSaveIndicator() {
    const indicator = document.getElementById('save-indicator') || createSaveIndicator();
    indicator.textContent = '💾 Draft saved';
    indicator.style.opacity = '1';
    
    setTimeout(() => {
        indicator.style.opacity = '0';
    }, 2000);
}

function showRestoreIndicator() {
    const indicator = document.getElementById('save-indicator') || createSaveIndicator();
    indicator.textContent = '📄 Draft restored';
    indicator.style.opacity = '1';
    
    setTimeout(() => {
        indicator.style.opacity = '0';
    }, 3000);
}

function createSaveIndicator() {
    const indicator = document.createElement('div');
    indicator.id = 'save-indicator';
    indicator.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #27ae60;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 0.9rem;
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.3s ease;
    `;
    
    document.body.appendChild(indicator);
    return indicator;
}

/**
 * Utility Functions
 */

// Confirm dialogs with better styling
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Loading states for buttons
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Loading...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }
}

// Smooth scrolling for anchor links
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

// Auto-hide alerts after 5 seconds
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }, 5000);
});

