// header-sidebar-integration.js - IMPROVED VERSION
// Menangani integrasi antara header, sidebar, dan main content

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
    if (sidebar) {
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
    
    // Debug info
    console.log('✅ Header-Sidebar integration loaded:', {
        sidebar: !!sidebar,
        toggle: !!sidebarToggle,
        mainContent: !!mainContent,
        hamburgerLines: hamburgerLines.length,
        isMobile: isMobile(),
        overlay: !!overlay
    });
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