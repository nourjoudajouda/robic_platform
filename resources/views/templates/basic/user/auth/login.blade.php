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
    <title>@lang('Login') - {{ gs('site_name') }}</title>
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
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/home/css/login.css') }}">
</head>
<body>
    <!-- Main Container -->
    <div class="main-container">
        <!-- Login Section -->
        <section class="login-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-7 col-md-8">
                        <div class="login-card">
                            <div class="login-header">
                                <img src="{{ asset('assets/images/logo_icon/logo-footer.png') }}" alt="ROBIC Logo" class="login-logo">
                                <h2 class="login-welcome">
                                    @lang('Welcome to robic'),<br> <span class="welcome-highlight">@lang('Your trading platform for Green Coffee')</span>
                                </h2>
                            </div>

                            <!-- Account Type Tabs -->
                            <div class="account-type-tabs">
                                <button type="button" class="tab-btn" data-tab="establishment">
                                    @lang('Establishment account')
                                </button>
                                <button type="button" class="tab-btn active" data-tab="individual">
                                    @lang('Individual account')
                                </button>
                            </div>

                            <!-- Login Form -->
                            <form action="{{ route('user.login') }}" method="POST" class="login-form verify-gcaptcha" id="loginForm" autocomplete="off">
                                @csrf
                                
                                @if ($errors->any())
                                    <div class="alert alert-danger mb-3" role="alert">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label for="username" class="form-label">@lang('Username or Email')</label>
                                        <input type="text" name="username" value="{{ old('username') }}" class="form-control" id="username" placeholder="@lang('Enter email')" autocomplete="off" required>
                                    </div>

                                    <div class="form-group col-12">
                                        <label for="password" class="form-label">@lang('Password')</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" class="form-control" name="password" id="password" placeholder="@lang('Enter password')" autocomplete="new-password" required>
                                            <button type="button" class="password-toggle" data-target="password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <x-captcha />

                                    <div class="form-group col-12">
                                        <div class="form-options">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remember">@lang('Remember me')</label>
                                            </div>
                                            <a href="{{ route('user.password.request') }}" class="forgot-password-link">@lang('Forgot your password?')</a>
                                        </div>
                                    </div>

                                    <div class="form-group col-12">
                                        <button type="submit" class="btn btn-login w-100">@lang('Login now')</button>
                                    </div>

                                    <div class="col-12">
                                        <div class="register-link">
                                            <p>@lang('Don\'t have an account?') <a href="{{ route('user.register') }}">@lang('Register now')</a></p>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="divider">
                                            <span class="divider-text">@lang('or')</span>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        @if (@gs('socialite_credentials')->google->status == Status::ENABLE)
                                            <a href="{{ route('user.social.login', 'google') }}" class="btn btn-google-login">
                                                <img src="{{ asset('assets/templates/basic/images/google.svg') }}" alt="Google" class="google-icon">
                                                @lang('Login with Google')
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('assets/home/js/login.js') }}"></script>
</body>
</html>
