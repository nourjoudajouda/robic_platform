/**
 * Login Page JavaScript
 * روبيك - تسجيل الدخول
 */

(function() {
    'use strict';

    // DOM Content Loaded
    document.addEventListener('DOMContentLoaded', function() {
        init();
    });

    /**
     * Initialize all functions
     */
    function init() {
        initAccountTypeTabs();
        initPasswordToggle();
        initFormSubmit();
        initGoogleLogin();
        initAjaxErrorHandler();
    }

    /**
     * Initialize Account Type Tabs
     */
    function initAccountTypeTabs() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                tabButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Here you can add logic to handle different account types
                const accountType = this.getAttribute('data-tab');
                console.log('Account type selected:', accountType);
            });
        });
    }

    /**
     * Initialize Password Toggle
     */
    function initPasswordToggle() {
        const passwordToggles = document.querySelectorAll('.password-toggle');
        
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    }

    /**
     * Initialize Form Submit
     */
    function initFormSubmit() {
        const loginForm = document.getElementById('loginForm');
        
        if (loginForm) {
            // Check for error messages on page load (e.g., from 419 error)
            checkForErrors();
            
            // Form will be submitted normally by Laravel
            // Just add loading state
            loginForm.addEventListener('submit', function(e) {
                const submitBtn = loginForm.querySelector('.btn-login');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري تسجيل الدخول...';
                    
                    // Re-enable after 10 seconds in case of error
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }, 10000);
                }
            });
        }
    }

    /**
     * Check for error messages and display them
     */
    function checkForErrors() {
        // Check if there are validation errors in the page
        const errorMessages = document.querySelectorAll('.alert-danger, .text-danger, .invalid-feedback');
        if (errorMessages.length > 0) {
            // Scroll to first error
            errorMessages[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    /**
     * Handle 419 errors globally (if using AJAX)
     */
    function initAjaxErrorHandler() {
        // Intercept fetch requests
        if (window.fetch) {
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args)
                    .then(response => {
                        if (response.status === 419) {
                            // Session expired - show message and reload
                            alert('انتهت صلاحية الجلسة. سيتم تحديث الصفحة الآن.');
                            window.location.reload();
                            return Promise.reject(new Error('Session expired'));
                        }
                        return response;
                    });
            };
        }

        // Intercept XMLHttpRequest
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSend = XMLHttpRequest.prototype.send;

        XMLHttpRequest.prototype.open = function(...args) {
            this._url = args[1];
            return originalOpen.apply(this, args);
        };

        XMLHttpRequest.prototype.send = function(...args) {
            this.addEventListener('load', function() {
                if (this.status === 419) {
                    alert('انتهت صلاحية الجلسة. سيتم تحديث الصفحة الآن.');
                    window.location.reload();
                }
            });
            return originalSend.apply(this, args);
        };
    }

    /**
     * Initialize Google Login
     */
    function initGoogleLogin() {
        const googleLoginBtn = document.querySelector('.btn-google-login');
        
        googleLoginBtn.addEventListener('click', function() {
            // Here you can add Google OAuth logic
            console.log('Google login clicked');
            
            // Disable button
            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري الاتصال...';
            
            // Simulate API call
            setTimeout(() => {
                // Reset button
                this.disabled = false;
                this.innerHTML = originalText;
                
                // Here you would redirect to Google OAuth
                // window.location.href = 'google-oauth-url';
            }, 1500);
        });
    }

})();
