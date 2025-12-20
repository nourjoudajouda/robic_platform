@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="dashboard-card gradient-one gradient-two lg">
        <div class="dashboard-card__top">
            <div class="dashboard-card__top-left">
                <span class="liveprice">@lang('Last Price')</span>
                <h2 class="price lastPrice">{{ $category ? showAmount($category->price) : 0 }}/@lang('gram')</h2>
                @if($category)
                <div class="positive {{ ($category->change_90d ?? 0) < 0 ? 'd-none' : '' }}">
                    <span class="badge badge--success"><i class="las la-arrow-right"></i> <span class="priceChange">{{ showAmount(($category->price * ($category->change_90d ?? 0)) / 100) }}</span>/@lang('gram')</span>
                    <span class="badge badge--success"><span class="percentChange">{{ $category->change_90d ?? 0 }}</span>%</span>
                </div>
                <div class="negative {{ ($category->change_90d ?? 0) >= 0 ? 'd-none' : '' }}">
                    <span class="badge badge--danger"><i class="las la-arrow-left"></i> <span class="priceChange">{{ showAmount(($category->price * abs($category->change_90d ?? 0)) / 100) }}</span>/@lang('gram')</span>
                    <span class="badge badge--danger"><span class="percentChange">{{ abs($category->change_90d ?? 0) }}</span>%</span>
                </div>
                @else
                <div class="alert alert-warning">
                    <p>@lang('No active categories found. Please add categories from admin panel.')</p>
                </div>
                @endif
            </div>
            <div class="dashboard-card__top-right">
                <div class="customNiceSelect">
                    <select name="days">
                        <option value="7">@lang('Last 7 Days')</option>
                        <option value="30">@lang('Last 30 Days')</option>
                        <option value="90" selected>@lang('Last 90 Days')</option>
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
    @if($categories && $categories->count() > 0)
    <div class="price-tracker-tap">
        @foreach ($categories as $singleCategory)
            <button class="price-tracker-tap__item categoryBtn {{ $loop->first ? 'active' : '' }}" data-category="{{ $singleCategory->id }}"><span>{{ $singleCategory->name }}</span></button>
        @endforeach
    </div>
    @endif
@endpush


@push('script')
    <script>
        (function($) {

            "use strict";

            let category = `{{ $category ? $category->id : '' }}`;
            let days = 0;

            $('[name="days"]').on('change', function() {
                days = $(this).val();
                updateChart();
            }).change();

            $('.categoryBtn').on('click', function() {
                category = $(this).data('category');
                $('.categoryBtn').removeClass('active');
                $(this).addClass('active');
                updateChart();
            });

            function updateChart() {
                $.get("{{ route('user.price.tracker') }}", {
                    category,
                    days
                }, function(response) {
                    let percentChange = response.percent_change;
                    let priceChange = response.price_change;

                    $('.percentChange').text(parseFloat(percentChange).toFixed(2));
                    $('.priceChange').text(Math.abs(parseFloat(priceChange).toFixed(2)));

                    if (percentChange > 0) {
                        $('.positive').removeClass('d-none');
                        $('.negative').addClass('d-none');
                    } else {
                        $('.positive').addClass('d-none');
                        $('.negative').removeClass('d-none');
                    }

                    if (percentChange === null) {
                        $('.positive').addClass('d-none');
                        $('.negative').addClass('d-none');
                    }

                    chart.updateOptions({
                        series: [{
                            name: 'Green Coffee Price',
                            data: response.prices.map(price => price.price)
                        }],
                        xaxis: {
                            categories: response.prices.map(price => price.date)
                        }
                    });
                });
            }

            let baseColor = `#{{ gs('base_color') }}`;
            let secondaryColor = `#{{ gs('secondary_color') }}`;

            var options = {
                series: [{
                    name: 'Gold Price',
                    data: @json($prices ? $prices->pluck('price') : [])
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
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'datetime',
                    categories: @json($prices ? $prices->pluck('date') : []),
                    labels: {
                        style: {
                            colors: '#fff',
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#fff',
                        }
                    }
                },
                tooltip: {
                    x: {
                        format: 'dd/MM/yy'
                    },
                    theme: 'dark',
                },
            };
            @if($category && $prices && $prices->count() > 0)
            var chart = new ApexCharts(document.querySelector("#apex_chart_three"), options);
            chart.render();
            @endif

        })(jQuery);
    </script>
@endpush
