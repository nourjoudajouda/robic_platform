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
            // Form will be submitted normally by Laravel
            // Just add loading state
            loginForm.addEventListener('submit', function(e) {
                const submitBtn = loginForm.querySelector('.btn-login');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري تسجيل الدخول...';
                    
                    // Re-enable after 5 seconds in case of error
                    setTimeout(() => {
                        if (submitBtn.disabled) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }, 5000);
                }
            });
        }
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
