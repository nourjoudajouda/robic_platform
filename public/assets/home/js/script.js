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

        // الحصول على نطاق الأسعار من الإعدادات
        const priceFrom = window.chartSettings ? parseFloat(window.chartSettings.priceFrom) : 0;
        const priceTo = window.chartSettings ? parseFloat(window.chartSettings.priceTo) : 20;
        const apiUrl = window.chartSettings ? window.chartSettings.apiUrl : '/market-prices';

        // جلب البيانات الحقيقية من API
        fetch(apiUrl)
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success' && result.data.length > 0) {
                    renderChart(result.data, priceFrom, priceTo);
                } else {
                    console.warn('لا توجد بيانات أسعار متاحة');
                    renderEmptyChart(priceFrom, priceTo);
                }
            })
            .catch(error => {
                console.error('خطأ في جلب بيانات الأسعار:', error);
                renderEmptyChart(priceFrom, priceTo);
            });
    }

    /**
     * Generate colors for chart lines
     */
    function generateColors(count) {
        const colors = [
            '#1e3a8a', // أزرق داكن
            '#166534', // أخضر داكن
            '#22c55e', // أخضر
            '#eab308', // أصفر
            '#f97316', // برتقالي
            '#06b6d4', // أزرق فاتح
            '#ec4899', // وردي
            '#8b5cf6', // بنفسجي
            '#14b8a6', // تركواز
            '#f59e0b', // كهرماني
        ];
        return colors.slice(0, count);
    }

    /**
     * Render chart with real data
     */
    function renderChart(productsData, priceFrom, priceTo) {
        const chartElement = document.getElementById('priceChart');
        
        console.log('Products Data:', productsData);
        
        // جميع الساعات في اليوم (24 ساعة)
        const hours = [];
        for (let i = 0; i < 24; i++) {
            if (i === 0) {
                hours.push('12 ص');
            } else if (i < 12) {
                hours.push(i + ' ص');
            } else if (i === 12) {
                hours.push('12 م');
            } else {
                hours.push((i - 12) + ' م');
            }
        }

        // تحويل بيانات المنتجات إلى series
        const series = productsData.map(product => ({
            name: product.name,
            data: product.data
        }));
        
        console.log('Series:', series);
        console.log('Series length:', series.length);
        console.log('First product name:', series[0]?.name);

        const options = {
            series: series,
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
                        reset: true,
                        download: true
                    },
                    export: {
                        csv: {
                            filename: 'market-prices',
                            headerCategory: 'الوقت',
                            dateFormatter(timestamp) {
                                return new Date(timestamp).toLocaleDateString('ar-SA')
                            }
                        },
                        svg: {
                            filename: 'market-prices-chart',
                        },
                        png: {
                            filename: 'market-prices-chart',
                        }
                    }
                },
                fontFamily: 'Cairo, sans-serif'
            },
            colors: generateColors(series.length), // ألوان ديناميكية حسب عدد المنتجات
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: hours,
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
                min: priceFrom,
                max: priceTo,
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
                        return val.toFixed(2) + ' ريال';
                    }
                },
                style: {
                    fontFamily: 'Cairo, sans-serif'
                }
            },
            legend: {
                show: true,
                showForSingleSeries: true,
                position: 'top',
                horizontalAlign: 'center',
                floating: false,
                fontFamily: 'Cairo, sans-serif',
                fontSize: '16px',
                fontWeight: 700,
                labels: {
                    colors: '#000',
                    useSeriesColors: false
                },
                markers: {
                    width: 16,
                    height: 16,
                    radius: 4,
                    strokeWidth: 0,
                    offsetX: -5
                },
                itemMargin: {
                    horizontal: 15,
                    vertical: 8
                },
                offsetY: 10
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
                size: 5,
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: {
                    size: 7
                }
            }
        };

        console.log('Chart Options:', options);
        
        const chart = new ApexCharts(chartElement, options);
        chart.render();

        // إضافة تأثير عند تحميل الصفحة
        setTimeout(() => {
            chartElement.style.opacity = '1';
        }, 500);
    }

    /**
     * Render empty chart (fallback)
     */
    function renderEmptyChart(priceFrom, priceTo) {
        const chartElement = document.getElementById('priceChart');
        
        // جميع الساعات في اليوم (24 ساعة)
        const hours = [];
        for (let i = 0; i < 24; i++) {
            if (i === 0) {
                hours.push('12 ص');
            } else if (i < 12) {
                hours.push(i + ' ص');
            } else if (i === 12) {
                hours.push('12 م');
            } else {
                hours.push((i - 12) + ' م');
            }
        }

        const emptyData = Array(24).fill(0);

        const options = {
            series: [{
                name: 'لا توجد بيانات',
                data: emptyData
            }],
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
            colors: ['#cccccc'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            xaxis: {
                categories: hours,
                title: {
                    text: 'ساعات اليوم',
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
                min: priceFrom,
                max: priceTo,
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
                show: true,
                showForSingleSeries: true,
                position: 'top',
                horizontalAlign: 'center',
                floating: false,
                fontFamily: 'Cairo, sans-serif',
                fontSize: '16px',
                fontWeight: 700,
                labels: {
                    colors: '#000',
                    useSeriesColors: false
                },
                markers: {
                    width: 16,
                    height: 16,
                    radius: 4
                },
                itemMargin: {
                    horizontal: 15,
                    vertical: 8
                }
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
            },
            noData: {
                text: 'لا توجد بيانات أسعار متاحة',
                align: 'center',
                verticalAlign: 'middle',
                style: {
                    color: '#666',
                    fontSize: '16px',
                    fontFamily: 'Cairo, sans-serif'
                }
            }
        };

        const chart = new ApexCharts(chartElement, options);
        chart.render();
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

