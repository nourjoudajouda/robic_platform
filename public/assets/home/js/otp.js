/**
 * OTP Page JavaScript
 * روبيك - التحقق من الإيميل
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
        initOTPInputs();
        initCountdown();
        initResendLink();
        initVerifyButton();
    }

    /**
     * Initialize OTP Input Fields
     */
    function initOTPInputs() {
        const otpInputs = document.querySelectorAll('.otp-input');
        
        if (otpInputs.length === 0) return;
        
        otpInputs.forEach((input, index) => {
            // Focus on first input
            if (index === 0) {
                setTimeout(() => input.focus(), 100);
            }

            // Handle input
            input.addEventListener('input', function(e) {
                const value = e.target.value;
                
                // Only allow numbers
                if (!/^\d*$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // Move to next input if value entered
                if (value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            // Handle paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').trim();
                
                if (/^\d+$/.test(pastedData)) {
                    const digits = pastedData.split('').slice(0, otpInputs.length);
                    digits.forEach((digit, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = digit;
                        }
                    });
                    
                    // Focus on last filled input or last input
                    const lastFilledIndex = Math.min(digits.length - 1, otpInputs.length - 1);
                    otpInputs[lastFilledIndex].focus();
                }
            });
        });
    }

    /**
     * Initialize Countdown Timer
     * Note: Countdown is handled by Laravel blade template
     */
    function initCountdown() {
        // Countdown is handled by the blade template script
        // This function is kept for compatibility but does nothing
        // The actual countdown is in the email.blade.php file
    }

    /**
     * Initialize Resend Link
     */
    function initResendLink() {
        const resendLink = document.querySelector('.try-again-link');
        
        if (resendLink) {
            resendLink.addEventListener('click', function(e) {
                // Link will be handled by Laravel route
                // Just add loading state if needed
                const originalText = this.textContent;
                this.textContent = 'جاري الإرسال...';
                
                // Reset after 3 seconds in case of error
                setTimeout(() => {
                    if (this.textContent === 'جاري الإرسال...') {
                        this.textContent = originalText;
                    }
                }, 3000);
            });
        }
    }

    /**
     * Initialize Verify Button
     */
    function initVerifyButton() {
        const verifyBtn = document.querySelector('.btn-verify');
        const otpForm = document.querySelector('.submit-form');
        const otpInputs = document.querySelectorAll('.otp-input');

        if (otpForm && verifyBtn) {
            otpForm.addEventListener('submit', function(e) {
                // Get OTP code from all inputs
                const otpCode = Array.from(otpInputs)
                    .map(input => input.value)
                    .join('');

                // Validate OTP length (should be 6 digits for Laravel)
                if (otpCode.length !== 6) {
                    e.preventDefault();
                    alert('يرجى إدخال رمز التحقق الكامل');
                    // Focus on first empty input
                    const firstEmpty = Array.from(otpInputs).find(input => !input.value);
                    if (firstEmpty) {
                        firstEmpty.focus();
                    } else {
                        otpInputs[0].focus();
                    }
                    return false;
                }

                // Disable button and show loading
                verifyBtn.disabled = true;
                const originalText = verifyBtn.innerHTML;
                verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> جاري التحقق...';
            });
        }

        // Auto-submit when all fields are filled
        if (otpInputs.length > 0) {
            otpInputs.forEach((input, index) => {
                input.addEventListener('input', function() {
                    if (this.value && index === otpInputs.length - 1) {
                        // Check if all fields are filled
                        const allFilled = Array.from(otpInputs).every(inp => inp.value);
                        if (allFilled && otpForm) {
                            // Small delay before auto-submit
                            setTimeout(() => {
                                otpForm.submit();
                            }, 300);
                        }
                    }
                });
            });
        }
    }

})();
