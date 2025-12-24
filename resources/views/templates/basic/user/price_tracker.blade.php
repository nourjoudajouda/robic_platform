@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-card gradient-one gradient-two lg">
        <div class="dashboard-card__top">
            <div class="dashboard-card__top-left">
                <span class="liveprice">@lang('Last Price')</span>
                <h2 class="price lastPrice">
                    {{ showAmount($currentPrice ?? 0) }}
                    @if($product && $product->unit)
                        /<span class="unitName">{{ $product->unit->name }}</span>
                    @endif
                </h2>
                <div class="positive {{ ($percentChange ?? 0) < 0 ? 'd-none' : '' }}">
                    <span class="badge badge--success">
                        <i class="las la-arrow-right"></i> 
                        <span class="priceChange">{{ showAmount(abs($priceChange ?? 0)) }}</span>
                        @if($product && $product->unit)/<span class="unitName">{{ $product->unit->name }}</span>@endif
                    </span>
                    <span class="badge badge--success"><span class="percentChange">{{ number_format($percentChange ?? 0, 2) }}</span>%</span>
                </div>
                <div class="negative {{ ($percentChange ?? 0) >= 0 ? 'd-none' : '' }}">
                    <span class="badge badge--danger">
                        <i class="las la-arrow-left"></i> 
                        <span class="priceChange">{{ showAmount(abs($priceChange ?? 0)) }}</span>
                        @if($product && $product->unit)/<span class="unitName">{{ $product->unit->name }}</span>@endif
                    </span>
                    <span class="badge badge--danger"><span class="percentChange">{{ number_format(abs($percentChange ?? 0), 2) }}</span>%</span>
                </div>
            </div>
            <div class="dashboard-card__top-right">
                <div class="customNiceSelect">
                    <select name="days">
                        <option value="1" selected>@lang('Last 24 Hours')</option>
                        <option value="7">@lang('Last 7 Days')</option>
                        <option value="30">@lang('Last 30 Days')</option>
                        <option value="90">@lang('Last 90 Days')</option>
                        <option value="180">@lang('Last 180 Days')</option>
                        <option value="365">@lang('Last 1 Year')</option>
                    </select>
                </div>
            </div>
        </div>
        <div id="apex_chart_three"></div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/30.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    @if($products && $products->count() > 0)
    <div class="price-tracker-tap">
        @foreach ($products as $singleProduct)
            <button class="price-tracker-tap__item productBtn {{ $loop->first ? 'active' : '' }}" data-product="{{ $singleProduct->id }}">
                <span>{{ $singleProduct->name }}</span>
            </button>
        @endforeach
    </div>
    @endif
@endpush


@push('script')
    <script>
        (function($) {

            "use strict";

            let product = `{{ $product ? $product->id : '' }}`;
            let days = {{ $days ?? 1 }};
            let baseColor = `#{{ gs('base_color') }}`;
            let secondaryColor = `#{{ gs('secondary_color') }}`;
            
            // الساعات بالعربية
            let hoursArabic = [
                '12 ص', '1 ص', '2 ص', '3 ص', '4 ص', '5 ص', 
                '6 ص', '7 ص', '8 ص', '9 ص', '10 ص', '11 ص',
                '12 م', '1 م', '2 م', '3 م', '4 م', '5 م', 
                '6 م', '7 م', '8 م', '9 م', '10 م', '11 م'
            ];

            // تغيير المنتج
            $('.productBtn').on('click', function() {
                product = $(this).data('product');
                $('.productBtn').removeClass('active');
                $(this).addClass('active');
                updateChart();
            });

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
                    product,
                    days
                }, function(response) {
                    let percentChange = response.percent_change;
                    let priceChange = response.price_change;
                    let currentPrice = response.current_price;
                    let unit = response.unit || 'unit';

                    // تحديث السعر الحالي
                    $('.lastPrice').html(currentPrice + '/<span class="unitName">' + unit + '</span>');

                    // تحديث نسبة التغيير
                    $('.percentChange').text(parseFloat(percentChange).toFixed(2));
                    $('.priceChange').text(Math.abs(parseFloat(priceChange).toFixed(2)));

                    if (percentChange > 0) {
                        $('.positive').removeClass('d-none');
                        $('.negative').addClass('d-none');
                    } else if (percentChange < 0) {
                        $('.positive').addClass('d-none');
                        $('.negative').removeClass('d-none');
                    } else {
                        $('.positive').addClass('d-none');
                        $('.negative').addClass('d-none');
                    }

                    let xAxisLabels = getXAxisLabels(response.labels, response.days);

                    chart.updateOptions({
                        series: [{
                            name: 'Market Price',
                            data: response.prices
                        }],
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
            let initialLabels = @json($labels);
            let xAxisLabels = getXAxisLabels(initialLabels, days);

            var options = {
                series: [{
                    name: '@lang("Market Price")',
                    data: @json($chartData)
                }],
                colors: [baseColor],
                fill: {
                    type: 'gradient',
                    gradient: {
                        type: 'vertical',
                        shade: 'light',
                        gradientToColors: [secondaryColor],
                        stops: [0, 100]
                    }
                },

                chart: {
                    height: 256,
                    type: 'area',
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
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
                grid: {
                    borderColor: 'rgba(255, 255, 255, 0.1)'
                }
            };
            
            var chart = new ApexCharts(document.querySelector("#apex_chart_three"), options);
            chart.render();

        })(jQuery);
    </script>
@endpush
