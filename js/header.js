// header-sidebar-integration.js
// Add this script after your existing scripts

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
    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    body.appendChild(overlay);
    
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
    
    // Update main content classes
    function updateMainContent(state) {
        if (!mainContent) return;
        
        // Remove all sidebar-related classes
        mainContent.classList.remove('sidebar-collapsed', 'sidebar-hidden', 'transitioning');
        
        // Add transition class temporarily
        mainContent.classList.add('transitioning');
        
        // Apply new state
        switch(state) {
            case 'collapsed':
                mainContent.classList.add('sidebar-collapsed');
                break;
            case 'hidden':
                mainContent.classList.add('sidebar-hidden');
                break;
            case 'visible':
                // Default state - no additional classes needed
                break;
        }
        
        // Remove transition class after animation
        setTimeout(() => {
            mainContent.classList.remove('transitioning');
        }, 300);
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
                animateHamburger(false);
                updateMainContent('hidden');
            } else {
                // Show sidebar
                sidebar.classList.add('show');
                overlay.classList.add('show');
                body.style.overflow = 'hidden';
                animateHamburger(true);
                updateMainContent('visible');
            }
        } else {
            // Desktop behavior
            const currentlyCollapsed = isCollapsed();
            
            if (currentlyCollapsed) {
                // Expand sidebar
                sidebar.classList.remove('collapsed');
                animateHamburger(false);
                updateMainContent('visible');
                localStorage.setItem('sidebarCollapsed', 'false');
            } else {
                // Collapse sidebar
                sidebar.classList.add('collapsed');
                animateHamburger(true);
                updateMainContent('collapsed');
                localStorage.setItem('sidebarCollapsed', 'true');
            }
        }
        
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
            sidebar.classList.add('hidden');
            animateHamburger(false);
            updateMainContent('hidden');
        } else {
            // Desktop initialization
            sidebar.classList.remove('hidden');
            const savedState = localStorage.getItem('sidebarCollapsed');
            const shouldCollapse = savedState === 'true';
            
            if (shouldCollapse) {
                sidebar.classList.add('collapsed');
                animateHamburger(true);
                updateMainContent('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                animateHamburger(false);
                updateMainContent('visible');
            }
        }
    }
    
    // Close mobile sidebar
    function closeMobileSidebar() {
        if (!isMobile()) return;
        
        sidebar?.classList.remove('show');
        overlay.classList.remove('show');
        body.style.overflow = '';
        animateHamburger(false);
        updateMainContent('hidden');
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
    
    // Smooth transitions for content when sidebar toggles
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && 
                mutation.attributeName === 'class' && 
                mutation.target === sidebar) {
                
                // Trigger reflow for smooth transitions
                if (mainContent) {
                    mainContent.style.transform = 'translateZ(0)';
                    requestAnimationFrame(() => {
                        mainContent.style.transform = '';
                    });
                }
            }
        });
    });
    
    if (sidebar) {
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
    
    // Performance optimization: throttle scroll events
    let ticking = false;
    function updateScrollProgress() {
        const progressFill = document.querySelector('.progress-fill');
        if (progressFill) {
            const scrolled = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
            progressFill.style.width = Math.min(scrolled, 100) + '%';
        }
        ticking = false;
    }
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateScrollProgress);
            ticking = true;
        }
    });
    
    // Expose utility functions globally
    window.SidebarController = {
        toggle: toggleSidebar,
        close: closeMobileSidebar,
        isCollapsed: isCollapsed,
        isVisible: isVisible,
        isMobile: isMobile
    };
    
    // Debug info
    console.log('Header-Sidebar integration loaded:', {
        sidebar: !!sidebar,
        toggle: !!sidebarToggle,
        mainContent: !!mainContent,
        hamburgerLines: hamburgerLines.length,
        isMobile: isMobile()
    });
});

// Additional CSS injection for dynamic styles
const dynamicStyles = `
    .main-content {
        --sidebar-width: 260px;
        --sidebar-collapsed-width: 60px;
    }
    
    .main-content.transitioning * {
        pointer-events: none;
    }
    
    @media (prefers-reduced-motion: reduce) {
        .main-content,
        .sidebar,
        .hamburger-line {
            transition: none !important;
        }
    }
`;

// Fix User Dropdown
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan dropdown user berfungsi
    const userDropdown = document.querySelector('.user-dropdown');
    const dropdownMenu = document.querySelector('.custom-dropdown');
    
    if (userDropdown && dropdownMenu) {
      userDropdown.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Toggle dropdown
        dropdownMenu.classList.toggle('show');
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!userDropdown.contains(e.target)) {
          dropdownMenu.classList.remove('show');
        }
      });
      
      // Prevent dropdown from closing when clicking inside
      dropdownMenu.addEventListener('click', function(e) {
        e.stopPropagation();
      });
    }
  });

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = dynamicStyles;
document.head.appendChild(styleSheet);