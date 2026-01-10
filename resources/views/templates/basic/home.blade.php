<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>روبيك, عالاصل دوّر - استثمر في Green Coffee</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/logo_icon/favicon.png') }}" type="image/x-icon">
    
    <!-- Bootstrap 5 RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts - Arabic -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/home/css/style.css') }}">
</head>
<body>
    <!-- Main Container -->
    <div class="main-container">
        <!-- Header Section -->
        <header class="header-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <div class="header-logo">
                            <img src="{{ asset('assets/images/logo_icon/main-logo.png') }}" alt="ROBIC Logo" class="header-logo-img">
                        </div>
                        <h1 class="main-title">روبيك, عالاصل دوّر</h1>
                        <p class="main-subtitle">استثمر في Green Coffee... مستقبلك التجاري يبدأ من الحبة الأولى</p>
                    </div>
                </div>
            </div>
        </header>

        

        <!-- Chart Section -->
        <section class="chart-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 col-md-12">
                        <div class="chart-card">
                            <h3 class="chart-title">أسعار Green Coffee في السوق بالريال / كيلو</h3>
                            <div id="priceChart" class="chart-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Login Section -->
        <section class="login-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 col-md-10">
                        <div class="login-card">
                            <h2 class="login-title">قم بتسجيل الدخول في المنصة و ما تفوت فرصتك في التداول</h2>
                            <a href="{{ route('user.login') }}" class="btn btn-login" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                تسجيل دخول
                            </a>
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
                        <h4 class="footer-title">روبيك, عالاصل دوّر</h4>
                        <p class="footer-description">استثمر في Green Coffee... مستقبلك التجاري يبدأ من الحبة الأولى</p>
                       
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
                        <p class="copyright">&copy; 2024 روبيك, عالاصل دوّر. جميع الحقوق محفوظة.</p>
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
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Chart Settings -->
    <script>
        window.chartSettings = {
            priceFrom: {{ gs('chart_price_from') ?? 0 }},
            priceTo: {{ gs('chart_price_to') ?? 20 }},
            apiUrl: "{{ route('market.prices') }}"
        };
    </script>
    
    <!-- Custom JS -->
    <script src="{{ asset('assets/home/js/script.js') }}"></script>
</body>
</html>
