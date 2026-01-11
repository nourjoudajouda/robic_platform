<!DOCTYPE html>
@php
    app()->setLocale('ar');
    $currentLang = 'ar';
    $isRTL = true;
@endphp
<html lang="{{ $currentLang }}" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@lang('Register') - {{ gs('site_name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo_icon/favicon.png') }}" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    @if($isRTL)
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @else
        <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">
    @endif
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    @if($isRTL)
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    @endif
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/home/css/register.css') }}">
    <!-- Notify -->
    @include('partials.notify')
</head>
<body>
    <!-- Main Container -->
    <div class="main-container">
        <!-- Register Section -->
        <section class="register-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-7 col-md-8">
                        <div class="register-card">
                            <div class="register-header">
                                <img src="{{ asset('assets/images/logo_icon/logo-footer.png') }}" alt="ROBIC Logo" class="register-logo">
                                <h2 class="register-subtitle">@lang('Create account in')<br> <span class="header-title">@lang('Your trading platform for Green Coffee')</span></h2>
                            </div>

                            <!-- Account Type Tabs -->
                            <div class="account-type-tabs">
                                <button type="button" class="tab-btn active" data-tab="individual">
                                    @lang('Individual account')
                                </button>
                                <button type="button" class="tab-btn" data-tab="establishment">
                                    @lang('Establishment account')
                                </button>
                            </div>

                            <!-- Individual Account Form -->
                            <form action="{{ route('user.register') }}" method="POST" class="register-form active verify-gcaptcha" id="individualForm" data-form="individual" autocomplete="off">
                                @csrf
                                <input type="hidden" name="form_type" value="individual">
                                
                                @if ($errors->any())
                                    <div class="alert alert-danger mb-3">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <div class="row">
                                    @if (session()->get('reference') != null)
                                        <div class="form-group col-12">
                                            <label for="referBy" class="form-label">@lang('Reference by')</label>
                                            <input type="text" name="referBy" id="referBy" class="form-control" value="{{ session()->get('reference') }}" readonly>
                                        </div>
                                    @endif

                                    <div class="form-group col-12">
                                        <label for="fullName" class="form-label">@lang('Name')</label>
                                        <input type="text" class="form-control" name="firstname" id="fullName" value="{{ old('firstname') }}" placeholder="@lang('Enter your full name')" autocomplete="off" required>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="lastname" class="form-label">@lang('Last Name')</label>
                                        <input type="text" class="form-control" name="lastname" id="lastname" value="{{ old('lastname') }}" placeholder="@lang('Enter family name')" autocomplete="off" required>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="email" class="form-label">@lang('E-Mail Address')</label>
                                        <input type="email" class="form-control checkUser" name="email" id="email" value="{{ old('email') }}" placeholder="@lang('Enter email')" autocomplete="off" required>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="phone" class="form-label">@lang('Mobile Number')</label>
                                        <div class="phone-input-wrapper">
                                            <input type="tel" class="form-control phone-number" name="mobile" id="phone" value="{{ old('mobile') }}" placeholder="@lang('Enter phone number')" autocomplete="off" required>
                                            <select class="phone-code-select" name="country_code" id="phoneCode" autocomplete="off" required>
                                                <option value="+966" data-flag="🇸🇦" {{ old('country_code', '+966') == '+966' ? 'selected' : '' }}>🇸🇦 +966</option>
                                                <option value="+971" data-flag="🇦🇪" {{ old('country_code') == '+971' ? 'selected' : '' }}>🇦🇪 +971</option>
                                                <option value="+965" data-flag="🇰🇼" {{ old('country_code') == '+965' ? 'selected' : '' }}>🇰🇼 +965</option>
                                                <option value="+974" data-flag="🇶🇦" {{ old('country_code') == '+974' ? 'selected' : '' }}>🇶🇦 +974</option>
                                                <option value="+973" data-flag="🇧🇭" {{ old('country_code') == '+973' ? 'selected' : '' }}>🇧🇭 +973</option>
                                                <option value="+968" data-flag="🇴🇲" {{ old('country_code') == '+968' ? 'selected' : '' }}>🇴🇲 +968</option>
                                                <option value="+961" data-flag="🇱🇧" {{ old('country_code') == '+961' ? 'selected' : '' }}>🇱🇧 +961</option>
                                                <option value="+962" data-flag="🇯🇴" {{ old('country_code') == '+962' ? 'selected' : '' }}>🇯🇴 +962</option>
                                                <option value="+20" data-flag="🇪🇬" {{ old('country_code') == '+20' ? 'selected' : '' }}>🇪🇬 +20</option>
                                                <option value="+212" data-flag="🇲🇦" {{ old('country_code') == '+212' ? 'selected' : '' }}>🇲🇦 +212</option>
                                                <option value="+213" data-flag="🇩🇿" {{ old('country_code') == '+213' ? 'selected' : '' }}>🇩🇿 +213</option>
                                                <option value="+216" data-flag="🇹🇳" {{ old('country_code') == '+216' ? 'selected' : '' }}>🇹🇳 +216</option>
                                                <option value="+1" data-flag="🇺🇸" {{ old('country_code') == '+1' ? 'selected' : '' }}>🇺🇸 +1</option>
                                                <option value="+44" data-flag="🇬🇧" {{ old('country_code') == '+44' ? 'selected' : '' }}>🇬🇧 +44</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="password" class="form-label">@lang('Password')</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" class="form-control" name="password" id="password" placeholder="أدخل كلمة المرور هنا" autocomplete="new-password" required>
                                            <button type="button" class="password-toggle" data-target="password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="password_confirmation" class="form-label">@lang('Confirm Password')</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="يرجى تأكيد كلمة المرور هنا" autocomplete="new-password" required>
                                            <button type="button" class="password-toggle" data-target="password_confirmation">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <x-captcha />

                                    <div class="form-group col-12">
                                        <button type="submit" class="btn btn-register w-100">
                                            <i class="fas fa-user-plus me-2"></i>
                                            @lang('Register')
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <div class="login-link">
                                            <p>@lang('Already have an account?') <a href="{{ route('user.login') }}">@lang('Login now')</a></p>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="divider">
                                            <span class="divider-text">أو</span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        @if (@gs('socialite_credentials')->google->status == Status::ENABLE)
                                            <a href="{{ route('user.social.login', 'google') }}" class="btn btn-google-login">
                                                <img src="{{ asset('assets/templates/basic/images/google.svg') }}" alt="Google" class="google-icon">
                                                تسجيل باستخدام جوجل
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>

                            <!-- Establishment Account Form -->
                            <form action="{{ route('user.register') }}" method="POST" class="register-form verify-gcaptcha" id="establishmentForm" data-form="establishment" autocomplete="off" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="form_type" value="establishment">
                                
                                @if ($errors->any())
                                    <div class="alert alert-danger mb-3">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <div class="row">
                                    @if (session()->get('reference') != null)
                                        <div class="form-group col-12">
                                            <label for="establishmentReferBy" class="form-label">@lang('Reference by')</label>
                                            <input type="text" name="referBy" id="establishmentReferBy" class="form-control" value="{{ session()->get('reference') }}" readonly>
                                        </div>
                                    @endif

                                    <div class="form-group col-12">
                                        <label for="establishmentName" class="form-label">اسم المنشأة</label>
                                        <input type="text" class="form-control" name="firstname" id="establishmentName" value="{{ old('firstname') }}" placeholder="ادخل اسم المنشأة هنا" autocomplete="off" required>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="establishmentEmail" class="form-label">@lang('E-Mail Address')</label>
                                        <input type="email" class="form-control checkUser" name="email" id="establishmentEmail" value="{{ old('email') }}" placeholder="ادخل البريد الإلكتروني الخاص بالمنشأة هنا" autocomplete="off" required>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="establishmentPhone" class="form-label">رقم الهاتف</label>
                                        <div class="phone-input-wrapper">
                                            <input type="tel" class="form-control phone-number" name="mobile" id="establishmentPhone" value="{{ old('mobile') }}" placeholder="ادخل رقم هاتف المنشأة هذا" autocomplete="off" required>
                                            <select class="phone-code-select" name="country_code" id="establishmentPhoneCode" autocomplete="off" required>
                                                <option value="+966" data-flag="🇸🇦" {{ old('country_code', '+966') == '+966' ? 'selected' : '' }}>🇸🇦 +966</option>
                                                <option value="+971" data-flag="🇦🇪" {{ old('country_code') == '+971' ? 'selected' : '' }}>🇦🇪 +971</option>
                                                <option value="+965" data-flag="🇰🇼" {{ old('country_code') == '+965' ? 'selected' : '' }}>🇰🇼 +965</option>
                                                <option value="+974" data-flag="🇶🇦" {{ old('country_code') == '+974' ? 'selected' : '' }}>🇶🇦 +974</option>
                                                <option value="+973" data-flag="🇧🇭" {{ old('country_code') == '+973' ? 'selected' : '' }}>🇧🇭 +973</option>
                                                <option value="+968" data-flag="🇴🇲" {{ old('country_code') == '+968' ? 'selected' : '' }}>🇴🇲 +968</option>
                                                <option value="+961" data-flag="🇱🇧" {{ old('country_code') == '+961' ? 'selected' : '' }}>🇱🇧 +961</option>
                                                <option value="+962" data-flag="🇯🇴" {{ old('country_code') == '+962' ? 'selected' : '' }}>🇯🇴 +962</option>
                                                <option value="+20" data-flag="🇪🇬" {{ old('country_code') == '+20' ? 'selected' : '' }}>🇪🇬 +20</option>
                                                <option value="+212" data-flag="🇲🇦" {{ old('country_code') == '+212' ? 'selected' : '' }}>🇲🇦 +212</option>
                                                <option value="+213" data-flag="🇩🇿" {{ old('country_code') == '+213' ? 'selected' : '' }}>🇩🇿 +213</option>
                                                <option value="+216" data-flag="🇹🇳" {{ old('country_code') == '+216' ? 'selected' : '' }}>🇹🇳 +216</option>
                                                <option value="+1" data-flag="🇺🇸" {{ old('country_code') == '+1' ? 'selected' : '' }}>🇺🇸 +1</option>
                                                <option value="+44" data-flag="🇬🇧" {{ old('country_code') == '+44' ? 'selected' : '' }}>🇬🇧 +44</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="commercialRegistration" class="form-label">السجل التجاري للمنشأة</label>
                                        <div class="file-upload-wrapper">
                                            <input type="file" class="file-input" name="commercial_registration" id="commercialRegistration" accept=".pdf,.jpg,.jpeg,.png" autocomplete="off" required>
                                            <label for="commercialRegistration" class="file-upload-label">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                                <span>يرجى رفع السجل التجاري للمنشأة هنا</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="establishmentPassword" class="form-label">@lang('Password')</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" class="form-control" name="password" id="establishmentPassword" placeholder="ادخل كلمة المرور هذا" autocomplete="new-password" required>
                                            <button type="button" class="password-toggle" data-target="establishmentPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="establishmentConfirmPassword" class="form-label">@lang('Confirm Password')</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" class="form-control" name="password_confirmation" id="establishmentConfirmPassword" placeholder="يوم تأكيد كلمة المرور هذا" autocomplete="new-password" required>
                                            <button type="button" class="password-toggle" data-target="establishmentConfirmPassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <x-captcha />

                                    <div class="form-group col-12">
                                        <button type="submit" class="btn btn-register w-100">
                                            <i class="fas fa-user-plus me-2"></i>
                                            @lang('Register')
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <div class="login-link">
                                            <p>@lang('Already have an account?') <a href="{{ route('user.login') }}">@lang('Login now')</a></p>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="divider">
                                            <span class="divider-text">أو</span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        @if (@gs('socialite_credentials')->google->status == Status::ENABLE)
                                            <a href="{{ route('user.social.login', 'google') }}" class="btn btn-google-login">
                                                <img src="{{ asset('assets/templates/basic/images/google.svg') }}" alt="Google" class="google-icon">
                                                تسجيل باستخدام جوجل
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 mb-md-0">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <a href="#" class="logo-link">
                                <img src="{{ asset('assets/images/logo_icon/logo-footer.png') }}" alt="ROBIC Logo" class="logo-img">
                            </a>
                        </div>
                        <h4 class="footer-title">@lang('ROBIC, your trusted platform')</h4>
                        <p class="footer-description">@lang('Invest in Green Coffee... Your commercial future starts from the first bean')</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4 mb-md-0">
                    <div class="footer-widget">
                        <h5 class="footer-subtitle">روابط سريعة</h5>
                        <ul class="footer-links">
                            <li><a href="{{ route('home') }}">الرئيسية</a></li>
                            <li><a href="#">سياسة الاستخدام</a></li>
                            <li><a href="#">سياسة الشراء</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4 mb-md-0">
                    <div class="footer-widget">
                        <h5 class="footer-subtitle">السياسات</h5>
                        <ul class="footer-links">
                            <li><a href="#">سياسة الخصوصية</a></li>
                            <li><a href="#">سياسة الاسترجاع</a></li>
                            <li><a href="#">المدونة</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-widget">
                        <h5 class="footer-subtitle">تواصل معنا</h5>
                        <ul class="footer-contact">
                            <li><i class="fas fa-envelope"></i> info@robic.com</li>
                            <li><i class="fas fa-phone"></i> +966 12 345 6789</li>
                            <li><i class="fas fa-map-marker-alt"></i> المملكة العربية السعودية</li>
                        </ul>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                        <p class="copyright">&copy; 2024 روييـك, عالاصل دوّر. جميع الحقوق محفوظة.</p>
                    </div>
                    <div class="col-lg-6 col-md-12">
                        <div class="payment-methods">
                            <a href="#" class="payment-method" title="Payment Card">
                                <img src="{{ asset('assets/images/frontend/payment_methods/wallet.svg') }}" alt="Payment Card">
                            </a>
                            <a href="#" class="payment-method" title="urpay">
                                <img src="{{ asset('assets/images/frontend/payment_methods/urpay.svg') }}" alt="urpay">
                            </a>
                            <a href="#" class="payment-method" title="mada">
                                <img src="{{ asset('assets/images/frontend/payment_methods/mada.svg') }}" alt="mada">
                            </a>
                            <a href="#" class="payment-method" title="Mastercard">
                                <img src="{{ asset('assets/images/frontend/payment_methods/mastercard.svg') }}" alt="Mastercard">
                            </a>
                            <a href="#" class="payment-method" title="VISA">
                                <img src="{{ asset('assets/images/frontend/payment_methods/visa.svg') }}" alt="VISA">
                            </a>
                            <a href="#" class="payment-method" title="Apple Pay">
                                <img src="{{ asset('assets/images/frontend/payment_methods/apple-pay.svg') }}" alt="Apple Pay">
                            </a>
                            <a href="#" class="payment-method" title="stc pay">
                                <img src="{{ asset('assets/images/frontend/payment_methods/stc-pay.svg') }}" alt="stc pay">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal for existing user -->
    <div class="modal custom--modal fade" id="existModalCenter" tabindex="-1" role="dialog" aria-labelledby="existModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="existModalLongTitle">@lang('You are with us')</h5>
                    <span type="button" class="close-icon" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </span>
                </div>
                <div class="modal-body">
                    <p class="text text-center">@lang('You already have an account please Login ')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--danger btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="{{ route('user.login') }}" class="btn btn--base btn--sm">@lang('Login')</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/home/js/register.js') }}"></script>
    <script>
        "use strict";
        (function($) {
            $('.checkUser').on('focusout', function(e) {
                var url = '{{ route('user.checkUser') }}';
                var value = $(this).val();
                var token = '{{ csrf_token() }}';

                var data = {
                    email: value,
                    _token: token
                }

                $.post(url, data, function(response) {
                    if (response.data != false) {
                        $('#existModalCenter').modal('show');
                    }
                });
            });

            // Google reCAPTCHA verification
            $('.verify-gcaptcha').on('submit', function(e) {
                if (typeof grecaptcha !== 'undefined') {
                    var response = grecaptcha.getResponse();
                    if (response.length == 0) {
                        var errorElement = document.getElementById('g-recaptcha-error');
                        if (errorElement) {
                            errorElement.innerHTML = '<span class="text--danger">@lang("Captcha field is required.")</span>';
                        } else {
                            alert('@lang("Captcha field is required.")');
                        }
                        e.preventDefault();
                        return false;
                    }
                }
                return true;
            });

            window.verifyCaptcha = function() {
                var errorElement = document.getElementById('g-recaptcha-error');
                if (errorElement) {
                    errorElement.innerHTML = '';
                }
            }
        })(jQuery);
    </script>
</body>
</html>
