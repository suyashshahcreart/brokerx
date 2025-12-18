// Photo Sphere Viewer - Only load if viewer containers exist
const viewerContainers = document.querySelectorAll('.viewer-container, #viewer1, #viewer2, #viewer3');
if (viewerContainers.length > 0) {
  const assetBase = (typeof window !== 'undefined' && window.__PROPPIK_ASSET_BASE) ? window.__PROPPIK_ASSET_BASE : '/proppik/';
  const normalize = (p) => {
    if (!p) return p;
    if (/^https?:\/\//i.test(p) || p.startsWith('/')) return p;
    // Theme assets live under /proppik/assets/...
    return assetBase.replace(/\/+$/, '/') + p.replace(/^\/+/, '');
  };
  // Dynamically import Photo Sphere Viewer only when needed
  Promise.all([
    import('@photo-sphere-viewer/core'),
    import('@photo-sphere-viewer/autorotate-plugin')
  ]).then(([{ Viewer }, { AutorotatePlugin }]) => {
    // Slide titles array
    const slideTitles = ['Panorama View 1', 'Panorama View 2', 'Panorama View 3'];

    // Initialize Swiper with better touch handling (main panorama slider)
    const sliderElement = document.querySelector('.slider-container .swiper');
    if (sliderElement) {
      const panoramaSwiper = new Swiper(sliderElement, {
        loop: true,
        speed: 1000,
        effect: 'fade',
        fadeEffect: {
          crossFade: true
        },
        allowTouchMove: true,
        grabCursor: false, // Don't show grab cursor, let viewer handle it
        // Only allow swipes from edges or with specific gestures
        touchEventsTarget: 'wrapper',
        threshold: 50, // Require more movement to trigger swipe
        touchAngle: 30, // Only horizontal swipes
        touchStartPreventDefault: false,
        // Use navigation buttons or keyboard for slide changes
        keyboard: {
          enabled: true,
        },
        // Navigation arrows
        navigation: {
          nextEl: '.slider-container .swiper-button-next',
          prevEl: '.slider-container .swiper-button-prev',
        },
      });

      // Array of panorama images
      const panoramas = [
        normalize('assets/images/babylon/original/360-1.jpg'),
        normalize('assets/images/kisan-canteen/original/360.jpg'),
        normalize('assets/images/kohinoor/original/360.jpg')
      ];
      const viewers = [];
      const viewerAutos = [];

      // Initialize all viewers
      panoramas.forEach((panorama, index) => {
        const viewerId = `viewer${index + 1}`;
        const container = document.getElementById(viewerId);
        
        if (container) {
          const viewer = new Viewer({
            container: container,
            panorama: panorama,
            plugins: [
              [AutorotatePlugin, {
                autorotateSpeed: '0.5rpm', // Slower rotation speed
                autorotateLat: null,
                autorotateDelay: 0,
                autorotateIdle: false,
              }]
            ],
            navbar: false, // Remove bottom navigation
            caption: '', // Remove caption
            loadingImg: false, // Remove loading indicator
            touchmoveTwoFingers: false, // Enable click and drag
            mousewheelCtrlKey: false,
            moveInertia: true,
            // Enable mouse and touch controls
            mousemove: true,
            mousewheel: false,
          });

          viewers.push(viewer);
          const auto = viewer.getPlugin(AutorotatePlugin);
          if (auto) {
            viewerAutos.push(auto);
          }
        }
      });

      // Handle slide change to resize viewers
      panoramaSwiper.on('slideChange', () => {
        viewers.forEach(viewer => {
          viewer.resize();
        });
      });

      // Pause / resume autorotate based on scroll position within the slider section
      const sliderSection = document.querySelector('.slider-container');
      if (sliderSection && viewerAutos.length) {
        const toggleAutoRotate = () => {
          const threshold = sliderSection.offsetTop + 150;
          const shouldStop = window.scrollY > threshold;
          viewerAutos.forEach(auto => {
            if (shouldStop) {
              auto.stop();
            } else {
              auto.start();
            }
          });
        };

        window.addEventListener('scroll', toggleAutoRotate, { passive: true });
        // Initial check
        toggleAutoRotate();
      }

      // Better event handling to prevent Swiper from interfering with viewer
      viewers.forEach((viewer, index) => {
        const container = document.getElementById(`viewer${index + 1}`);
        if (container) {
          let isDraggingViewer = false;
          let startX = 0;
          let startY = 0;
          
          // Track when user is dragging the viewer
          container.addEventListener('mousedown', (e) => {
            isDraggingViewer = true;
            startX = e.clientX;
            startY = e.clientY;
            panoramaSwiper.disable();
          });

          container.addEventListener('touchstart', (e) => {
            isDraggingViewer = true;
            if (e.touches.length > 0) {
              startX = e.touches[0].clientX;
              startY = e.touches[0].clientY;
            }
            panoramaSwiper.disable();
          });

          // Re-enable Swiper when user releases
          container.addEventListener('mouseup', () => {
            setTimeout(() => {
              isDraggingViewer = false;
              panoramaSwiper.enable();
            }, 50);
          });

          container.addEventListener('touchend', () => {
            setTimeout(() => {
              isDraggingViewer = false;
              panoramaSwiper.enable();
            }, 50);
          });

          // Allow horizontal swipes on edges to change slides
          container.addEventListener('touchmove', (e) => {
            if (isDraggingViewer && e.touches.length > 0) {
              const deltaX = Math.abs(e.touches[0].clientX - startX);
              const deltaY = Math.abs(e.touches[0].clientY - startY);
              const isHorizontalSwipe = deltaX > deltaY && deltaX > 50;
              
              // If it's a clear horizontal swipe from edge, allow Swiper
              if (isHorizontalSwipe && (startX < 50 || startX > window.innerWidth - 50)) {
                panoramaSwiper.enable();
              }
            }
          });
        }
      });
    }
  }).catch(error => {
    console.warn('Photo Sphere Viewer failed to load:', error);
  });
}

// Initialize Testimonials Swiper
const testimonialsSwiper = new Swiper('.testimonials-swiper', {
  slidesPerView: 1,
  spaceBetween: 25,
  loop: true,
  loopedSlides: 6,
  loopAdditionalSlides: 3,
  speed: 1000,
  autoplay: {
    delay: 3000,
    disableOnInteraction: false,
    pauseOnMouseEnter: false,
  },
  direction: 'horizontal',
  breakpoints: {
    640: {
      slidesPerView: 2,
      spaceBetween: 25,
    },
    1024: {
      slidesPerView: 3,
      spaceBetween: 30,
    },
    1400: {
      slidesPerView: 4,
      spaceBetween: 30,
    },
  },
  effect: 'slide',
  grabCursor: true,
  freeMode: false,
  watchSlidesProgress: true,
  watchSlidesVisibility: true,
});



// Floating Section Close Button
const closeFloatingBtn = document.querySelector('.btn-close-floating');
if (closeFloatingBtn) {
  closeFloatingBtn.addEventListener('click', function() {
    const floatingSection = document.querySelector('.floating-section');
    if (floatingSection) {
      floatingSection.style.transform = 'translateX(-50%) translateY(100px)';
      floatingSection.style.opacity = '0';
      setTimeout(() => {
        floatingSection.style.display = 'none';
      }, 300);
    }
  });
}

// Initialize Shutter Accordion
document.addEventListener('DOMContentLoaded', function() {
  const accordion = document.getElementById('shutterAccordion');
  if (accordion) {
    const panels = accordion.querySelectorAll('.shutter-panel');
    
    panels.forEach(panel => {
      panel.addEventListener('click', () => {
        // Check if the clicked panel is already active
        if (panel.classList.contains('active')) {
          return; // Do nothing if already active
        }
        
        // Remove 'active' class from the currently active panel
        const currentActive = accordion.querySelector('.shutter-panel.active');
        if (currentActive) {
          currentActive.classList.remove('active');
        }
        
        // Add 'active' class to the clicked panel
        panel.classList.add('active');
      });
    });
  }
  
  // Keep old accordion code for backward compatibility
  const accordionItems = document.querySelectorAll('.process-accordion .accordion-item');
  
  // Ensure at least one item is always active
  if (accordionItems.length > 0) {
    // If no item is active, activate the first one
    const hasActive = Array.from(accordionItems).some(item => item.classList.contains('active'));
    if (!hasActive) {
      accordionItems[0].classList.add('active');
    }
  }
  
  accordionItems.forEach(item => {
    item.addEventListener('click', function() {
      const isActive = this.classList.contains('active');
      const activeCount = Array.from(accordionItems).filter(accItem => accItem.classList.contains('active')).length;
      
      // If clicking on the only active item, don't close it
      if (isActive && activeCount === 1) {
        return; // Prevent closing the last active item
      }
      
      // Close all items
      accordionItems.forEach(accItem => {
        accItem.classList.remove('active');
      });
      
      // Open clicked item if it wasn't active, or keep it open if it was
      if (!isActive) {
        this.classList.add('active');
      } else {
        // If it was active and there were others, activate the first one instead
        accordionItems[0].classList.add('active');
      }
    });
  });
});

// Initialize Magnific Popup for Video
document.addEventListener('DOMContentLoaded', function() {
  // Wait for jQuery and Magnific Popup to load
  if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
      if (typeof $.magnificPopup !== 'undefined') {
        $('.popup-youtube').magnificPopup({
          disableOn: 700,
          type: 'iframe',
          mainClass: 'mfp-fade',
          removalDelay: 160,
          preloader: false,
          fixedContentPos: false,
          iframe: {
            patterns: {
              youtube: {
                index: 'youtube.com/',
                id: 'v=',
                src: 'https://www.youtube.com/embed/%id%?autoplay=1'
              }
            }
          }
        });
      } else {
        // Retry after a short delay if Magnific Popup hasn't loaded yet
        setTimeout(function() {
          if (typeof $.magnificPopup !== 'undefined') {
            $('.popup-youtube').magnificPopup({
              disableOn: 700,
              type: 'iframe',
              mainClass: 'mfp-fade',
              removalDelay: 160,
              preloader: false,
              fixedContentPos: false,
              iframe: {
                patterns: {
                  youtube: {
                    index: 'youtube.com/',
                    id: 'v=',
                    src: 'https://www.youtube.com/embed/%id%?autoplay=1'
                  }
                }
              }
            });
          }
        }, 500);
      }
    });
  }
});

// Contact Form Handler with reCAPTCHA v3 and AJAX
document.addEventListener('DOMContentLoaded', function() {
  const contactForm = document.getElementById('contactForm');
  const filterButtons = document.querySelectorAll('.btn-filter');
  const filteredTourItems = document.querySelectorAll('.filtered-tour-item');

  // Gallery filters (top tours + gallery tiles)
  if (filterButtons.length && filteredTourItems.length) {
    filterButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        this.classList.add('active');

        const filterValue = this.getAttribute('data-filter');

        // Filter items
        filteredTourItems.forEach(item => {
          if (filterValue === 'all') {
            item.classList.remove('hidden');
          } else {
            const itemCategory = item.getAttribute('data-category');
            if (itemCategory === filterValue) {
              item.classList.remove('hidden');
            } else {
              item.classList.add('hidden');
            }
          }
        });
      });
    });
  }

  if (contactForm) {
    // Restrict phone input to only digits and max 10 digits
    const phoneInput = document.getElementById('contactPerson');
    if (phoneInput) {
      phoneInput.addEventListener('input', function(e) {
        // Remove all non-digit characters
        let value = this.value.replace(/\D/g, '');
        // Limit to 10 digits
        if (value.length > 10) {
          value = value.substring(0, 10);
        }
        this.value = value;
      });
      
      // Prevent paste of non-numeric characters
      phoneInput.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const numbers = paste.replace(/\D/g, '').substring(0, 10);
        this.value = numbers;
      });
    }
    
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get form elements
      const submitBtn = document.getElementById('submitBtn');
      const submitText = submitBtn.querySelector('.submit-text');
      const submitLoader = submitBtn.querySelector('.submit-loader');
      const formMessage = document.getElementById('formMessage');
      
      // Hide previous messages
      formMessage.classList.add('d-none');
      formMessage.classList.remove('alert-success', 'alert-danger');
      
      // Show loader and disable button
      submitText.classList.add('d-none');
      submitLoader.classList.remove('d-none');
      submitBtn.disabled = true;
      
      // Get form data
      const formData = {
        fullName: document.getElementById('fullName').value.trim(),
        companyName: document.getElementById('companyName').value.trim(),
        contactPerson: document.getElementById('contactPerson').value.trim(),
        address: document.getElementById('address').value.trim(),
        requirement: document.getElementById('requirement').value.trim()
      };
      
      // Validate form - check required fields
      if (!formData.fullName || !formData.companyName || !formData.contactPerson || !formData.address || !formData.requirement) {
        showMessage('Please fill in all required fields.', 'danger');
        hideLoader();
        return;
      }
      
      // Validate phone number format - exactly 10 digits
      const cleanPhone = formData.contactPerson.replace(/\D/g, ''); // Remove all non-digits
      if (cleanPhone.length !== 10) {
        showMessage('Please enter a valid 10-digit phone number.', 'danger');
        hideLoader();
        return;
      }
      // Update formData with cleaned phone number
      formData.contactPerson = cleanPhone;
      
      // Check terms checkbox
      const agreeTerms = document.getElementById('agreeTerms');
      if (!agreeTerms.checked) {
        showMessage('Please agree to the Terms & Conditions and Privacy Policy.', 'danger');
        hideLoader();
        return;
      }
      
      // Execute reCAPTCHA v3
      if (typeof grecaptcha !== 'undefined') {
        grecaptcha.ready(function() {
          grecaptcha.execute('6LcpuycsAAAAAAJFaBunTz63ks3_lubeAQGfrb0z', { action: 'submit' }).then(function(token) {
            // Add token to form data
            formData.token = token;
            
            // Send AJAX request
            sendFormData(formData);
          }).catch(function(error) {
            console.error('reCAPTCHA error:', error);
            hideLoader();
            showMessage('reCAPTCHA verification failed. Please refresh the page and try again.', 'danger');
          });
        });
      } else {
        // If reCAPTCHA is not loaded, show error
        console.error('reCAPTCHA not loaded');
        hideLoader();
        showMessage('reCAPTCHA is not loaded. Please refresh the page and try again.', 'danger');
      }
      
      function sendFormData(data) {
        // Create FormData object
        const formDataObj = new FormData();
        Object.keys(data).forEach(key => {
          formDataObj.append(key, data[key]);
        });
        
        // Send AJAX request
        fetch('contactform.php', {
          method: 'POST',
          body: formDataObj
        })
        .then(response => {
          // Check if response is ok
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          // Try to parse JSON
          return response.text().then(text => {
            try {
              return JSON.parse(text);
            } catch (e) {
              // If not JSON, log the response
              console.error('Non-JSON response:', text);
              throw new Error('Invalid response from server');
            }
          });
        })
        .then(data => {
          hideLoader();
          
          if (data.success) {
            showMessage(data.message || 'Thank you! Your message has been sent successfully. We will get back to you soon.', 'success');
            contactForm.reset();
            
            // Scroll to message
            formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          } else {
            showMessage(data.message || 'Sorry, there was an error sending your message. Please try again later.', 'danger');
          }
        })
        .catch(error => {
          hideLoader();
          console.error('Error:', error);
          showMessage('Sorry, there was an error sending your message. Please check your connection and try again.', 'danger');
        });
      }
      
      function showMessage(message, type) {
        formMessage.textContent = message;
        formMessage.classList.remove('d-none');
        formMessage.classList.add('alert-' + type);
        formMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
      
      function hideLoader() {
        submitText.classList.remove('d-none');
        submitLoader.classList.add('d-none');
        submitBtn.disabled = false;
      }
    });
  }
});

// Set Current Year in Footer
document.addEventListener('DOMContentLoaded', function() {
  const yearElement = document.getElementById('currentYear');
  if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
  }
});

