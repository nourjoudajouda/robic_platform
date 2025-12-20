/**
 * Register Page JavaScript
 * روبيك - إنشاء حساب
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
        initTabs();
        initPhoneCodeSelect();
        initPasswordToggle();
        initFormValidation();
        initFormSubmit();
        initFileUpload();
    }

    /**
     * Initialize Phone Code Select
     */
    function initPhoneCodeSelect() {
        // Initialize Select2 for phone code dropdowns with search
        if (typeof $.fn.select2 !== 'undefined') {
            $('.phone-code-select').each(function() {
                const $select = $(this);
                const $wrapper = $select.closest('.phone-input-wrapper');
                
                $select.select2({
                    theme: 'default',
                    width: '105px',
                    dropdownParent: $wrapper,
                    language: {
                        noResults: function() {
                            return 'لا توجد نتائج';
                        },
                        searching: function() {
                            return 'جاري البحث...';
                        }
                    },
                    placeholder: 'اختر رمز الدولة',
                    allowClear: false,
                    minimumResultsForSearch: 0, // Enable search for all options
                    escapeMarkup: function(markup) {
                        // Don't escape markup to allow emoji to display
                        return markup;
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        // Get the original option element to preserve emoji
                        var $option = $select.find('option[value="' + data.id + '"]');
                        if ($option.length) {
                            // Return text directly to avoid any HTML wrapper
                            return $option.text();
                        }
                        return data.text;
                    },
                    templateSelection: function(data) {
                        // Get the original option element to preserve emoji
                        var $option = $select.find('option[value="' + data.id + '"]');
                        if ($option.length) {
                            // Return text directly to avoid any HTML wrapper
                            return $option.text();
                        }
                        return data.text;
                    }
                });

                // Set default value to Saudi Arabia
                $select.val('+966').trigger('change');
            });

            // Close select when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.phone-input-wrapper').length) {
                    $('.phone-code-select').select2('close');
                }
            });
        } else {
            // Fallback: Set default value to Saudi Arabia
            const phoneCodeSelects = document.querySelectorAll('.phone-code-select');
            phoneCodeSelects.forEach(select => {
                select.value = '+966';
            });
        }
    }

    /**
     * Initialize Tabs
     */
    function initTabs() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const forms = document.querySelectorAll('.register-form');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');

                // Remove active class from all buttons
                tabButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');

                // Hide all forms
                forms.forEach(form => {
                    form.style.display = 'none';
                    form.classList.remove('active');
                });

                // Show target form
                const targetForm = document.querySelector(`[data-form="${targetTab}"]`);
                if (targetForm) {
                    targetForm.style.display = 'block';
                    targetForm.classList.add('active');
                }
            });
        });
    }

    /**
     * Initialize File Upload
     */
    function initFileUpload() {
        const fileInput = document.getElementById('commercialRegistration');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const fileLabel = this.closest('.file-upload-wrapper').querySelector('.file-upload-label span');
                if (fileLabel) {
                    if (this.files && this.files.length > 0) {
                        const fileName = this.files[0].name;
                        fileLabel.textContent = fileName;
                    } else {
                        fileLabel.textContent = 'يرجى رفع السجل التجاري للمنشأة هنا';
                    }
                }
            });
        }
    }

    /**
     * Initialize Password Toggle
     */
    function initPasswordToggle() {
        const passwordToggles = document.querySelectorAll('.password-toggle');
        
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
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
     * Initialize Form Validation
     */
    function initFormValidation() {
        // Individual form validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('password_confirmation');

        if (password && confirmPassword && confirmPassword.parentElement) {
            // Create error message element if it doesn't exist
            let errorElement = confirmPassword.parentElement.querySelector('.password-error');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'password-error text-danger mt-1';
                errorElement.style.fontSize = '0.875rem';
                if (confirmPassword.parentElement) {
                    confirmPassword.parentElement.appendChild(errorElement);
                }
            }

            // Real-time password confirmation validation
            function validatePasswordMatch() {
                if (!password || !confirmPassword) {
                    return true;
                }
                
                if (confirmPassword.value && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('كلمة المرور غير متطابقة');
                    confirmPassword.classList.add('is-invalid');
                    if (errorElement) {
                        errorElement.textContent = 'كلمة المرور غير متطابقة';
                        errorElement.style.display = 'block';
                    }
                    return false;
                } else {
                    confirmPassword.setCustomValidity('');
                    confirmPassword.classList.remove('is-invalid');
                    if (errorElement) {
                        errorElement.style.display = 'none';
                    }
                    return true;
                }
            }

            if (confirmPassword && password) {
                confirmPassword.addEventListener('input', validatePasswordMatch);
                password.addEventListener('input', validatePasswordMatch);
            }

            // Validate on form submit
            const individualForm = document.getElementById('individualForm');
            if (individualForm) {
                individualForm.addEventListener('submit', function(e) {
                    // Only prevent if passwords don't match
                    if (password && confirmPassword && confirmPassword.value && password.value !== confirmPassword.value) {
                        e.preventDefault();
                        if (errorElement) {
                            errorElement.textContent = 'كلمة المرور غير متطابقة';
                            errorElement.style.display = 'block';
                        }
                        return false;
                    }
                    // Allow form to submit normally
                });
            }
        }

        // Establishment form validation
        const establishmentPassword = document.getElementById('establishmentPassword');
        const establishmentConfirmPassword = document.getElementById('establishmentConfirmPassword');

        if (establishmentPassword && establishmentConfirmPassword && establishmentConfirmPassword.parentElement) {
            // Create error message element if it doesn't exist
            let errorElement = establishmentConfirmPassword.parentElement.querySelector('.password-error');
            if (!errorElement) {
                errorElement = document.createElement('div');
                errorElement.className = 'password-error text-danger mt-1';
                errorElement.style.fontSize = '0.875rem';
                if (establishmentConfirmPassword.parentElement) {
                    establishmentConfirmPassword.parentElement.appendChild(errorElement);
                }
            }

            // Real-time password confirmation validation
            function validateEstablishmentPasswordMatch() {
                if (!establishmentPassword || !establishmentConfirmPassword) {
                    return true;
                }
                
                if (establishmentConfirmPassword.value && establishmentPassword.value !== establishmentConfirmPassword.value) {
                    establishmentConfirmPassword.setCustomValidity('كلمة المرور غير متطابقة');
                    establishmentConfirmPassword.classList.add('is-invalid');
                    if (errorElement) {
                        errorElement.textContent = 'كلمة المرور غير متطابقة';
                        errorElement.style.display = 'block';
                    }
                    return false;
                } else {
                    establishmentConfirmPassword.setCustomValidity('');
                    establishmentConfirmPassword.classList.remove('is-invalid');
                    if (errorElement) {
                        errorElement.style.display = 'none';
                    }
                    return true;
                }
            }

            if (establishmentConfirmPassword && establishmentPassword) {
                establishmentConfirmPassword.addEventListener('input', validateEstablishmentPasswordMatch);
                establishmentPassword.addEventListener('input', validateEstablishmentPasswordMatch);
            }

            // Validate on form submit
            const establishmentForm = document.getElementById('establishmentForm');
            if (establishmentForm) {
                establishmentForm.addEventListener('submit', function(e) {
                    // Only prevent if passwords don't match
                    if (establishmentPassword && establishmentConfirmPassword && establishmentConfirmPassword.value && establishmentPassword.value !== establishmentConfirmPassword.value) {
                        e.preventDefault();
                        if (errorElement) {
                            errorElement.textContent = 'كلمة المرور غير متطابقة';
                            errorElement.style.display = 'block';
                        }
                        return false;
                    }
                    // Allow form to submit normally
                });
            }
        }
    }

    /**
     * Initialize Form Submit
     */
    function initFormSubmit() {
        // Individual form submit
        const individualForm = document.getElementById('individualForm');
        if (individualForm) {
            individualForm.addEventListener('submit', function(e) {
                // Form will be submitted by Laravel
                // Just add loading state
                const submitBtn = individualForm.querySelector('.btn-register');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري الإنشاء...';
                    
                    // Re-enable after 5 seconds in case of error
                    const timeoutId = setTimeout(() => {
                        const btn = individualForm.querySelector('.btn-register');
                        if (btn && btn.disabled) {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    }, 5000);
                    
                    // Clear timeout if form submits successfully
                    individualForm.addEventListener('submit', function() {
                        clearTimeout(timeoutId);
                    }, { once: true });
                }
            });
        }

        // Establishment form submit
        const establishmentForm = document.getElementById('establishmentForm');
        if (establishmentForm) {
            establishmentForm.addEventListener('submit', function(e) {
                // Form will be submitted by Laravel
                // Just add loading state
                const submitBtn = establishmentForm.querySelector('.btn-register');
                if (submitBtn && !submitBtn.disabled) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري الإنشاء...';
                    
                    // Re-enable after 5 seconds in case of error
                    const timeoutId = setTimeout(() => {
                        const btn = establishmentForm.querySelector('.btn-register');
                        if (btn && btn.disabled) {
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    }, 5000);
                    
                    // Clear timeout if form submits successfully
                    establishmentForm.addEventListener('submit', function() {
                        clearTimeout(timeoutId);
                    }, { once: true });
                }
            });
        }
    }

})();
