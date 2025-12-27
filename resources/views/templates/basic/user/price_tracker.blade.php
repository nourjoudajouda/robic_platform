@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-card gradient-one gradient-two lg">
        <div class="dashboard-card__top">
            <div class="dashboard-card__top-left">
                <span class="liveprice">@lang('Price Tracker')</span>
                <h2 class="price">@lang('All Products')</h2>
            </div>
            <div class="dashboard-card__top-right">
                <div class="customNiceSelect">
                    <select name="days">
                        <option value="1" {{ ($days ?? 1) == 1 ? 'selected' : '' }}>@lang('Last 24 Hours')</option>
                        <option value="7" {{ ($days ?? 1) == 7 ? 'selected' : '' }}>@lang('Last 7 Days')</option>
                        <option value="30" {{ ($days ?? 1) == 30 ? 'selected' : '' }}>@lang('Last 30 Days')</option>
                        <option value="90" {{ ($days ?? 1) == 90 ? 'selected' : '' }}>@lang('Last 90 Days')</option>
                        <option value="180" {{ ($days ?? 1) == 180 ? 'selected' : '' }}>@lang('Last 180 Days')</option>
                        <option value="365" {{ ($days ?? 1) == 365 ? 'selected' : '' }}>@lang('Last 1 Year')</option>
                    </select>
                </div>
            </div>
        </div>
        
        @if($products && $products->count() > 0)
        <div class="products-list" style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 15px;">
            <h4 style="color: #fff; margin-bottom: 10px; font-size: 14px; font-weight: 600;">@lang('Products in Chart'):</h4>
            <div class="row">
                @foreach($products as $index => $product)
                    @php
                        $colors = [
                            gs('base_color'),
                            '#16C47F',
                            '#ffb22e',
                            '#23953f',
                            '#CD853F',
                            '#FFD65A',
                            '#B43F3F',
                            '#8b5cf6',
                            '#06b6d4',
                            '#f97316'
                        ];
                        $color = $colors[$index % count($colors)] ?? gs('base_color');
                        // التأكد من وجود # في اللون
                        $colorValue = (strpos($color, '#') === 0) ? $color : '#' . $color;
                    @endphp
                    <div class="col-md-3 col-sm-4 col-6" style="margin-bottom: 10px;">
                        <div class="product-item" style="display: flex; align-items: center; gap: 8px;">
                            <span class="product-color" style="width: 16px; height: 16px; background: {{ $colorValue }}; border-radius: 4px; display: inline-block;"></span>
                            <span class="product-name" style="color: #fff; font-size: 13px;">{{ $product->name }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <div id="apex_chart_three"></div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/30.png') }}" alt="image">
@endsection


@push('script')
    <script>
        (function($) {

            "use strict";

            let days = {{ $days ?? 1 }};
            
            // ألوان مختلفة لكل منتج
            const productColors = [
                '#{{ gs('base_color') }}', // اللون الأساسي
                '#16C47F', // أخضر
                '#ffb22e', // أصفر
                '#23953f', // أخضر داكن
                '#CD853F', // بني
                '#FFD65A', // أصفر فاتح
                '#B43F3F', // أحمر
                '#8b5cf6', // بنفسجي
                '#06b6d4', // أزرق فاتح
                '#f97316'  // برتقالي
            ];
            
            // الساعات بالعربية
            let hoursArabic = [
                '12 ص', '1 ص', '2 ص', '3 ص', '4 ص', '5 ص', 
                '6 ص', '7 ص', '8 ص', '9 ص', '10 ص', '11 ص',
                '12 م', '1 م', '2 م', '3 م', '4 م', '5 م', 
                '6 م', '7 م', '8 م', '9 م', '10 م', '11 م'
            ];

            // تغيير الفترة
            $('[name="days"]').on('change', function() {
                days = $(this).val();
                updateChart();
            });

            function getXAxisLabels(labels, daysFilter) {
                if (daysFilter == 1) {
                    // عرض الساعات
                    return hoursArabic;
                } else if (daysFilter <= 30) {
                    // للفترات القصيرة (7، 30 يوم): عرض التاريخ بصيغة رقمية (يوم/شهر)
                    return labels.map(function(date) {
                        let d = new Date(date);
                        let day = d.getDate();
                        let month = d.getMonth() + 1;
                        return day + '/' + month;
                    });
                } else {
                    // للفترات الطويلة (90، 180، 365 يوم): عرض اسم الشهر
                    const monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 
                                       'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
                    return labels.map(function(date) {
                        let d = new Date(date);
                        let day = d.getDate();
                        let month = monthNames[d.getMonth()];
                        return day + ' ' + month;
                    });
                }
            }

            function updateChart() {
                $.get("{{ route('user.price.tracker') }}", {
                    days
                }, function(response) {
                    // تحويل البيانات لصيغة series
                    const series = response.products.map(function(product, index) {
                        return {
                            name: product.name,
                            data: product.data
                        };
                    });

                    let xAxisLabels = getXAxisLabels(response.labels, response.days);

                    chart.updateOptions({
                        series: series,
                        colors: productColors.slice(0, series.length),
                        xaxis: {
                            categories: xAxisLabels,
                            labels: {
                                style: {
                                    colors: '#fff',
                                }
                            }
                        }
                    });
                });
            }

            // تحديد التسميات الأولية
            let initialLabels = @json($labels ?? []);
            let xAxisLabels = getXAxisLabels(initialLabels, days);

            // تحويل البيانات الأولية لصيغة series
            const initialSeries = @json($allProductsData ?? []).map(function(product, index) {
                return {
                    name: product.name,
                    data: product.data
                };
            });

            var options = {
                series: initialSeries,
                colors: productColors.slice(0, initialSeries.length),
                chart: {
                    height: 400,
                    type: 'line',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                xaxis: {
                    categories: xAxisLabels,
                    labels: {
                        style: {
                            colors: '#fff',
                        }
                    }
                },
                yaxis: {
                    min: {{ $priceFrom ?? 0 }},
                    max: {{ $priceTo ?? 20 }},
                    labels: {
                        style: {
                            colors: '#fff',
                        },
                        formatter: function(value) {
                            return value.toFixed(2);
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function(value) {
                            return value.toFixed(2) + ' {{ __(gs("cur_text")) }}';
                        }
                    }
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'center',
                    labels: {
                        colors: '#fff',
                        useSeriesColors: false
                    },
                    markers: {
                        width: 12,
                        height: 12,
                        radius: 6
                    }
                },
                grid: {
                    borderColor: 'rgba(255, 255, 255, 0.1)'
                }
            };
            
            var chart = new ApexCharts(document.querySelector("#apex_chart_three"), options);
            chart.render();

        })(jQuery);
    </script>
@endpush
