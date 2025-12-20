/**
 * Home Page JavaScript
 * روييـك, عالاصل دوّر
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
        initLoginButton();
        initQuickLinks();
        initSmoothScroll();
        initAnimations();
        initBackgroundImage();
        initPriceChart();
    }

    /**
     * Initialize Login Button
     */
    function initLoginButton() {
        const loginBtn = document.getElementById('loginBtn');
        
        if (loginBtn) {
            // Add click animation
            loginBtn.addEventListener('click', function(e) {
                this.style.transform = 'scale(0.95)';
                
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });

            // Add hover effect
            loginBtn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.05)';
            });

            loginBtn.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        }
    }

    /**
     * Initialize Quick Links
     */
    function initQuickLinks() {
        const quickLinks = document.querySelectorAll('.quick-link-item');
        
        quickLinks.forEach((link, index) => {
            link.addEventListener('click', function(e) {
                // يمكنك إضافة معالجة النقر هنا
                console.log('تم النقر على:', this.querySelector('span').textContent);
                
                // إضافة تأثير النقر
                this.style.transform = 'scale(0.98)';
                
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });

            // إضافة تأثير عند المرور بالماوس
            link.addEventListener('mouseenter', function() {
                const icon = this.querySelector('i');
                if (icon) {
                    icon.style.transform = 'rotate(5deg) scale(1.1)';
                }
            });

            link.addEventListener('mouseleave', function() {
                const icon = this.querySelector('i');
                if (icon) {
                    icon.style.transform = '';
                }
            });
        });
    }

    /**
     * Initialize Smooth Scroll
     */
    function initSmoothScroll() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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
    }

    /**
     * Initialize Animations on Scroll
     */
    function initAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe elements
        const animatedElements = document.querySelectorAll('.login-card, .quick-link-item, .chart-card');
        animatedElements.forEach(el => {
            observer.observe(el);
        });
    }

    /**
     * Initialize Background Image
     * يمكنك تغيير الصورة هنا عند الحصول عليها
     */
    function initBackgroundImage() {
        const backgroundOverlay = document.querySelector('.background-overlay');
        
        // إذا كانت الصورة موجودة، سيتم تحميلها تلقائياً من CSS
        // يمكنك أيضاً تحميلها ديناميكياً هنا:
        
        // const bgImage = new Image();
        // bgImage.src = 'assets/images/background.jpg';
        // bgImage.onload = function() {
        //     backgroundOverlay.style.backgroundImage = `url(${this.src})`;
        // };
        
        // إضافة تأثير parallax خفيف
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            if (backgroundOverlay) {
                backgroundOverlay.style.transform = `translateY(${scrolled * 0.5}px)`;
            }
        });
    }

    /**
     * Initialize Price Chart
     */
    function initPriceChart() {
        // التحقق من وجود ApexCharts
        if (typeof ApexCharts === 'undefined') {
            console.warn('ApexCharts غير محمل');
            return;
        }

        const chartElement = document.getElementById('priceChart');
        if (!chartElement) {
            return;
        }

        // أوقات السوق من 8 ص إلى 4 م
        const times = ['8 ص', '9 ص', '10 ص', '11 ص', '12 ص', '1 م', '2 م', '3 م', '4 م'];

        // بيانات الأسعار لأنواع القهوة المختلفة
        // برازيلي - أزرق داكن - من 11 إلى 18 ريال
        const brazilian = [12, 13, 14, 12, 15, 16, 14, 17, 18];
        
        // كيني - أخضر داكن - من 9 إلى 15 ريال
        const kenyan = [10, 11, 12, 10, 13, 14, 12, 13, 14];
        
        // هندي - أخضر فاتح - من 8 إلى 14 ريال
        const indian = [9, 10, 11, 9, 12, 13, 11, 12, 13];
        
        // كولمبي - أصفر - من 6 إلى 12 ريال
        const colombian = [7, 8, 9, 7, 10, 11, 9, 10, 11];
        
        // فيتنامي - برتقالي - من 3 إلى 8 ريال
        const vietnamese = [4, 5, 6, 4, 7, 8, 6, 7, 7];
        
        // اندونيسي - أزرق فاتح - من 2 إلى 6 ريال
        const indonesian = [3, 4, 5, 3, 5, 6, 4, 5, 5];

        const options = {
            series: [
                {
                    name: 'برازيلي',
                    data: brazilian
                },
                {
                    name: 'كيني',
                    data: kenyan
                },
                {
                    name: 'هندي',
                    data: indian
                },
                {
                    name: 'كولمبي',
                    data: colombian
                },
                {
                    name: 'فيتنامي',
                    data: vietnamese
                },
                {
                    name: 'اندونيسي',
                    data: indonesian
                }
            ],
            chart: {
                type: 'line',
                height: 400,
                toolbar: {
                    show: true,
                    tools: {
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                },
                fontFamily: 'Cairo, sans-serif'
            },
            colors: ['#1e3a8a', '#166534', '#22c55e', '#eab308', '#f97316', '#06b6d4'], // ألوان الخطوط
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: times,
                title: {
                    text: 'وقت السوق',
                    style: {
                        color: '#666',
                        fontFamily: 'Cairo, sans-serif',
                        fontSize: '14px',
                        fontWeight: 600
                    }
                },
                labels: {
                    style: {
                        colors: '#666',
                        fontFamily: 'Cairo, sans-serif'
                    }
                }
            },
            yaxis: {
                title: {
                    text: 'أسعار البن الأخضر في السوق بالريال / كيلو',
                    style: {
                        color: '#666',
                        fontFamily: 'Cairo, sans-serif',
                        fontSize: '14px',
                        fontWeight: 600
                    }
                },
                min: 2,
                max: 18,
                tickAmount: 8,
                labels: {
                    style: {
                        colors: '#666',
                        fontFamily: 'Cairo, sans-serif'
                    },
                    formatter: function (val) {
                        return val.toFixed(0) + ' ريال';
                    }
                }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        return val.toFixed(0) + ' ريال';
                    }
                },
                style: {
                    fontFamily: 'Cairo, sans-serif'
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                fontFamily: 'Cairo, sans-serif',
                fontSize: '14px',
                fontWeight: 600
            },
            grid: {
                borderColor: '#e0e0e0',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            markers: {
                size: 4,
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: {
                    size: 6
                }
            }
        };

        const chart = new ApexCharts(chartElement, options);
        chart.render();

        // إضافة تأثير عند تحميل الصفحة
        setTimeout(() => {
            chartElement.style.opacity = '1';
        }, 500);
    }

    /**
     * Show Login Modal (Optional)
     */
    function showLoginModal() {
        // يمكنك إضافة modal لتسجيل الدخول هنا
        alert('سيتم فتح صفحة تسجيل الدخول');
    }

    /**
     * Utility: Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Handle Window Resize
     */
    window.addEventListener('resize', debounce(function() {
        // إعادة حساب المواضع إذا لزم الأمر
        console.log('تم تغيير حجم النافذة');
    }, 250));

    /**
     * Add Loading Animation
     */
    window.addEventListener('load', function() {
        document.body.classList.add('loaded');
        
        // إخفاء أي loader إذا كان موجوداً
        const loader = document.querySelector('.loader');
        if (loader) {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 300);
        }
    });

})();
