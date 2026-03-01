/**
 * ToyHaven Page Transitions & Loading States
 * Provides smooth page transitions and loading overlays
 */

(function() {
    'use strict';
    
    // Page Transition on Load
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('page-transition');
        
        // Remove transition class after animation
        setTimeout(function() {
            document.body.classList.remove('page-transition');
        }, 400);
    });
    
    // Loading Overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.id = 'loadingOverlay';
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading...</p>
        </div>
    `;
    document.body.appendChild(loadingOverlay);
    
    // Show Loading Function
    window.showLoading = function(message) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            const text = overlay.querySelector('p');
            if (text && message) {
                text.textContent = message;
            }
            overlay.classList.add('active');
        }
    };
    
    // Hide Loading Function
    window.hideLoading = function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('active');
        }
    };
    
    // Auto-show loading on form submit
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.hasAttribute('data-loading')) {
            const message = form.getAttribute('data-loading-message') || 'Processing...';
            showLoading(message);
        }
    });
    
    // Auto-show loading on link clicks with data-loading attribute
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[data-loading]');
        if (link && !link.hasAttribute('data-bs-toggle')) {
            const message = link.getAttribute('data-loading-message') || 'Loading...';
            showLoading(message);
        }
    });
    
    // Page Transition on Navigation
    const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not([data-bs-toggle]):not(.dropdown-item):not(.dropdown-toggle)');
    links.forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Skip if it's a dropdown or has no href
            if (this.closest('.dropdown-menu') || !this.href || this.href === '#') {
                return;
            }
            
            if (this.hostname === window.location.hostname) {
                e.preventDefault();
                const href = this.href;
                
                document.body.style.opacity = '0';
                document.body.style.transition = 'opacity 0.3s ease';
                
                setTimeout(function() {
                    window.location.href = href;
                }, 300);
            }
        });
    });
    
    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
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
    
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observe elements with data-animate attribute
    document.querySelectorAll('[data-animate]').forEach(function(el) {
        observer.observe(el);
    });
    
    // Button Loading State
    window.setButtonLoading = function(button, loading) {
        if (loading) {
            button.disabled = true;
            button.setAttribute('data-original-text', button.innerHTML);
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
        } else {
            button.disabled = false;
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
                button.removeAttribute('data-original-text');
            }
        }
    };
    
    // Auto-handle form button loading
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.hasAttribute('data-no-loading')) {
                setButtonLoading(submitBtn, true);
            }
        });
    });
    
    // Card Hover Effects
    document.querySelectorAll('.card-hover').forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
    });
    
    // Image Lazy Loading with Fade In
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('animate-fade-in');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(function(img) {
            imageObserver.observe(img);
        });
    }
    
    // Toast Notifications
    window.showToast = function(message, type, duration) {
        type = type || 'info';
        duration = duration || 3000;
        
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type} animate-slide-in-right`;
        toast.innerHTML = `
            <div class="toast-icon">
                <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'}-fill"></i>
            </div>
            <div class="toast-message">${message}</div>
            <button type="button" class="toast-close" onclick="this.parentElement.remove()">
                <i class="bi bi-x"></i>
            </button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.classList.add('toast-fade-out');
            setTimeout(function() {
                toast.remove();
            }, 300);
        }, duration);
    };
    
    // Prevent double form submission
    document.querySelectorAll('form').forEach(function(form) {
        let submitted = false;
        form.addEventListener('submit', function(e) {
            if (submitted) {
                e.preventDefault();
                return false;
            }
            submitted = true;
            
            // Reset after 5 seconds in case of error
            setTimeout(function() {
                submitted = false;
            }, 5000);
        });
    });
    
})();
