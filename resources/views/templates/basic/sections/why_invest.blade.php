@php
    $whyInvest = getContent('why_invest.content', true);
    $categories = App\Models\Category::active()->get();
    $chargeLimit = App\Models\ChargeLimit::where('slug', 'buy')->first();
@endphp
<section class="blog-section section-bg py-60">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-12">
                <div class="section-heading">
                    <h2 class="section-heading__title">{{ __($whyInvest->data_values->heading) }}</h2>
                    <p class="section-heading__desc">
                        {{ __($whyInvest->data_values->subheading) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="gradient-border">
                    <div class="bean-price">
                        <div class="bean-price-header">
                            <h4 class="bean-price-header__title">@lang('Green Coffee Price in ') {{ __(gs('cur_text')) }}</h4>
                            <p class="bean-price-header__desc">
                                @lang('High'):<span class="highPrice me-3"></span>
                                @lang('Low'):<span class="lowPrice"></span>
                                <span class="amountChangeClass"><i class="fa-solid fa-caret-down iconClass"></i> 
                                    <span class="amountChange"></span>
                                    <span class="percentageChange"></span>
                                </span>
                            </p>
                            <div class="customNiceSelect">
                                <select name="priceDays">
                                    <option value="30">@lang('30 Days')</option>
                                    <option value="90">@lang('90 Days')</option>
                                    <option value="180">@lang('180 Days')</option>
                                    <option value="365">@lang('1 Year')</option>
                                    <option value="1825">@lang('5 Years')</option>
                                    <option value="2920">@lang('8 Years')</option>
                                    <option value="3650" selected>@lang('10 Years')</option>
                                </select>
                            </div>
                        </div>
                        <div id="apex_chart_one"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row gy-4 pt-60">
            <div class="col-xxl-5 col-lg-6">
                <div class="gradient-border">
                    <div class="bean-rate">
                        <h5 class="bean-rate__title">@lang('Today\'s Bean Rate')</h5>
                        <ul class="bean-rate__list">
                            @foreach ($categories as $category)
                                <li class="bean-rate__list-item">
                                    <span class="product">{{ $category->name }}</span>
                                    <span class="price">{{ showAmount($category->price) }}/@lang('gram')</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xxl-7 col-lg-6">
                <div class="gradient-border">
                    <div class="bean-calculator">
                        <h5 class="bean-calculator__title">@lang('Bean Price Calculator')</h5>
                        <form class="bean-calculator__form">
                            <div class="bean-calculator__top">
                                <div class="bean-category">
                                    <span class="bean-category__title">@lang('Product Quality')</span>
                                    <div class="customNiceSelect">
                                        <select name="category">
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" data-price="{{ $category->price }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="calculator-switch">
                                    <div class="calculator-switch__item">
                                        <input class="form-check-input" type="radio" name="beanCalculatorSwitch" id="beanCalculatorSwitch1" value="1" checked>
                                        <label class="text cursor-pointer" for="beanCalculatorSwitch1">@lang('Calculate in '){{ __(gs('cur_text')) }}</label>
                                    </div>
                                    <span class="calculator-switch__icon"><i class="fa-solid fa-right-left"></i></span>
                                    <div class="calculator-switch__item">
                                        <input class="form-check-input" type="radio" name="beanCalculatorSwitch" id="beanCalculatorSwitch2" value="2">
                                        <label class="text cursor-pointer" for="beanCalculatorSwitch2">@lang('Calculate in Quantity')</label>
                                    </div>
                                </div>
                            </div>
                            <div class="bean-calculator__inputs">
                                <div class="form-group">
                                    <label class="form--label">{{ __(gs('cur_text')) }}</label>
                                    <input type="number" step="any" class="form--control" placeholder="0.00" name="amount">
                                </div>
                                <span class="equal"><i class="fa-solid fa-equals"></i></span>
                                <div class="form-group">
                                    <label class="form--label">@lang('Gram Green Coffee')</label>
                                    <input type="number" step="any" class="form--control" placeholder="0.00" name="gram" disabled>
                                </div>
                            </div>

                            <div class="bean-calculator__bottom">
                                @if ($chargeLimit->fixed_charge || $chargeLimit->percent_charge || $chargeLimit->vat)
                                    <span class="info font-small"><i class="fa-solid fa-circle-info me-1"></i>
                                        {{ getChargeText($chargeLimit) }}
                                    </span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@push('script')
    <script>
        (function($) {
            "use strict";


            $('[name=priceDays]').on('change', function() {
                let days = $(this).val();
                $('.highPrice').text('--');
                $('.lowPrice').text('--');
                $('.amountChange').text('--');
                $('.percentageChange').text('--');

                $.get(`{{ route('bean.price') }}`, {days}, function(response) {
                    if (response.status == 'error') {
                        notify('error', response.message);
                        return false;
                    }

                    let highPrice = response.data.max_price;
                    let lowPrice = response.data.min_price;
                    $('.highPrice').text(highPrice);
                    $('.lowPrice').text(lowPrice);

                    $('.amountChange').text(response.data.amount_change);
                    $('.percentageChange').text(response.data.percentage);

                    if(response.data.amount_change_direction == 'up'){
                        $('.amountChangeClass').addClass('text--success');
                        $('.amountChangeClass').removeClass('text--danger');
                        $('.iconClass').addClass('fa-caret-up');
                        $('.iconClass').removeClass('fa-caret-down');
                    }else{
                        $('.amountChangeClass').addClass('text--danger');
                        $('.amountChangeClass').removeClass('text--success');
                        $('.iconClass').addClass('fa-caret-down');
                        $('.iconClass').removeClass('fa-caret-up');
                    }

                    updateChart(response.data.bean_price);
                });

            }).trigger('change');

            function updateChart(data) {
                chart.updateOptions({
                    series: [{
                        name: 'Bean Price',
                        data: data.map(item => Number(item.max_price).toFixed(2))
                    }],
                    xaxis: {
                        categories: data.map(item => item.price_date)
                    }
                });
            }

            let baseColor = `#{{ gs('base_color') }}`;

            var options = {
                series: [{
                    name: 'Bean Price',
                    data: []
                }],
                chart: {
                    height: 450,
                    type: 'bar',
                },
                xaxis: {
                    categories: []
                },
                plotOptions: {
                    bar: {
                        columnWidth: '40%'
                    }
                },
                colors: [baseColor],
                dataLabels: {
                    enabled: false
                },
                xaxis: {
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
                    theme: 'dark',
                }
            };
            var chart = new ApexCharts(document.querySelector("#apex_chart_one"), options);
            chart.render();


            // Bean Price Calculator

            let amount = 0;
            let gram = 0;
            let categoryPrice = 0;

            $('[name=category]').on('change', function() {
                categoryPrice = $(this).find('option:selected').data('price');
                calculateBeanPriceQuantity();
            }).trigger('change');

            $('[name=amount]').on('keyup', function() {
                amount = $(this).val();
                calculateBeanPriceQuantity();
            });

            $('[name=gram]').on('keyup', function() {
                gram = $(this).val();
                calculateBeanPriceQuantity();
            });

            function calculateBeanPriceQuantity() {
                let switchValue = $('[name=beanCalculatorSwitch]:checked').val();
                if (switchValue == 1) {
                    $('[name=gram]').val(parseFloat(amount / categoryPrice).toFixed(8));
                } else {
                    $('[name=amount]').val(parseFloat(gram * categoryPrice).toFixed(2));
                }
            }

            $('[name=beanCalculatorSwitch]').on('change', function() {
                let switchValue = $(this).val();

                if (switchValue == 1) {
                    $('[name=gram]').attr('disabled', true);
                    $('[name=amount]').attr('disabled', false);
                } else {
                    $('[name=amount]').attr('disabled', true);
                    $('[name=gram]').attr('disabled', false);
                }
            });


        })(jQuery);
    </script>
@endpush
