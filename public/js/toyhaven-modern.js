/**
 * ToyHaven Modern UI JavaScript
 * Professional, Responsive Interactive Components
 */

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initDropdowns();
    initModals();
    initToasts();
    initTabs();
    initTooltips();
    initFormValidation();
    initImagePreview();
    initSidebar();
});

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    if (mobileMenuClose && mobileMenu) {
        mobileMenuClose.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
    
    // Close on overlay click
    if (mobileMenu) {
        mobileMenu.addEventListener('click', (e) => {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }
}

/**
 * Dropdown Menus
 */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        
        if (trigger && menu) {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                
                // Close other dropdowns
                document.querySelectorAll('[data-dropdown].active').forEach(d => {
                    if (d !== dropdown) d.classList.remove('active');
                });
                
                dropdown.classList.toggle('active');
            });
        }
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown].active').forEach(d => {
            d.classList.remove('active');
        });
    });
}

/**
 * Modal Dialogs
 */
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modalCloses = document.querySelectorAll('[data-modal-close]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = trigger.getAttribute('data-modal-target');
            const modal = document.getElementById(targetId);
            const overlay = document.getElementById(targetId + '-overlay');
            
            if (modal) {
                modal.classList.add('active');
                if (overlay) overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    modalCloses.forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            const modal = closeBtn.closest('.modal');
            const overlay = document.querySelector('.modal-overlay.active');
            
            if (modal) {
                modal.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close on overlay click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', () => {
            const modal = document.querySelector('.modal.active');
            if (modal) {
                modal.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.querySelector('.modal.active');
            const overlay = document.querySelector('.modal-overlay.active');
            if (modal) {
                modal.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });
}

/**
 * Toast Notifications
 */
function initToasts() {
    window.showToast = function(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-icon">${getToastIcon(type)}</span>
                <span class="toast-message">${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;
        
        const container = document.getElementById('toast-container') || createToastContainer();
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    };
    
    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 1rem; right: 1rem; z-index: 10000;';
        document.body.appendChild(container);
        return container;
    }
    
    function getToastIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }
}

/**
 * Tab Navigation
 */
function initTabs() {
    const tabGroups = document.querySelectorAll('[data-tabs]');
    
    tabGroups.forEach(group => {
        const triggers = group.querySelectorAll('[data-tab-trigger]');
        const panels = group.querySelectorAll('[data-tab-panel]');
        
        triggers.forEach(trigger => {
            trigger.addEventListener('click', () => {
                const target = trigger.getAttribute('data-tab-trigger');
                
                // Update triggers
                triggers.forEach(t => t.classList.remove('active'));
                trigger.classList.add('active');
                
                // Update panels
                panels.forEach(panel => {
                    if (panel.getAttribute('data-tab-panel') === target) {
                        panel.classList.add('active');
                        panel.style.display = 'block';
                    } else {
                        panel.classList.remove('active');
                        panel.style.display = 'none';
                    }
                });
            });
        });
    });
}

/**
 * Tooltips
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        const text = element.getAttribute('data-tooltip');
        const position = element.getAttribute('data-tooltip-position') || 'top';
        
        element.addEventListener('mouseenter', () => {
            const tooltip = document.createElement('div');
            tooltip.className = `tooltip tooltip-${position}`;
            tooltip.textContent = text;
            tooltip.id = 'active-tooltip';
            
            document.body.appendChild(tooltip);
            
            const rect = element.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            let top, left;
            
            switch(position) {
                case 'top':
                    top = rect.top - tooltipRect.height - 8;
                    left = rect.left + (rect.width - tooltipRect.width) / 2;
                    break;
                case 'bottom':
                    top = rect.bottom + 8;
                    left = rect.left + (rect.width - tooltipRect.width) / 2;
                    break;
                case 'left':
                    top = rect.top + (rect.height - tooltipRect.height) / 2;
                    left = rect.left - tooltipRect.width - 8;
                    break;
                case 'right':
                    top = rect.top + (rect.height - tooltipRect.height) / 2;
                    left = rect.right + 8;
                    break;
            }
            
            tooltip.style.top = top + 'px';
            tooltip.style.left = left + 'px';
            
            setTimeout(() => tooltip.classList.add('show'), 10);
        });
        
        element.addEventListener('mouseleave', () => {
            const tooltip = document.getElementById('active-tooltip');
            if (tooltip) {
                tooltip.classList.remove('show');
                setTimeout(() => tooltip.remove(), 200);
            }
        });
    });
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            const inputs = form.querySelectorAll('[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    showFieldError(input, 'This field is required');
                } else {
                    input.classList.remove('is-invalid');
                    hideFieldError(input);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast('Please fill in all required fields', 'error');
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    showFieldError(input, 'This field is required');
                } else {
                    input.classList.remove('is-invalid');
                    hideFieldError(input);
                }
            });
            
            input.addEventListener('input', () => {
                if (input.value.trim()) {
                    input.classList.remove('is-invalid');
                    hideFieldError(input);
                }
            });
        });
    });
    
    function showFieldError(input, message) {
        let error = input.parentElement.querySelector('.invalid-feedback');
        if (!error) {
            error = document.createElement('div');
            error.className = 'invalid-feedback';
            input.parentElement.appendChild(error);
        }
        error.textContent = message;
    }
    
    function hideFieldError(input) {
        const error = input.parentElement.querySelector('.invalid-feedback');
        if (error) error.remove();
    }
}

/**
 * Image Preview
 */
function initImagePreview() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            const previewId = input.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (file && preview) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    if (preview.tagName === 'IMG') {
                        preview.src = e.target.result;
                    } else {
                        preview.style.backgroundImage = `url(${e.target.result})`;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

/**
 * Sidebar Toggle
 */
function initSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarClose = document.getElementById('sidebar-close');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });
    }
    
    if (sidebarClose && sidebar) {
        sidebarClose.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
    }
    
    // Close sidebar on overlay click (mobile)
    if (sidebar) {
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.style.cssText = 'position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; display: none;';
        document.body.appendChild(overlay);
        
        sidebar.addEventListener('transitionend', () => {
            if (sidebar.classList.contains('active')) {
                overlay.style.display = 'block';
            } else {
                overlay.style.display = 'none';
            }
        });
        
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
    }
}

/**
 * Confirm Dialog
 */
window.confirmAction = function(message, callback) {
    if (confirm(message)) {
        callback();
    }
};

/**
 * Copy to Clipboard
 */
window.copyToClipboard = function(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success', 2000);
    }).catch(() => {
        showToast('Failed to copy', 'error', 2000);
    });
};

/**
 * Smooth Scroll
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href !== '#') {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
});

/**
 * Auto-hide alerts
 */
setTimeout(() => {
    document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
        const duration = parseInt(alert.getAttribute('data-auto-dismiss')) || 5000;
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, duration);
    });
}, 100);

/**
 * Loading State
 */
window.setLoading = function(element, loading = true) {
    if (loading) {
        element.disabled = true;
        element.dataset.originalText = element.innerHTML;
        element.innerHTML = '<span class="spinner"></span> Loading...';
    } else {
        element.disabled = false;
        element.innerHTML = element.dataset.originalText;
    }
};

/**
 * Format Currency
 */
window.formatCurrency = function(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

/**
 * Format Date
 */
window.formatDate = function(date) {
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

/**
 * Debounce Function
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
