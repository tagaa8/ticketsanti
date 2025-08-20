/**
 * Santiago Tickets - Enhanced JavaScript Application
 * Responsive functionality and mobile enhancements
 */

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Check if device is mobile
function isMobile() {
    return window.innerWidth <= 768;
}

// Format date to local string
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Format time to local string
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(hours, minutes);
    return date.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
}

// Show loading state
function showLoading(element) {
    element.classList.add('loading');
    element.disabled = true;
}

// Hide loading state
function hideLoading(element) {
    element.classList.remove('loading');
    element.disabled = false;
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Style the toast
    Object.assign(toast.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '16px 24px',
        borderRadius: '8px',
        color: 'white',
        fontWeight: '500',
        zIndex: '10000',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease-in-out',
        maxWidth: '320px',
        wordWrap: 'break-word'
    });
    
    // Set background color based on type
    const colors = {
        info: '#007bff',
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107'
    };
    toast.style.backgroundColor = colors[type] || colors.info;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 5000);
}

// =============================================================================
// FORM VALIDATION
// =============================================================================

function validatePassword() {
    const password = document.getElementById('password');
    if (!password) return true;
    
    const value = password.value;
    const isValid = value.length >= 8 && /\d/.test(value);
    
    if (!isValid) {
        showPasswordError('La contraseña debe tener al menos 8 caracteres y contener al menos un número.');
        return false;
    }
    
    hidePasswordError();
    return true;
}

function showPasswordError(message) {
    const password = document.getElementById('password');
    const formGroup = password.closest('.form-group');
    
    // Remove existing error
    const existingError = formGroup.querySelector('.form-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Add error class
    formGroup.classList.add('has-error');
    password.classList.add('error');
    
    // Create error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'form-error';
    errorDiv.innerHTML = `
        <span class="error-icon">⚠️</span>
        <span>${message}</span>
    `;
    
    formGroup.appendChild(errorDiv);
}

function hidePasswordError() {
    const password = document.getElementById('password');
    const formGroup = password.closest('.form-group');
    
    formGroup.classList.remove('has-error');
    password.classList.remove('error');
    
    const errorDiv = formGroup.querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Enhanced form validation
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'Este campo es obligatorio');
            isValid = false;
        } else {
            hideFieldError(field);
            
            // Specific validations
            if (field.type === 'email' && !isValidEmail(field.value)) {
                showFieldError(field, 'Por favor, introduce un email válido');
                isValid = false;
            }
            
            if (field.name === 'password' && !validatePassword()) {
                isValid = false;
            }
        }
    });
    
    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function showFieldError(field, message) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;
    
    formGroup.classList.add('has-error');
    field.classList.add('error');
    
    let errorDiv = formGroup.querySelector('.form-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'form-error';
        formGroup.appendChild(errorDiv);
    }
    
    errorDiv.innerHTML = `
        <span class="error-icon">⚠️</span>
        <span>${message}</span>
    `;
}

function hideFieldError(field) {
    const formGroup = field.closest('.form-group');
    if (!formGroup) return;
    
    formGroup.classList.remove('has-error');
    field.classList.remove('error');
    
    const errorDiv = formGroup.querySelector('.form-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// =============================================================================
// TICKET MANAGEMENT
// =============================================================================

// Current active filter
let currentTicketFilter = 'proximos';

// Initialize ticket filters
function initializeTicketFilters() {
    const filters = ['pasados', 'hoy', 'proximos'];
    
    filters.forEach(filter => {
        const button = document.getElementById(`btn-${filter}`);
        if (button) {
            button.addEventListener('click', () => {
                setActiveFilter(filter);
                fetchTickets(filter);
            });
        }
    });
    
    // Load initial tickets
    if (document.getElementById('tickets-container')) {
        fetchTickets(currentTicketFilter);
    }
}

function setActiveFilter(filter) {
    // Remove active class from all buttons
    document.querySelectorAll('.tickets-filter button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to current button
    const activeButton = document.getElementById(`btn-${filter}`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
    
    currentTicketFilter = filter;
}

function fetchTickets(type) {
    const container = document.getElementById('tickets-container');
    if (!container) return;
    
    // Show loading state
    showTicketsLoading(container);
    
    fetch(`fetch_tickets.php?type=${type}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideTicketsLoading(container);
            renderTickets(data, container, type);
        })
        .catch(error => {
            console.error('Error fetching tickets:', error);
            hideTicketsLoading(container);
            showTicketsError(container, 'Error al cargar los tickets. Por favor, intenta de nuevo.');
        });
}

function showTicketsLoading(container) {
    container.innerHTML = '';
    container.className = 'tickets-container';
    
    // Create loading skeletons
    for (let i = 0; i < 3; i++) {
        const skeleton = createTicketSkeleton();
        container.appendChild(skeleton);
    }
}

function hideTicketsLoading(container) {
    container.innerHTML = '';
}

function createTicketSkeleton() {
    const skeleton = document.createElement('div');
    skeleton.className = 'ticket-skeleton';
    skeleton.innerHTML = `
        <div class="skeleton-line long"></div>
        <div class="skeleton-line medium"></div>
        <div class="skeleton-line short"></div>
        <div class="skeleton-line medium"></div>
        <div class="skeleton-qr"></div>
    `;
    return skeleton;
}

function renderTickets(tickets, container, type) {
    container.className = 'tickets-container';
    
    if (!tickets || tickets.length === 0) {
        showEmptyTickets(container, type);
        return;
    }
    
    // Determine layout based on screen size
    const isMobileView = isMobile();
    const ticketsGrid = document.createElement('div');
    ticketsGrid.className = isMobileView ? 'tickets-list' : 'tickets-grid';
    
    tickets.forEach(ticket => {
        const ticketElement = createTicketElement(ticket, isMobileView);
        ticketsGrid.appendChild(ticketElement);
    });
    
    container.appendChild(ticketsGrid);
}

function createTicketElement(ticket, isMobileView = false) {
    const ticketElement = document.createElement('div');
    const statusClass = getTicketStatusClass(ticket);
    
    ticketElement.className = `ticket ${statusClass}${isMobileView ? ' mobile-view' : ''}`;
    
    ticketElement.innerHTML = `
        <div class="ticket-header">
            <img src="src/img/gallery/full/logo.jpg" class="ticket-logo" alt="Logo">
            <div class="ticket-id">#${ticket.id_ticket}</div>
        </div>
        
        <div class="ticket-content">
            <div class="ticket-event">${ticket.nombre_evento}</div>
            
            <div class="ticket-details">
                <div class="ticket-detail">
                    <span class="detail-label">Ubicación:</span>
                    <span class="detail-value">${ticket.ubicacion_estadio}</span>
                </div>
                <div class="ticket-detail">
                    <span class="detail-label">Estadio:</span>
                    <span class="detail-value">${ticket.nombre_estadio}</span>
                </div>
                <div class="ticket-detail">
                    <span class="detail-label">Fecha:</span>
                    <span class="detail-value">${formatDate(ticket.fecha)}</span>
                </div>
                <div class="ticket-detail">
                    <span class="detail-label">Hora:</span>
                    <span class="detail-value">${formatTime(ticket.hora)}</span>
                </div>
                <div class="ticket-detail">
                    <span class="detail-label">Comprador:</span>
                    <span class="detail-value">${ticket.nombre_usuario} ${ticket.apellido_usuario}</span>
                </div>
            </div>
            
            <div class="ticket-meta">
                <div class="ticket-zone">
                    <span class="zone-label">Zona</span>
                    <span class="zone-value">${ticket.zona}</span>
                </div>
                <div class="ticket-seat">
                    <span class="seat-label">Asiento</span>
                    <span class="seat-value">${ticket.asiento}</span>
                </div>
            </div>
        </div>
        
        <div class="ticket-qr">
            <div class="qr-label">Código QR</div>
            <img src="src/codigos_qr/${ticket.qrcode}" class="ticket-qrcode" alt="QR Code">
        </div>
        
        <div class="ticket-status ${statusClass}">${getTicketStatusText(ticket)}</div>
        <div class="ticket-separator"></div>
    `;
    
    return ticketElement;
}

function getTicketStatusClass(ticket) {
    // This logic should be adjusted based on your ticket status system
    const currentDate = new Date();
    const ticketDate = new Date(ticket.fecha);
    
    if (ticket.id_activo === 2) return 'status-used';
    if (ticket.id_activo === 0) return 'status-expired';
    if (ticketDate < currentDate) return 'status-expired';
    
    return 'status-active';
}

function getTicketStatusText(ticket) {
    const statusClass = getTicketStatusClass(ticket);
    
    switch (statusClass) {
        case 'status-used':
            return 'Usado';
        case 'status-expired':
            return 'Expirado';
        case 'status-active':
        default:
            return 'Activo';
    }
}

function showEmptyTickets(container, type) {
    const emptyMessage = getEmptyMessage(type);
    
    container.innerHTML = `
        <div class="tickets-empty">
            <div class="empty-icon">🎫</div>
            <h3>No hay tickets ${type}</h3>
            <p>${emptyMessage}</p>
            <a href="index.php" class="cta-button">Explorar Eventos</a>
        </div>
    `;
}

function getEmptyMessage(type) {
    const messages = {
        'pasados': 'No tienes tickets de eventos pasados.',
        'hoy': 'No tienes tickets para eventos de hoy.',
        'proximos': 'No tienes tickets para eventos próximos. ¡Explora nuestros eventos disponibles!'
    };
    
    return messages[type] || 'No hay tickets disponibles.';
}

function showTicketsError(container, message) {
    container.innerHTML = `
        <div class="tickets-empty">
            <div class="empty-icon">⚠️</div>
            <h3>Error al cargar tickets</h3>
            <p>${message}</p>
            <button class="cta-button" onclick="fetchTickets('${currentTicketFilter}')">
                Intentar de nuevo
            </button>
        </div>
    `;
}

// =============================================================================
// RESPONSIVE UTILITIES
// =============================================================================

// Handle window resize
function handleResize() {
    const container = document.getElementById('tickets-container');
    if (container && container.children.length > 0) {
        // Re-render tickets with appropriate layout
        fetchTickets(currentTicketFilter);
    }
}

// Mobile menu toggle (if implemented)
function initializeMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    const closeBtn = document.querySelector('.mobile-nav .close-btn');
    
    if (menuToggle && mobileNav) {
        menuToggle.addEventListener('click', () => {
            mobileNav.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                mobileNav.classList.remove('active');
                document.body.style.overflow = '';
            });
        }
        
        // Close on backdrop click
        mobileNav.addEventListener('click', (e) => {
            if (e.target === mobileNav) {
                mobileNav.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
}

// =============================================================================
// INITIALIZATION
// =============================================================================

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ticket filters
    initializeTicketFilters();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Add form validation to all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (form.hasAttribute('novalidate')) return;
            
            if (!validateForm(form)) {
                e.preventDefault();
                showToast('Por favor, corrige los errores en el formulario', 'error');
            }
        });
    });
    
    // Add responsive handling
    window.addEventListener('resize', debounce(handleResize, 250));
    
    // Add loading states to buttons
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            if (form && validateForm(form)) {
                showLoading(this);
                
                // Reset loading state after form submission timeout
                setTimeout(() => {
                    hideLoading(this);
                }, 5000);
            }
        });
    });
    
    // Initialize password strength indicator (if present)
    const passwordField = document.getElementById('password');
    if (passwordField) {
        passwordField.addEventListener('input', debounce(updatePasswordStrength, 300));
    }
    
    console.log('Santiago Tickets App initialized successfully');
});

// Password strength indicator
function updatePasswordStrength() {
    const password = document.getElementById('password');
    const strengthIndicator = document.querySelector('.password-strength');
    
    if (!password || !strengthIndicator) return;
    
    const value = password.value;
    const strength = calculatePasswordStrength(value);
    
    const strengthFill = strengthIndicator.querySelector('.strength-fill');
    const strengthText = strengthIndicator.querySelector('.strength-text');
    
    if (strengthFill && strengthText) {
        strengthFill.className = `strength-fill ${strength.level}`;
        strengthText.className = `strength-text ${strength.level}`;
        strengthText.textContent = strength.text;
    }
}

function calculatePasswordStrength(password) {
    let score = 0;
    
    // Length check
    if (password.length >= 8) score += 1;
    if (password.length >= 12) score += 1;
    
    // Character variety checks
    if (/[a-z]/.test(password)) score += 1;
    if (/[A-Z]/.test(password)) score += 1;
    if (/[0-9]/.test(password)) score += 1;
    if (/[^a-zA-Z0-9]/.test(password)) score += 1;
    
    const levels = {
        0: { level: 'weak', text: 'Muy débil' },
        1: { level: 'weak', text: 'Débil' },
        2: { level: 'fair', text: 'Regular' },
        3: { level: 'fair', text: 'Regular' },
        4: { level: 'good', text: 'Buena' },
        5: { level: 'strong', text: 'Fuerte' },
        6: { level: 'strong', text: 'Muy fuerte' }
    };
    
    return levels[Math.min(score, 6)];
}