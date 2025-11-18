/* ----------------------------------------------------------------

[ Custom settings ]

1. ScrollIt
2. Preloader
3. Navbar scrolling background
4. Close navbar-collapse when a clicked
5. Close mobile menu "on click"
6. Smooth Scrolling
7. Background Image
8. Slider & Slider-Fade owlCarousel
9. Team owlCarousel
10. Services owlCarousel
11. Services 2 owlCarousel
12. Pricing owlCarousel
13. Gallery owlCarousel for Portfolio
14. Testimonials owlCarousel
15. MagnificPopup
16. YouTubePopUp
17. Parallaxie
18. Progress Skills
19. Tooltip
20. Woe Animated
21. Splitting Text
22. Accordion Box
23. Reveal Effect
24. Magnet Cursor
25. Mouse Cursor
26. Contact Form
27. Scroll back to top
28. Portfolio - Observers
29. Process 2
30. Gallery 2
31. Isotope Active Masonry Gallery
32. Skills Bar

------------------------------------------------------------------- */
(function () {
    'use strict';
    var wind = $(window);
    
    /*----------------------------------------------------
      1. ScrollIt
    ----------------------------------------------------*/
    $.scrollIt({
        upKey: 38
        , downKey: 40
        , easing: 'ease-in-out'
        , scrollTime: 600
        , activeClass: 'active'
        , onPageChange: null
        , topOffset: -100
    });
    

    /*----------------------------------------------------
      2. Preloader
    ----------------------------------------------------*/
    var CustomApp = {
      init: function () {
        this.handlePreloader();
      },
      handlePreloader: function () {
        $(document).ready(function () {
          $("#preloader").fadeOut(500);
          $(".preloader-bg").delay(300).fadeOut(500);
        });
        setTimeout(function () {
          $("#preloader").fadeOut(500);
          $(".preloader-bg").delay(300).fadeOut(500);
        }, 2000);
      }
    };
    $(document).ready(function () {
      CustomApp.init();
    });
     
    /*----------------------------------------------------
      3. Navbar scrolling background
    ----------------------------------------------------*/
    wind.on("scroll", function () {
        var bodyScroll = wind.scrollTop()
            , navbar = $(".navbar")
            , logo = $(".navbar .logo> img");
        if (bodyScroll > 100) {
            navbar.addClass("nav-scroll");
            logo.attr('src', 'frontend/images/logo.png');
        }
        else {
            navbar.removeClass("nav-scroll");
            logo.attr('src', 'frontend/images/logo.png');
        }
    });
    
    /*----------------------------------------------------
      4. Close navbar-collapse when a clicked
    ----------------------------------------------------*/
    $(".navbar-nav .dropdown-item a").on('click', function () {
        $(".navbar-collapse").removeClass("show");
    });
    
    /*----------------------------------------------------
      5. Close mobile menu "on click"
    ----------------------------------------------------*/
    var navMain = $(".scroll-init");
    navMain.on("click", "a", null, function () {
        navMain.collapse('hide');
    });
    
    /*----------------------------------------------------
      6. Smooth Scrolling
    ----------------------------------------------------*/
    $('a[href*="#"]')
    .not('[href="#"]').not('[href="#0"]').click(function (event) {
    // On-page links
    if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                // Figure out element to scroll to
                var target = $(this.hash);
                target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                // Does a scroll target exist?
                if (target.length) {
                    // Only prevent default if animation is actually gonna happen
                    event.preventDefault();
                    $('html, body').animate({
                        scrollTop: target.offset().top
                    }, 1000, function () {
                        // Callback after animation
                        // Must change focus!
                        var $target = $(target);
                        $target.focus();
                        if ($target.is(":focus")) { // Checking if the target was focused
                            return false;
                        }
                        else {
                            $target.attr('tabindex', '-1'); // Adding tabindex for elements not focusable
                            $target.focus(); // Set focus again
                        };
                    });
                }
            }
        });
    
    /*----------------------------------------------------
      7. Background Image 
    ----------------------------------------------------*/
    var pageSection = $(".bg-img, section");
    pageSection.each(function (indx) {
        if ($(this).attr("data-background")) {
            $(this).css("background-image", "url(" + $(this).data("background") + ")");
        }
    });
    
    /*----------------------------------------------------
      8. Slider & Slider-Fade owlCarousel
    ----------------------------------------------------*/
    // $(document).ready(function () {
    //     var owl = $('.header .owl-carousel');
    //     // Slider owlCarousel - (Inner Page Slider)
    //     $('.slider .owl-carousel').owlCarousel({
    //         items: 1
    //         , loop: true
    //         , dots: true
    //         , margin: 0
    //         , autoplay: false
    //         , autoplayTimeout: 5000
    //         , nav: false
    //         , navText: ['<i class="ti-angle-left" aria-hidden="true"></i>', '<i class="ti-angle-right" aria-hidden="true"></i>']
    //         , responsiveClass: true
    //         , responsive: {
    //             0: {
    //                 dots: false
    //             , }
    //             , 600: {
    //                 dots: false
    //             , }
    //             , 1000: {
    //                 dots: false
    //             , }
    //         }
    //     });
    //     $('.slider-fade .owl-carousel').owlCarousel({
    //         items: 1
    //         , loop: true
    //         , dots: true
    //         , margin: 0
    //         , autoplay: true
    //         , autoplayTimeout: 5000
    //         , animateOut: 'fadeOut'
    //         , nav: false
    //         , navText: ['<i class="ti-angle-left" aria-hidden="true"></i>', '<i class="ti-angle-right" aria-hidden="true"></i>']
    //     });
    //     owl.on('changed.owl.carousel', function (event) {
    //         var item = event.item.index - 2; // Position of the current item
    //         $('h6').removeClass('animated fadeInUp');
    //         $('h1').removeClass('animated fadeInUp');
    //         $('.btn-wrap').removeClass('animated fadeInUp');
    //         $('.owl-item').not('.cloned').eq(item).find('h6').addClass('animated fadeInUp');
    //         $('.owl-item').not('.cloned').eq(item).find('h1').addClass('animated fadeInUp');
    //         $('.owl-item').not('.cloned').eq(item).find('.btn-wrap').addClass('animated fadeInUp');
    //     });
    // });
    
    /*----------------------------------------------------
      9. Team owlCarousel
    ----------------------------------------------------*/
    // $('.team .owl-carousel').owlCarousel({
    //     loop: true
    //     , margin: 30
    //     , dots: false
    //     , mouseDrag: true
    //     , autoplay: false
    //     , nav: false
    //     , navText: ["<span class='lnr ti-angle-left'></span>", "<span class='lnr ti-angle-right'></span>"]
    //     , responsiveClass: true
    //     , responsive: {
    //         0: {
    //             items: 1
    //             , dots: true
    //         }
    //         , 600: {
    //             items: 2
    //         }
    //         , 1000: {
    //             items: 4
    //         }
    //     }
    // });
    
    /*----------------------------------------------------
      10. Services owlCarousel
    ----------------------------------------------------*/
    // $('.services .owl-carousel').owlCarousel({
    //     loop: true
    //     , margin: 20
    //     , mouseDrag: true
    //     , autoplay: false
    //     , dots: false
    //     , autoplayHoverPause: true
    //     , nav: false
    //     , navText: ["<span class='lnr ti-angle-left'></span>", "<span class='lnr ti-angle-right'></span>"]
    //     , responsiveClass: true
    //     , responsive: {
    //         0: {
    //             items: 1
    //         }
    //         , 600: {
    //             items: 2
    //         }
    //         , 1000: {
    //             items: 4
    //         }
    //     }
    // });
    
    /*----------------------------------------------------
      11. Services 2 owlCarousel
    ----------------------------------------------------*/
    // $('.services2 .owl-carousel').owlCarousel({
    //     loop: true
    //     , margin: 15
    //     , mouseDrag: true
    //     , autoplay: false
    //     , dots: false
    //     , nav: false
    //     , navText: ["<span class='lnr ti-angle-left'></span>", "<span class='lnr ti-angle-right'></span>"]
    //     , autoplayHoverPause: true
    //     , responsiveClass: true
    //     , responsive: {
    //         0: {
    //             items: 1
    //         , }
    //         , 600: {
    //             items: 2
    //         }
    //         , 1000: {
    //             items: 3
    //         }
    //     }
    // });
    
    /*----------------------------------------------------
      12. Pricing owlCarousel
    ----------------------------------------------------*/
    // $('.price .owl-carousel').owlCarousel({
    //     loop: true
    //     , margin: 15
    //     , mouseDrag: true
    //     , autoplay: false
    //     , dots: false
    //     , nav: false
    //     , navText: ["<span class='lnr ti-angle-left'></span>", "<span class='lnr ti-angle-right'></span>"]
    //     , autoplayHoverPause: true
    //     , responsiveClass: true
    //     , responsive: {
    //         0: {
    //             items: 1
    //             , dots: true
    //         , }
    //         , 600: {
    //             items: 2
    //             , dots: true
    //         }
    //         , 1000: {
    //             items: 3
    //         }
    //     }
    // });
    
    /*----------------------------------------------------
      13. Gallery owlCarousel for Portfolio
    ----------------------------------------------------*/
    // $('.gallery-item .owl-carousel').owlCarousel({
    //     loop: true
    //     , margin: 15
    //     , mouseDrag: true
    //     , autoplay: false
    //     , dots: false
    //     , nav: false
    //     , navText: ['<i class="ti-arrow-left" aria-hidden="true"></i>', '<i class="ti-arrow-right" aria-hidden="true"></i>']
    //     , responsiveClass: true
    //     , responsive: {
    //         0: {
    //             items: 1
    //         , }
    //         , 600: {
    //             items: 1
    //         }
    //         , 1000: {
    //             items: 1
    //         }
    //     }
    // });
    
    /*----------------------------------------------------
      14. Testimonials owlCarousel
    ----------------------------------------------------*/
    $('.testimonials .owl-carousel').owlCarousel({
        loop: true
        , margin: 30
        , mouseDrag: true
        , autoplay: false
        , dots: false
        , nav: false
        , navText: ["<span class='lnr ti-angle-left'></span>", "<span class='lnr ti-angle-right'></span>"]
        , responsiveClass: true
        , responsive: {
            0: {
                items: 1
            , }
            , 600: {
                items: 1
            }
            , 1000: {
                items: 1
            }
        }
    });
    
    /*----------------------------------------------------
      15. MagnificPopup
    ----------------------------------------------------*/
    // $('.img-zoom').magnificPopup({
    //     type: "image"
    //     , closeOnContentClick: !0
    //     , mainClass: "mfp-fade"
    //     , gallery: {
    //         enabled: !0
    //         , navigateByImgClick: !0
    //         , preload: [0, 1]
    //     }
    // })
    // $('.magnific-youtube, .magnific-vimeo, .magnific-custom').magnificPopup({
    //     disableOn: 700
    //     , type: 'iframe'
    //     , mainClass: 'mfp-fade'
    //     , removalDelay: 300
    //     , preloader: false
    //     , fixedContentPos: false
    // });
    
    /*----------------------------------------------------
      16. YouTubePopUp
    ----------------------------------------------------*/
    // $("a.vid").YouTubePopUp();
    
    /*----------------------------------------------------
      17. Parallaxie
    ----------------------------------------------------*/
    $('.parallaxie').parallaxie({
        speed: 0.2
        , size: "cover"
    });
    
    /*----------------------------------------------------
      18. Progress Skills
    ----------------------------------------------------*/
    var c4 = $('.circle');
    var myVal = $(this).attr('data-value');
    $(".sk-progress .circle").each(function () {
        c4.circleProgress({
            startAngle: -Math.PI / 2 * 1
            , value: myVal
            , thickness: 4
            , fill: {
                gradient: ["#222222", "#777"]
            }
        });
    });
      
    /*----------------------------------------------------
      19. Tooltip
    ----------------------------------------------------*/
    $('[data-tooltip-tit]').hover(function () {
        $('<div class="div-tooltip-tit"></div>').text($(this).attr('data-tooltip-tit')).appendTo('body').fadeIn('slow');
    }, function () {
        $('.div-tooltip-tit').remove();
    }).mousemove(function (e) {
        $('.div-tooltip-tit').css({
            top: e.pageY + 10
            , left: e.pageX + 20
        })
    });
    $('[data-tooltip-sub]').hover(function () {
        $('<div class="div-tooltip-sub"></div>').text($(this).attr('data-tooltip-sub')).appendTo('body').fadeIn('slow');
    }, function () {
        $('.div-tooltip-sub').remove();
    }).mousemove(function (e) {
        $('.div-tooltip-sub').css({
            top: e.pageY + 60
            , left: e.pageX + 20
        })
    });
    
    /*----------------------------------------------------
      20. Woe Animated
    ----------------------------------------------------*/
    var wow = new WOW({
        animateClass: 'animated'
        , offset: 100
    });
    wow.init();
    
    /*----------------------------------------------------
      21. Splitting Text
    ----------------------------------------------------*/
    $(window).on("load", function () {
        Splitting();
    });
    
    /*----------------------------------------------------
      22. Accordion Box
    ----------------------------------------------------*/
    if ($(".accordion-box").length) {
        $(".accordion-box").on("click", ".acc-btn", function () {
            var outerBox = $(this).parents(".accordion-box");
            var target = $(this).parents(".accordion");
            if ($(this).next(".acc-content").is(":visible")) {
                //return false;
                $(this).removeClass("active");
                $(this).next(".acc-content").slideUp(300);
                $(outerBox).children(".accordion").removeClass("active-block");
            }
            else {
                $(outerBox).find(".accordion .acc-btn").removeClass("active");
                $(this).addClass("active");
                $(outerBox).children(".accordion").removeClass("active-block");
                $(outerBox).find(".accordion").children(".acc-content").slideUp(300);
                target.addClass("active-block");
                $(this).next(".acc-content").slideDown(300);
            }
        });
    }
    
    /*----------------------------------------------------
      23. Reveal Effect
    ----------------------------------------------------*/
    var scroll = window.requestAnimationFrame ||
    // IE Fallback
    function (callback) {
            window.setTimeout(callback, 3000)
        };
    var elementsToShow = document.querySelectorAll('.reveal-effect');
    function loop() {
        Array.prototype.forEach.call(elementsToShow, function (element) {
            if (isElementInViewport(element)) {
                element.classList.add('animated');
            }
        });
        scroll(loop);
    }
    // Call the loop for the first time
    loop();
    function isElementInViewport(el) {
        // special bonus for those using jQuery
        if (typeof jQuery === "function" && el instanceof jQuery) {
            el = el[0];
        }
        var rect = el.getBoundingClientRect();
        return (
            (rect.top <= 0 && rect.bottom >= 0) || (rect.bottom >= (window.innerHeight || document.documentElement.clientHeight) && rect.top <= (window.innerHeight || document.documentElement.clientHeight)) || (rect.top >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight)));
    }
    
    /*----------------------------------------------------
      24. Magnet Cursor
    ----------------------------------------------------*/
    function magnetize(el, e) {
        var mX = e.pageX
            , mY = e.pageY;
        const item = $(el);
        const customDist = item.data('dist') * 20 || 80;
        const centerX = item.offset().left + (item.width() / 2);
        const centerY = item.offset().top + (item.height() / 2);
        var deltaX = Math.floor((centerX - mX)) * -0.35;
        var deltaY = Math.floor((centerY - mY)) * -0.35;
        var distance = calculateDistance(item, mX, mY);
        if (distance < customDist) {
            TweenMax.to(item, 0.5, {
                y: deltaY
                , x: deltaX
                , scale: 1
            });
            item.addClass('magnet');
        }
        else {
            TweenMax.to(item, 0.6, {
                y: 0
                , x: 0
                , scale: 1
            });
            item.removeClass('magnet');
        }
    }
    function calculateDistance(elem, mouseX, mouseY) {
        return Math.floor(Math.sqrt(Math.pow(mouseX - (elem.offset().left + (elem.width() / 2)), 2) + Math.pow(mouseY - (elem.offset().top + (elem.height() / 2)), 2)));
    }
    function lerp(a, b, n) {
        return (1 - n) * a + n * b
    }
    
    /*----------------------------------------------------
      25. Mouse Cursor
    ----------------------------------------------------*/
    class Cursor {
        constructor() {
            this.bind()
                //seleziono la classe del cursore
            this.cursor = document.querySelector('.js-cursor')
            this.mouseCurrent = {
                x: 0
                , y: 0
            }
            this.mouseLast = {
                x: this.mouseCurrent.x
                , y: this.mouseCurrent.y
            }
            this.rAF = undefined
        }
        bind() {
      ['getMousePosition', 'run'].forEach((fn) => this[fn] = this[fn].bind(this))
        }
        getMousePosition(e) {
            this.mouseCurrent = {
                x: e.clientX
                , y: e.clientY
            }
        }
        run() {
            this.mouseLast.x = lerp(this.mouseLast.x, this.mouseCurrent.x, 0.2)
            this.mouseLast.y = lerp(this.mouseLast.y, this.mouseCurrent.y, 0.2)
            this.mouseLast.x = Math.floor(this.mouseLast.x * 100) / 100
            this.mouseLast.y = Math.floor(this.mouseLast.y * 100) / 100
            this.cursor.style.transform = `translate3d(${this.mouseLast.x}px, ${this.mouseLast.y}px, 0)`
            this.rAF = requestAnimationFrame(this.run)
        }
        requestAnimationFrame() {
            this.rAF = requestAnimationFrame(this.run)
        }
        addEvents() {
            window.addEventListener('mousemove', this.getMousePosition, false)
        }
        on() {
            this.addEvents()
            this.requestAnimationFrame()
        }
        init() {
            this.on()
        }
    }
    if ($('.js-cursor').length > 0) {
        const cursor = new Cursor()
        cursor.init();
        // Cursor Conditions
        $('.team .owl-theme .item, .services .owl-theme .item, .services2 .owl-theme .item, .portfolio .owl-theme .item, .testimonials .owl-theme .item, .gallery-item .item').hover(function () {
            $('.cursor').toggleClass('drag');
        });
        // Cursor Class Settings
        // $('a, ').hover(function () {
        // $('.cursor').toggleClass('light');
        // });
    }
    
    /*----------------------------------------------------
      26. Contact Form
    ----------------------------------------------------*/
    var form = $('.contact__form')
        , message = $('.contact__msg')
        , form_data;
    // success function
    function done_func(response) {
        message.fadeIn().removeClass('alert-danger').addClass('alert-success');
        message.text(response);
        setTimeout(function () {
            message.fadeOut();
        }, 2000);
        form.find('input:not([type="submit"]), textarea').val('');
    }
    // fail function
    function fail_func(data) {
        message.fadeIn().removeClass('alert-success').addClass('alert-success');
        message.text(data.responseText);
        setTimeout(function () {
            message.fadeOut();
        }, 2000);
    }
    form.submit(function (e) {
        e.preventDefault();
        form_data = $(this).serialize();
        $.ajax({
            type: 'POST'
            , url: form.attr('action')
            , data: form_data
        }).done(done_func).fail(fail_func);
    });
    
    /*----------------------------------------------------
      27. Scroll back to top
    ----------------------------------------------------*/
    var progressPath = document.querySelector('.progress-wrap path');
    var pathLength = progressPath.getTotalLength();
    progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
    progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
    progressPath.style.strokeDashoffset = pathLength;
    progressPath.getBoundingClientRect();
    progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';
    var updateProgress = function () {
        var scroll = $(window).scrollTop();
        var height = $(document).height() - $(window).height();
        var progress = pathLength - (scroll * pathLength / height);
        progressPath.style.strokeDashoffset = progress;
    }
    updateProgress();
    $(window).scroll(updateProgress);
    var offset = 150;
    var duration = 550;
    $(window).on('scroll', function () {
        if ($(this).scrollTop() > offset) {
            $('.progress-wrap').addClass('active-progress');
        }
        else {
            $('.progress-wrap').removeClass('active-progress');
        }
    });
    $('.progress-wrap').on('click', function (event) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop: 0
        }, duration);
        return false;
    });
    
    /*----------------------------------------------------
      28. Portfolio - Observers
    ----------------------------------------------------*/
    const faders = document.querySelectorAll(".fade-in");
    const sliders = document.querySelectorAll(".slide-in");
    const imageAnimation = document.querySelectorAll(".image-in");
    const appearOptions = {
        threshold: 0
        , rootMargin: "0px 0px -100px 0px"
    };
    const appearOnScroll = new IntersectionObserver(function (entries, appearOnScroll) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) {
                return;
            }
            else {
                entry.target.classList.add("appear");
                appearOnScroll.unobserve(entry.target);
            }
        });
    }, appearOptions);
    faders.forEach(fader => {
        appearOnScroll.observe(fader);
    });
    sliders.forEach(slider => {
        appearOnScroll.observe(slider);
    });
    imageAnimation.forEach(imageAnimation => {
        appearOnScroll.observe(imageAnimation);
    });
    
    /*----------------------------------------------------
      29. Process 2
    ----------------------------------------------------*/
    var element = $('.interactive');
    if (element.hasClass('process2')) {
        var items = element.find('.inner');
        if (items.length) {
            items.on({
                mouseenter: function () {
                    var index = $(this).data('index')
                        , targetImg = element.find(`.process2-image.img-${index}`);
                    if ($(this).hasClass('activate')) return;
                    items.removeClass('activate');
                    $(this).addClass('activate');
                    element.find('.process2-image').removeClass('show');
                    targetImg.addClass('show');
                }
                , mouseleave: function () {}
            });
        }
    }
    
    /*----------------------------------------------------
      30. Gallery 2
    ----------------------------------------------------*/
    function initScrollAnimations() {
        const faders = document.querySelectorAll(".fade-in");
        const sliders = document.querySelectorAll(".slide-in");
        const imageAnimation = document.querySelectorAll(".image-in");
        const appearOptions = {
            threshold: 0
            , rootMargin: "0px 0px -100px 0px"
        };
        const appearOnScroll = new IntersectionObserver(function (entries, appearOnScroll) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("appear");
                    appearOnScroll.unobserve(entry.target);
                }
            });
        }, appearOptions);
        faders.forEach(fader => {
            appearOnScroll.observe(fader);
        });
        sliders.forEach(slider => {
            appearOnScroll.observe(slider);
        });
        imageAnimation.forEach(image => {
            appearOnScroll.observe(image);
        });
    }
    initScrollAnimations();
    
    /*----------------------------------------------------
      31. Isotope Active Masonry Gallery
    ----------------------------------------------------*/
    // $('.gallery-items').imagesLoaded(function () {
    //     // Add isotope on click filter function
    //     $('.gallery-filter li').on('click', function () {
    //         $(".gallery-filter li").removeClass("active");
    //         $(this).addClass("active");
    //         var selector = $(this).attr('data-filter');
    //         $(".gallery-items").isotope({
    //             filter: selector
    //             , animationOptions: {
    //                 duration: 750
    //                 , easing: 'linear'
    //                 , queue: false
    //             , }
    //         });
    //         return false;
    //     });
    //     $(".gallery-items").isotope({
    //         itemSelector: '.single-item'
    //         , layoutMode: 'masonry'
    //     , });
    // });
    
    /*----------------------------------------------------
      32. Skills Bar
    ----------------------------------------------------*/
    window.addEventListener('DOMContentLoaded', () => {
        // Tüm bar elemanlarını seç
        const bars = document.querySelectorAll('#skills .bar');
        bars.forEach(bar => {
            // Bar'ın üstündeki p.bar-title içindeki .percent span'ını bul
            const barTitle = bar.previousElementSibling;
            if (!barTitle) return;
            const percentSpan = barTitle.querySelector('.percent');
            if (!percentSpan) return;
            // Yüzde metnini al, örn "90%"
            const percentText = percentSpan.textContent.trim();
            // Yüzdelik değeri sayıya çevir
            const percentValue = parseInt(percentText);
            if (!isNaN(percentValue)) {
                // Bar içindeki dolgu elemanını seç
                const fill = bar.querySelector('.bar-fill');
                // Genişliği ayarla, transition animasyonlu olacak
                setTimeout(() => {
                    fill.style.width = percentValue + '%';
                }, 100); // küçük gecikme animasyonun başlaması için
            }
        });
    });
    /*----------------------------------------------------
      33. Blog Feed
    ----------------------------------------------------*/
    const BLOG_API_ENDPOINT = 'https://proppik.com/blog/wp-json/wp/v2/posts?per_page=3&_embed&fields=id,title,link,excerpt,content,date,featured_media,yoast_head_json,_embedded';
    const BLOG_IMAGE_FALLBACK = 'images/about.jpg';
    
    const stripHTML = (htmlString) => {
        if (!htmlString) return '';
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlString;
        return tempDiv.textContent || tempDiv.innerText || '';
    };
    
    const truncateText = (text, limit = 140) => {
        if (!text) return '';
        const trimmed = text.trim();
        if (trimmed.length <= limit) return trimmed;
        return `${trimmed.substring(0, limit).trim()}…`;
    };
    
    const formatPostDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };
    
    const escapeAttribute = (value) => {
        if (!value) return '';
        return value.replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    };
    
    const getEmbeddedImage = (post) => {
        const embeddedMedia = post?._embedded?.['wp:featuredmedia'];
        if (!Array.isArray(embeddedMedia) || !embeddedMedia.length) return null;
        const media = embeddedMedia[0];
        if (!media || typeof media !== 'object' || media.code === 'rest_forbidden') return null;
        return media.media_details?.sizes?.medium?.source_url ||
            media.media_details?.sizes?.medium_large?.source_url ||
            media.media_details?.sizes?.large?.source_url ||
            media.source_url ||
            null;
    };
    
    const fetchFeaturedMediaMap = async (posts) => {
        const ids = new Set();
        posts.forEach(post => {
            if (!getEmbeddedImage(post) && post?.featured_media) {
                ids.add(post.featured_media);
            }
        });
        if (!ids.size) return new Map();
    
        const mediaMap = new Map();
        const idsArray = Array.from(ids);
        
        try {
            // Try fetching from collection endpoint first (avoids 401 on individual items)
            const includeParam = idsArray.join(',');
            const response = await fetch(`https://proppik.com/blog/wp-json/wp/v2/media?include=${includeParam}&_fields=id,source_url,media_details&per_page=100`);
            
            if (response.ok) {
                const mediaArray = await response.json();
                if (Array.isArray(mediaArray)) {
                    mediaArray.forEach(media => {
                        if (media && media.id) {
                            const image =
                                media.media_details?.sizes?.medium?.source_url ||
                                media.media_details?.sizes?.medium_large?.source_url ||
                                media.media_details?.sizes?.large?.source_url ||
                                media.source_url;
                            if (image) {
                                mediaMap.set(media.id, image);
                            }
                        }
                    });
                }
            } else {
                // Fallback: Try individual requests if collection endpoint fails
                await Promise.all(idsArray.map(async (id) => {
                    try {
                        const response = await fetch(`https://proppik.com/blog/wp-json/wp/v2/media/${id}?_fields=id,source_url,media_details`);
                        if (!response.ok) return;
                        const media = await response.json();
                        if (!media || media.code === 'rest_forbidden') return;
                        const image =
                            media.media_details?.sizes?.medium?.source_url ||
                            media.media_details?.sizes?.medium_large?.source_url ||
                            media.media_details?.sizes?.large?.source_url ||
                            media.source_url;
                        if (image) {
                            mediaMap.set(id, image);
                        }
                    } catch (error) {
                        console.error(`Failed to resolve media ${id}:`, error);
                    }
                }));
            }
        } catch (error) {
            console.error('Failed to fetch featured media:', error);
        }
    
        return mediaMap;
    };
    
    const extractFirstImageFromContent = (htmlString) => {
        if (!htmlString) return null;
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlString;
        const img = tempDiv.querySelector('img');
        if (!img) return null;
        return img.getAttribute('data-src') || img.getAttribute('src');
    };
    
    const getYoastImage = (post) => {
        const ogImages = post?.yoast_head_json?.og_image;
        if (!Array.isArray(ogImages)) return null;
        const imageObj = ogImages.find(item => item?.url);
        return imageObj?.url || null;
    };
    
    const oEmbedCache = new Map();
    
    const fetchOEmbedThumbnail = async (postUrl) => {
        if (!postUrl) return null;
        if (oEmbedCache.has(postUrl)) return oEmbedCache.get(postUrl);
    
        const endpoint = `https://proppik.com/blog/wp-json/oembed/1.0/embed?url=${encodeURIComponent(postUrl)}&_fields=thumbnail_url`;
    
        try {
            const response = await fetch(endpoint, {
                headers: {
                    'Accept': 'application/json'
                }
            });
    
            if (!response.ok) {
                oEmbedCache.set(postUrl, null);
                return null;
            }
    
            const data = await response.json();
            const thumbnail = data?.thumbnail_url || null;
            oEmbedCache.set(postUrl, thumbnail);
            return thumbnail;
        } catch (error) {
            console.error(`Failed to load oEmbed thumbnail for ${postUrl}:`, error);
            oEmbedCache.set(postUrl, null);
            return null;
        }
    };
    
    const resolvePostImage = async (post, mediaMap) => {
        let image = getEmbeddedImage(post) || getYoastImage(post);
    
        if (!image && post?.featured_media && mediaMap.has(post.featured_media)) {
            image = mediaMap.get(post.featured_media);
        }
    
        if (!image) {
            image = extractFirstImageFromContent(post?.content?.rendered);
        }
    
        if (!image) {
            image = await fetchOEmbedThumbnail(post?.link);
        }
    
        return image || BLOG_IMAGE_FALLBACK;
    };
    
    const buildBlogCard = (post, imageSrc) => {
        const title = stripHTML(post?.title?.rendered) || 'Untitled';
        const excerpt = truncateText(stripHTML(post?.excerpt?.rendered));
        const date = formatPostDate(post?.date);
        const postLink = escapeAttribute(post?.link || '#');
        const imageUrl = escapeAttribute(imageSrc || BLOG_IMAGE_FALLBACK);
    
        return `
            <div class="col-lg-4 col-md-6">
                <article class="image-card blog-card h-100 d-flex flex-column">
                    <div class="image ratio ratio-4x3">
                        <a href="${postLink}" target="_blank" rel="noopener">
                            <img src="${imageUrl}" alt="${escapeAttribute(title)}" loading="lazy">
                            <div class="overlay">
                                <p>Read Post</p>
                            </div>
                        </a>
                    </div>
                    <div class="blog-card-body d-flex flex-column">
                        ${date ? `<span class="blog-card-date text-muted d-inline-block mb-2">${date}</span>` : ''}
                        <h3 class="blog-card-title">${title}</h3>
                        ${excerpt ? `<p class="blog-card-excerpt">${excerpt}</p>` : ''}
                        <div class="mt-auto pt-3">
                            <a href="${postLink}" target="_blank" rel="noopener" class="btn btn-black-outline">Read More</a>
                        </div>
                    </div>
                </article>
            </div>
        `;
    };
    
    async function loadRecentPosts() {
        const container = document.getElementById('blog-grid');
        if (!container) return;
    
        const setPlaceholder = (markup) => {
            container.innerHTML = `
                <div class="col-12">
                    <div class="image-card loading">
                        <div class="overlay">
                            ${markup}
                        </div>
                    </div>
                </div>
            `;
        };
    
        setPlaceholder('<p>Loading latest posts…</p>');
    
        try {
            const response = await fetch(BLOG_API_ENDPOINT, {
                headers: {
                    'Accept': 'application/json'
                }
            });
    
            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }
    
            const posts = await response.json();
            if (!Array.isArray(posts) || !posts.length) {
                setPlaceholder('<p>No blog posts available right now. Check back soon!</p>');
                return;
            }
    
            const mediaMap = await fetchFeaturedMediaMap(posts);
            const cards = await Promise.all(posts.map(async post => {
                const imageSrc = await resolvePostImage(post, mediaMap);
                return buildBlogCard(post, imageSrc);
            }));
            container.innerHTML = cards.join('');
        } catch (error) {
            console.error('Error fetching posts:', error);
            setPlaceholder('<p>We couldn’t load the latest posts. Please try again later.</p>');
        }
    }
    
    window.addEventListener('DOMContentLoaded', loadRecentPosts);
    
    
    
    
    
}());