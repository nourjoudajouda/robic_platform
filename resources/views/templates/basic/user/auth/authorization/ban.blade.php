<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حساب محظور - روبيك</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo_icon/favicon.png') }}" type="image/x-icon">
    
    <!-- Bootstrap 5 RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/home/css/otp.css') }}">
    <style>
        .ban-card {
            background: #fff;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        .ban-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Main Container -->
    <div class="main-container">
        <!-- Ban Section -->
        <section class="otp-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-6 col-md-7 col-sm-9">
                        <div class="otp-card">
                            <!-- Logo -->
                            <div class="otp-header">
                                <img src="{{ asset('assets/images/logo_icon/logo-footer.png') }}" alt="ROBIC Logo" class="otp-logo">
                            </div>

                            <!-- Ban Icon -->
                            <div class="padlock-container">
                                <i class="fas fa-ban ban-icon"></i>
                            </div>

                            <!-- Title -->
                            <h2 class="otp-title text-danger">@lang('You are banned')</h2>

                            <!-- Reason -->
                            <div class="ban-card mt-4">
                                <p class="fw-bold mb-2">@lang('Reason'):</p>
                                <p class="text-muted">{{ $user->ban_reason ?? __('No reason provided') }}</p>
                            </div>

                            <!-- Logout Link -->
                            <div class="resend-section mt-4">
                                <p class="resend-text">
                                    <a href="{{ route('user.logout') }}">@lang('Logout')</a>
                                </p>
                            </div>
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
                        <h4 class="footer-title">روييـك, عالاصل دوّر</h4>
                        <p class="footer-description">Invest in Green Coffee... Your commercial future starts from the first bean</p>
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
    <!-- Notify -->
    @include('partials.notify')
</body>
</html>
