// ToyHaven Platform - Interactive Animations and Effects

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== Navbar Scroll Effect =====
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // ===== Scroll Reveal Animation =====
    const revealElements = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    revealElements.forEach(element => {
        revealObserver.observe(element);
    });

    // ===== Animate elements on scroll =====
    const animateOnScroll = () => {
        const elements = document.querySelectorAll('.animate-on-scroll');
        const windowHeight = window.innerHeight;

        elements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            
            if (elementTop < windowHeight - 100) {
                element.classList.add('animated');
            }
        });
    };

    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on load

    // ===== Smooth scroll for anchor links =====
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // ===== Add animation delay to product cards =====
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach((card, index) => {
        if (index < 10) { // Limit to first 10 cards to avoid performance issues
            card.style.animationDelay = `${index * 0.1}s`;
        }
    });

    // ===== Button ripple effect =====
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // ===== Cart badge pulse on add =====
    const cartBadge = document.querySelector('.cart-badge');
    if (cartBadge) {
        // Add pulse animation when page loads if badge exists
        cartBadge.classList.add('animate-pulse');
        setTimeout(() => {
            cartBadge.classList.remove('animate-pulse');
        }, 2000);
    }

    // ===== Form input focus animations =====
    const formInputs = document.querySelectorAll('.form-control, .form-select');
    formInputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });

    // ===== Image lazy loading animation =====
    const images = document.querySelectorAll('img[data-lazy]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.lazy;
                img.classList.add('fade-in');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // ===== Counter animation for numbers =====
    const animateCounter = (element, target, duration = 2000) => {
        let start = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            start += increment;
            if (start >= target) {
                element.textContent = Math.round(target);
                clearInterval(timer);
            } else {
                element.textContent = Math.round(start);
            }
        }, 16);
    };

    // ===== Add stagger animation to product grid =====
    const productGrid = document.querySelector('.product-grid');
    if (productGrid) {
        const cards = productGrid.querySelectorAll('.product-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.05}s`;
        });
    }

    // ===== Smooth page transitions (reset on back/forward so bfcache restore doesn't leave fade) =====
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            document.body.style.opacity = '';
            document.body.style.transition = '';
        }
    });
    const links = document.querySelectorAll('a:not([href^="#"]):not([target="_blank"])');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.hasAttribute('data-no-transition')) {
                document.body.style.opacity = '0.8';
                document.body.style.transition = 'opacity 0.2s';
            }
        });
    });

    // ===== Product card hover effect enhancement =====
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // ===== Add loading state to buttons on form submit =====
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"], input[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                const originalText = submitButton.textContent || submitButton.value;
                submitButton.disabled = true;
                submitButton.style.opacity = '0.7';
                submitButton.textContent = submitButton.textContent ? 'Loading...' : submitButton.value;
                submitButton.value = submitButton.value ? 'Loading...' : submitButton.value;
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitButton.disabled = false;
                    submitButton.style.opacity = '1';
                    submitButton.textContent = originalText;
                    submitButton.value = originalText;
                }, 5000);
            }
        });
    });

    console.log('ToyHaven animations initialized!');
});
