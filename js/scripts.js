// header-sidebar-integration.js - IMPROVED VERSION
// Menangani integrasi antara header, sidebar, dan main content

/* global Chart, IntersectionObserver */
// Deklarasi variabel global untuk menghindari warning

document.addEventListener('DOMContentLoaded', function() {
    
    // Elements
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    const hamburgerLines = document.querySelectorAll('.hamburger-line');
    const body = document.body;
    
    // State management
    let isTransitioning = false;
    
    // Create overlay for mobile
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.id = 'sidebarOverlay';
        body.appendChild(overlay);
    }
    
    // Utility functions
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    function isCollapsed() {
        return sidebar?.classList.contains('collapsed');
    }
    
    function isVisible() {
        return sidebar?.classList.contains('show') || 
               (!sidebar?.classList.contains('hidden') && window.innerWidth > 768);
    }
    
    // Animate hamburger menu
    function animateHamburger(isActive) {
        if (!hamburgerLines.length) return;
        
        sidebarToggle?.classList.toggle('active', isActive);
        
        if (isActive) {
            hamburgerLines[0].style.transform = 'translateY(6px) rotate(45deg)';
            hamburgerLines[1].style.opacity = '0';
            hamburgerLines[1].style.transform = 'translateX(20px)';
            hamburgerLines[2].style.transform = 'translateY(-6px) rotate(-45deg)';
        } else {
            hamburgerLines[0].style.transform = '';
            hamburgerLines[1].style.opacity = '1';
            hamburgerLines[1].style.transform = '';
            hamburgerLines[2].style.transform = '';
        }
    }
    
    // Update main content margins
    function updateMainContent() {
        if (!mainContent) return;
        
        if (isMobile()) {
            // Mobile: always full width
            mainContent.style.marginLeft = '0';
        } else {
            // Desktop: adjust based on sidebar state
            if (isCollapsed()) {
                mainContent.style.marginLeft = '60px';
            } else {
                mainContent.style.marginLeft = '260px';
            }
        }
    }
    
    // Toggle sidebar with proper state management
    function toggleSidebar() {
        if (isTransitioning) return;
        isTransitioning = true;
        
        if (isMobile()) {
            // Mobile behavior
            const isShowing = sidebar.classList.contains('show');
            
            if (isShowing) {
                // Hide sidebar
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                body.style.overflow = '';
                body.classList.remove('sidebar-open');
                animateHamburger(false);
            } else {
                // Show sidebar
                sidebar.classList.add('show');
                overlay.classList.add('show');
                body.style.overflow = 'hidden';
                body.classList.add('sidebar-open');
                animateHamburger(true);
            }
        } else {
            // Desktop behavior
            const currentlyCollapsed = isCollapsed();
            
            if (currentlyCollapsed) {
                // Expand sidebar
                sidebar.classList.remove('collapsed');
                animateHamburger(false);
                localStorage.setItem('sidebarCollapsed', 'false');
            } else {
                // Collapse sidebar
                sidebar.classList.add('collapsed');
                animateHamburger(true);
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        }
        
        // Update main content
        updateMainContent();
        
        // Reset transition lock
        setTimeout(() => {
            isTransitioning = false;
        }, 350);
    }
    
    // Initialize sidebar state
    function initializeSidebar() {
        if (!sidebar) return;
        
        if (isMobile()) {
            // Mobile initialization
            sidebar.classList.remove('collapsed');
            sidebar.classList.remove('show');
            animateHamburger(false);
        } else {
            // Desktop initialization
            sidebar.classList.remove('hidden', 'show');
            const savedState = localStorage.getItem('sidebarCollapsed');
            const shouldCollapse = savedState === 'true';
            
            if (shouldCollapse) {
                sidebar.classList.add('collapsed');
                animateHamburger(true);
            } else {
                sidebar.classList.remove('collapsed');
                animateHamburger(false);
            }
        }
        
        // Update main content
        updateMainContent();
    }
    
    // Close mobile sidebar
    function closeMobileSidebar() {
        if (!isMobile()) return;
        
        sidebar?.classList.remove('show');
        overlay.classList.remove('show');
        body.style.overflow = '';
        body.classList.remove('sidebar-open');
        animateHamburger(false);
    }
    
    // Event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Overlay click to close mobile sidebar
    overlay.addEventListener('click', closeMobileSidebar);
    
    // Close mobile sidebar when clicking nav links
    const navLinks = document.querySelectorAll('.sidebar-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (isMobile()) {
                setTimeout(closeMobileSidebar, 150);
            }
        });
    });
    
    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            const wasMobile = body.classList.contains('mobile-view');
            const isNowMobile = isMobile();
            
            if (isNowMobile !== wasMobile) {
                body.classList.toggle('mobile-view', isNowMobile);
                closeMobileSidebar();
                initializeSidebar();
            } else {
                // Just update main content margins
                updateMainContent();
            }
        }, 250);
    });
    
    // Escape key to close mobile sidebar
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isMobile() && isVisible()) {
            closeMobileSidebar();
        }
    });
    
    // Prevent sidebar close when clicking inside
    sidebar?.addEventListener('click', function(e) {
        e.stopPropagation();
    });
    
    // Auto-close mobile sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (isMobile() && 
            isVisible() && 
            !sidebar.contains(e.target) && 
            !sidebarToggle?.contains(e.target)) {
            closeMobileSidebar();
        }
    });
    
    // Observe sidebar class changes for animations
    if (sidebar && typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && 
                    mutation.attributeName === 'class') {
                    updateMainContent();
                }
            });
        });
        
        observer.observe(sidebar, { attributes: true });
    }
    
    // Initialize everything
    body.classList.toggle('mobile-view', isMobile());
    initializeSidebar();
    
    // Set active navigation link
    function setActiveLink() {
        const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
        navLinks.forEach(link => {
            link.classList.remove('active');
            const href = link.getAttribute('href');
            if (href && (href === currentPage || href.includes(currentPage))) {
                link.classList.add('active');
            }
        });
    }
    
    setActiveLink();
    
    // Expose utility functions globally
    window.SidebarController = {
        toggle: toggleSidebar,
        close: closeMobileSidebar,
        isCollapsed: isCollapsed,
        isVisible: isVisible,
        isMobile: isMobile,
        updateMainContent: updateMainContent
    };
    
    // Debug info (only in development)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
        console.log('✅ Header-Sidebar integration loaded:', {
            sidebar: !!sidebar,
            toggle: !!sidebarToggle,
            mainContent: !!mainContent,
            hamburgerLines: hamburgerLines.length,
            isMobile: isMobile(),
            overlay: !!overlay
        });
    }
});

// Additional CSS injection for smooth transitions
const dynamicStyles = `
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1039;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .sidebar-overlay.show {
        display: block;
        opacity: 1;
    }
    
    .main-content {
        transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    body.sidebar-open {
        overflow: hidden;
    }
    
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0 !important;
        }
    }
    
    @media (prefers-reduced-motion: reduce) {
        .main-content,
        .sidebar,
        .hamburger-line,
        .sidebar-overlay {
            transition: none !important;
        }
    }
`;

// Inject styles if not already present
if (!document.getElementById('sidebar-dynamic-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'sidebar-dynamic-styles';
    styleSheet.textContent = dynamicStyles;
    document.head.appendChild(styleSheet);
}

// ========================================
// UTILITY FUNCTIONS - Safe Wrappers
// ========================================

/**
 * Safe Chart.js initialization helper
 * Prevents "Chart is not defined" errors
 * 
 * @param {string} canvasId - ID of canvas element
 * @param {object} config - Chart.js configuration object
 * @returns {Chart|null} Chart instance or null if failed
 * 
 * @example
 * const myChart = window.initializeChart('salesChart', {
 *     type: 'bar',
 *     data: { labels: ['Jan', 'Feb'], datasets: [{ data: [10, 20] }] }
 * });
 */
window.initializeChart = function(canvasId, config) {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js library not loaded. Please include Chart.js before using charts.');
        return null;
    }
    
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        console.warn(`Canvas element with id "${canvasId}" not found.`);
        return null;
    }
    
    try {
        return new Chart(canvas.getContext('2d'), config);
    } catch (error) {
        console.error('Error initializing chart:', error);
        return null;
    }
};

/**
 * Safe IntersectionObserver helper with fallback
 * Prevents "IntersectionObserver is not defined" errors
 * 
 * @param {function} callback - Callback function when intersection occurs
 * @param {object} options - IntersectionObserver options
 * @returns {object} IntersectionObserver instance or fallback object
 * 
 * @example
 * const observer = window.createIntersectionObserver((entries) => {
 *     entries.forEach(entry => {
 *         if (entry.isIntersecting) {
 *             entry.target.classList.add('visible');
 *         }
 *     });
 * }, { threshold: 0.5 });
 * 
 * observer.observe(document.querySelector('.animate-me'));
 */
window.createIntersectionObserver = function(callback, options) {
    if (typeof IntersectionObserver === 'undefined') {
        console.warn('IntersectionObserver not supported in this browser. Using fallback.');
        // Fallback: immediately trigger callback for all elements
        return {
            observe: function(element) {
                if (typeof callback === 'function') {
                    callback([{ target: element, isIntersecting: true }]);
                }
            },
            unobserve: function() {},
            disconnect: function() {}
        };
    }
    
    try {
        return new IntersectionObserver(callback, options);
    } catch (error) {
        console.error('Error creating IntersectionObserver:', error);
        return {
            observe: function() {},
            unobserve: function() {},
            disconnect: function() {}
        };
    }
};

/**
 * Format date/time safely with multiple format options
 * Prevents date parsing errors
 * 
 * @param {string|Date} dateString - Date string or Date object
 * @param {string} format - Format type: 'date', 'datetime', 'datetime-full', 'time', 'time-full', 'iso', 'locale'
 * @returns {string} Formatted date string or original string if invalid
 * 
 * @example
 * window.formatDateTime('2024-02-14 13:45:30', 'datetime');  // "14/02/2024 13:45"
 * window.formatDateTime('2024-02-14', 'date');               // "14/02/2024"
 * window.formatDateTime(new Date(), 'time');                 // "13:45"
 */
window.formatDateTime = function(dateString, format) {
    if (!dateString) return '-';
    
    format = format || 'datetime'; // Default format
    
    try {
        const date = dateString instanceof Date ? dateString : new Date(dateString);
        
        // Check if date is valid
        if (isNaN(date.getTime())) {
            console.warn('Invalid date:', dateString);
            return String(dateString);
        }
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        
        // Format options
        const formats = {
            'date': `${day}/${month}/${year}`,
            'datetime': `${day}/${month}/${year} ${hours}:${minutes}`,
            'datetime-full': `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`,
            'time': `${hours}:${minutes}`,
            'time-full': `${hours}:${minutes}:${seconds}`,
            'iso': date.toISOString(),
            'locale': date.toLocaleString('id-ID'),
            'locale-date': date.toLocaleDateString('id-ID'),
            'locale-time': date.toLocaleTimeString('id-ID')
        };
        
        return formats[format] || formats['datetime'];
    } catch (error) {
        console.error('Error formatting date:', error);
        return String(dateString);
    }
};

/**
 * Escape HTML to prevent XSS attacks
 * 
 * @param {string} text - Text to escape
 * @returns {string} Escaped HTML string
 * 
 * @example
 * window.escapeHtml('<script>alert("XSS")</script>');
 * // Returns: "&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;"
 */
window.escapeHtml = function(text) {
    if (!text) return '';
    
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return String(text).replace(/[&<>"']/g, function(m) {
        return map[m];
    });
};

/**
 * Show alert notification with auto-dismiss
 * 
 * @param {string} type - Alert type: 'success', 'danger', 'warning', 'info'
 * @param {string} message - Alert message (can contain HTML)
 * @param {number} duration - Auto-dismiss duration in ms (default: 5000)
 * @param {string} containerId - Container element ID (default: 'alertContainer')
 * 
 * @example
 * window.showAlert('success', 'Data berhasil disimpan!', 3000);
 * window.showAlert('danger', '<strong>Error!</strong> Gagal menyimpan data.', 5000);
 */
window.showAlert = function(type, message, duration, containerId) {
    duration = duration || 5000;
    containerId = containerId || 'alertContainer';
    
    const validTypes = ['success', 'danger', 'warning', 'info'];
    if (!validTypes.includes(type)) {
        type = 'info';
    }
    
    const alertClass = `alert-${type}`;
    const iconMap = {
        'success': 'fa-check-circle',
        'danger': 'fa-exclamation-circle',
        'warning': 'fa-exclamation-triangle',
        'info': 'fa-info-circle'
    };
    
    const html = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconMap[type]} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    let container = document.getElementById(containerId);
    if (!container) {
        container = document.createElement('div');
        container.id = containerId;
        container.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 9999; max-width: 400px;';
        document.body.appendChild(container);
    }
    
    container.insertAdjacentHTML('beforeend', html);
    
    const alertElement = container.lastElementChild;
    
    // Auto dismiss
    setTimeout(() => {
        if (alertElement && alertElement.parentElement) {
            alertElement.classList.remove('show');
            setTimeout(() => {
                if (alertElement.parentElement) {
                    alertElement.remove();
                }
            }, 150);
        }
    }, duration);
};

/**
 * Debounce function to limit function calls
 * 
 * @param {function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {function} Debounced function
 * 
 * @example
 * const debouncedSearch = window.debounce(function() {
 *     console.log('Searching...');
 * }, 500);
 * 
 * input.addEventListener('keyup', debouncedSearch);
 */
window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Throttle function to limit function execution rate
 * 
 * @param {function} func - Function to throttle
 * @param {number} limit - Time limit in milliseconds
 * @returns {function} Throttled function
 * 
 * @example
 * const throttledScroll = window.throttle(function() {
 *     console.log('Scrolling...');
 * }, 100);
 * 
 * window.addEventListener('scroll', throttledScroll);
 */
window.throttle = function(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
};

/**
 * Format number with thousands separator
 * 
 * @param {number} num - Number to format
 * @param {number} decimals - Number of decimal places (default: 0)
 * @returns {string} Formatted number string
 * 
 * @example
 * window.formatNumber(1234567);        // "1.234.567"
 * window.formatNumber(1234.5678, 2);   // "1.234,57"
 */
window.formatNumber = function(num, decimals) {
    decimals = decimals || 0;
    
    if (isNaN(num)) return '0';
    
    const parts = parseFloat(num).toFixed(decimals).split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    
    return parts.join(',');
};

/**
 * Format currency in Indonesian Rupiah
 * 
 * @param {number} amount - Amount to format
 * @param {boolean} showPrefix - Show "Rp" prefix (default: true)
 * @returns {string} Formatted currency string
 * 
 * @example
 * window.formatCurrency(1234567);          // "Rp 1.234.567"
 * window.formatCurrency(1234567, false);   // "1.234.567"
 */
window.formatCurrency = function(amount, showPrefix) {
    showPrefix = showPrefix !== false;
    const formatted = window.formatNumber(amount, 0);
    return showPrefix ? `Rp ${formatted}` : formatted;
};

// Log successful initialization
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    console.log('✅ All utility functions loaded successfully');
    console.log('📦 Available global functions:', {
        SidebarController: 'Object with sidebar control methods',
        initializeChart: 'Safe Chart.js wrapper',
        createIntersectionObserver: 'Safe IntersectionObserver wrapper',
        formatDateTime: 'Date/time formatter',
        escapeHtml: 'HTML escaper for XSS prevention',
        showAlert: 'Bootstrap alert helper',
        debounce: 'Debounce utility',
        throttle: 'Throttle utility',
        formatNumber: 'Number formatter',
        formatCurrency: 'Currency formatter (IDR)'
    });
}