// Mobile Menu Slider Functionality - Standalone Version
// This ensures the mobile menu works independently

(function() {
  'use strict';
  
  function initMobileMenu() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenuSlider = document.getElementById('mobileMenuSlider');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const mobileSubmenuItems = document.querySelectorAll('.mobile-menu-item-has-submenu');
    const body = document.body;

    // Check if elements exist
    if (!mobileMenuToggle || !mobileMenuSlider || !mobileMenuOverlay) {
      console.warn('Mobile menu elements not found');
      return false;
    }

    // Handle submenu toggles - Improved version with better event handling
    const submenuHandlers = new Map(); // Store handlers to prevent duplicates
    
    function setupSubmenus() {
      if (mobileSubmenuItems.length > 0) {
        mobileSubmenuItems.forEach((item, index) => {
          const link = item.querySelector('.mobile-menu-link-with-arrow');
          if (link) {
            // Remove old handler if exists
            if (submenuHandlers.has(link)) {
              const oldHandler = submenuHandlers.get(link);
              link.removeEventListener('click', oldHandler.clickHandler, true);
              link.removeEventListener('touchend', oldHandler.touchHandler, { passive: false });
            }
            
            // Create a unique handler for each item
            const clickHandler = function(e) {
              e.preventDefault();
              e.stopPropagation();
              e.stopImmediatePropagation();
              
              const isActive = item.classList.contains('active');
              
              // Close all other submenus
              mobileSubmenuItems.forEach(otherItem => {
                if (otherItem !== item) {
                  otherItem.classList.remove('active');
                }
              });
              
              // Toggle current submenu
              if (isActive) {
                item.classList.remove('active');
              } else {
                item.classList.add('active');
              }
            };
            
            const touchHandler = function(e) {
              e.preventDefault();
              clickHandler(e);
            };
            
            // Store handlers
            submenuHandlers.set(link, { clickHandler, touchHandler });
            
            // Add event listeners
            link.addEventListener('click', clickHandler, true); // Use capture phase
            link.addEventListener('touchend', touchHandler, { passive: false });
          }
        });
      }
    }

    // Function to open mobile menu
    function openMobileMenu() {
      // Only open on mobile/tablet screens (less than 992px)
      if (window.innerWidth >= 992) {
        return;
      }
      mobileMenuToggle.classList.add('active');
      mobileMenuSlider.classList.add('active');
      mobileMenuOverlay.classList.add('active');
      body.style.overflow = 'hidden';
      // Re-setup submenus after menu opens
      setTimeout(setupSubmenus, 100);
    }

    // Function to close mobile menu
    function closeMobileMenu() {
      mobileMenuToggle.classList.remove('active');
      mobileMenuSlider.classList.remove('active');
      mobileMenuOverlay.classList.remove('active');
      body.style.overflow = '';
      
      // Close all submenus when closing main menu
      mobileSubmenuItems.forEach(item => {
        item.classList.remove('active');
      });
    }

    // Toggle mobile menu - with proper event handling
    mobileMenuToggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      
      // Only toggle on mobile/tablet screens
      if (window.innerWidth >= 992) {
        return;
      }
      
      if (mobileMenuSlider.classList.contains('active')) {
        closeMobileMenu();
      } else {
        openMobileMenu();
      }
    }, false);

    // Close mobile menu button
    if (mobileMenuClose) {
      mobileMenuClose.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        closeMobileMenu();
      }, false);
    }

    // Close menu when clicking overlay
    mobileMenuOverlay.addEventListener('click', function(e) {
      if (e.target === mobileMenuOverlay) {
        closeMobileMenu();
      }
    }, false);

    // Setup submenus initially (already defined above)
    setupSubmenus();

    // Close menu when clicking on a link (for smooth navigation)
    const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link:not(.mobile-menu-link-with-arrow), .mobile-submenu-link');
    mobileMenuLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        // Small delay to allow navigation
        setTimeout(() => {
          closeMobileMenu();
        }, 300);
      }, false);
    });

    // Close menu on window resize (if resizing to desktop)
    let resizeTimer;
    window.addEventListener('resize', function() {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function() {
        if (window.innerWidth >= 992) {
          closeMobileMenu();
        }
      }, 250);
    }, false);

    // Prevent body scroll when menu is open
    mobileMenuSlider.addEventListener('touchmove', function(e) {
      if (this.scrollTop === 0 && e.touches && e.touches[0] && e.touches[0].clientY > 0) {
        e.preventDefault();
      }
    }, { passive: false });

    return true;
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMobileMenu);
  } else {
    // DOM is already ready
    initMobileMenu();
  }

  // Also try to initialize after a short delay (fallback)
  setTimeout(function() {
    if (!document.querySelector('.mobile-menu-toggle.active')) {
      initMobileMenu();
    }
  }, 100);
})();
