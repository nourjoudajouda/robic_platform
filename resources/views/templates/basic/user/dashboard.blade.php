@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row gy-3">
        <div class="col-12">
            <div class="notice"></div>

            @php
                $kyc = getContent('kyc.content', true);
            @endphp

            @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
                <div class="alert alert--danger" role="alert">
                    <div class="d-flex justify-content-between align-items-center gap-1">
                        <h4 class="alert__title">@lang('KYC Documents Rejected')</h4>
                        <button class="btn btn--secondary btn--xsm" data-bs-toggle="modal" data-bs-target="#kycRejectionReason">@lang('Show Reason')</button>
                    </div>
                    <hr>
                    <p class="mb-0 alert__desc">{{ __(@$kyc->data_values->reject) }} <a href="{{ route('user.kyc.form') }}">@lang('Click Here to Re-submit Documents')</a>.</p>
                    <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a>
                </div>
            @elseif(auth()->user()->kv == Status::KYC_UNVERIFIED)
                <div class="alert alert--info" role="alert">
                    <h4 class="alert__title">@lang('KYC Verification required')</h4>
                    <hr>
                    <p class="mb-0 alert__desc">{{ __(@$kyc->data_values->required) }} <a href="{{ route('user.kyc.form') }}">@lang('Click Here to Submit Documents')</a></p>
                </div>
            @elseif(auth()->user()->kv == Status::KYC_PENDING)
                <div class="alert alert--warning" role="alert">
                    <h4 class="alert__title">@lang('KYC Verification pending')</h4>
                    <hr>
                    <p class="mb-0 alert__desc">{{ __(@$kyc->data_values->pending) }} <a href="{{ route('user.kyc.data') }}">@lang('See KYC Data')</a></p>
                </div>
            @endif
        </div>
        <div class="col-12">
            <div class="dashboard-column">
                <div class="dashboard-column__left">
                    <div class="dashboard-column__left-top">
                        <div class="welcome">
                            <h2 class="welcome__title">@lang('Welcome Back'), {{ $user->firstname }}!</h2>
                            <p class="welcome__desc">@lang('Your Dashboard Overview')</p>

                            <div class="account-item-wrapper">
                                <div class="balance account-item">
                                    <h6 class="account-item__title">@lang('My Balance')</h6>
                                    <h5 class="account-item__number">{{ showAmount($user->balance) }}</h5>
                                </div>
                                <div class="asset_value account-item">
                                    <h6 class="account-item__title">@lang('My Total Asset')</h6>
                                    <h5 class="account-item__number">{{ showAmount($assetValue) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="dashboard-card gradient-one gradient-two">
                            <div class="dashboard-card__top">
                                <p class="dashboard-card__title mb-0">
                                    <span class="dashboard-card__title-icon">
                                        <img src="{{ asset('/assets/images/coin.svg') }}" alt="image">
                                    </span>
                                    <span class="dashboard-card__title-text">
                                        @lang('Deposit Balance')
                                    </span>
                                </p>
                            </div>
                            <div class="dashboard-card__body text-center py-4">
                                <a href="{{ route('user.deposit.index') }}" class="btn btn--base btn--lg" style="max-width: 210px;">
                                    <i class="fas fa-wallet me-2"></i> @lang('Deposit Balance')
                                </a>
                                <p class="mt-3 mb-0 text-white-50 small">
                                    <i class="fas fa-info-circle me-1"></i> @lang('Add funds to your wallet')
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-column__left-bottom">
                        <div class="dashboard-card gradient-three gradient-four">
                            <p class="dashboard-card__title">
                                <span class="dashboard-card__title-icon">
                                    <img src="{{ asset($activeTemplateTrue . 'images/icons/17.png') }}" alt="image">
                                </span>
                                <span class="dashboard-card__title-text">
                                    @lang('Portfolio Overview')
                                </span>
                            </p>
                            @if ($portfolioData['total_asset_quantity'] > 0)
                                <div id="apex_chart_two"></div>
                            @else
                                <x-empty-card empty-message="No asset found" />
                            @endif
                        </div>
                        <div class="dashboard-card gradient-one gradient-two">
                            <div class="dashboard-card__top">
                                <p class="dashboard-card__title mb-0">
                                    <span class="dashboard-card__title-icon">
                                        <img src="{{ asset($activeTemplateTrue . 'images/icons/18.png') }}" alt="image">
                                    </span>
                                    <span class="dashboard-card__title-text">
                                        @lang('Green Coffee price')
                                    </span>
                                </p>
                                <div class="dashboard-card__top-right">
                                    <div class="customNiceSelect">
                                        <select name="days">
                                            <option value="1" {{ ($days ?? 90) == 1 ? 'selected' : '' }}>@lang('Last 24 Hours')</option>
                                            <option value="7" {{ ($days ?? 90) == 7 ? 'selected' : '' }}>@lang('Last 7 Days')</option>
                                            <option value="30" {{ ($days ?? 90) == 30 ? 'selected' : '' }}>@lang('Last 30 Days')</option>
                                            <option value="90" {{ ($days ?? 90) == 90 ? 'selected' : '' }}>@lang('Last 90 Days')</option>
                                            <option value="180" {{ ($days ?? 90) == 180 ? 'selected' : '' }}>@lang('Last 180 Days')</option>
                                            <option value="365" {{ ($days ?? 90) == 365 ? 'selected' : '' }}>@lang('Last 1 Year')</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="apex_chart_three"></div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-column__right">
                    <div class="dashboard-buttons mb-3">
                        <a href="{{ route('user.sell.form') }}" class="btn btn--danger btn--lg"><i class="fas fa-money-bill-trend-up"></i> @lang('Sell Green Coffee')</a>
                        @if (gs('redeem_option'))
                            <a href="{{ route('user.redeem.form') }}" class="btn btn--orange btn--lg"><i class="fas fa-truck"></i> @lang('Shipping and receiving')</a>
                        @endif
                        {{-- Gift feature disabled
                        @else
                            <a href="{{ route('user.gift.form') }}" class="btn btn--orange btn--lg"><i class="fas fa-gift"></i> @lang('Gift Green Coffee')</a>
                        @endif
                        --}}
                    </div>
                    <div class="dashboard-card gradient-three gradient-four">
                        <p class="dashboard-card__title">
                            <span class="dashboard-card__title-icon">
                                <img src="{{ asset($activeTemplateTrue . 'images/icons/19.png') }}" alt="image">
                            </span>
                            <span class="dashboard-card__title-text">
                                @lang('Buy Green Coffee')
                            </span>
                        </p>
                        <div class="text-center py-4">
                            <a href="{{ route('user.buy.form') }}" class="btn btn--base btn--lg">
                                <i class="fas fa-shopping-cart me-2"></i> @lang('Buy Green Coffee')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row gy-4 mt-4">
        <div class="col-xxl-7">
            <div class="dashboard-card">
                <div class="dashboard-card__top">
                    <p class="dashboard-card__title mb-0">
                        <span class="dashboard-card__title-icon">
                            <img src="{{ asset($activeTemplateTrue . 'images/icons/20.png') }}" alt="image">
                        </span>
                        <span class="dashboard-card__title-text">
                            @lang(gs('redeem_option') ? 'Recent Shipping History' : 'No History')
                        </span>
                    </p>
                    <a href="{{ route('user.asset.logs') }}?data=redeem-gift" class="btn btn--base btn--sm">@lang('View All')</a>
                </div>

                <ul class="gift-withdraw-list">
                    @forelse ($giftRedeems as $giftRedeem)
                        {{-- Skip gift history records --}}
                        @if ($giftRedeem->type == Status::GIFT_HISTORY)
                            @continue
                        @endif
                        <li class="gift-withdraw-list__item">
                            <div class="status">
                                <h6 class="status__title">@lang('Shipping and receiving')</h6>
                                <span class="status__date d-block">{{ $giftRedeem->created_at->format('M d, Y') }}</span>
                                <span class="status__time d-block">{{ $giftRedeem->created_at->format('h:i A') }}</span>
                            </div>
                            <div class="content">
                                <span class="content__title">@lang('Amount')</span>
                                <p class="content__info">{{ showAmount($giftRedeem->quantity, currencyFormat: false) }} {{ $giftRedeem->batch && $giftRedeem->batch->product && $giftRedeem->batch->product->unit ? $giftRedeem->batch->product->unit->symbol : 'Unit' }}</p>
                            </div>
                            <div class="content">
                                <span class="content__title">@lang('Delivery Address')</span>
                                <p class="content__info">{{ __(strLimit(substr($giftRedeem->redeemData->delivery_address, 9), 15)) }}</p>
                            </div>
                            <div class="gift-withdraw-list__item-badges">
                                @php
                                    echo $giftRedeem->redeemData->statusBadge;
                                @endphp
                            </div>
                        </li>
                    @empty
                        @if (gs('redeem_option'))
                            <x-empty-card empty-message="No redeem history found" />
                        @else
                            <x-empty-card empty-message="No history found" />
                        @endif
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-xxl-5">
            <div class="dashboard-card">
                <div class="dashboard-card__top">
                    <p class="dashboard-card__title mb-0">
                        <span class="dashboard-card__title-icon">
                            <img src="{{ asset($activeTemplateTrue . 'images/icons/21.png') }}" alt="image">
                        </span>
                        <span class="dashboard-card__title-text">
                            @lang('Recent Buy & Sell History')
                        </span>
                    </p>
                    <a href="{{ route('user.asset.logs') }}?data=buy-sell" class="btn btn--base btn--sm">@lang('View All')</a>
                </div>
                @if ($buySells->count())
                    <div class="dashboard-table">
                        <table class="table table--responsive--sm">
                            <thead>
                                <tr>
                                    <th>@lang('Date & Time')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Amount')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($buySells as $buySell)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="d-block">{{ $buySell->created_at->format('M d, Y') }}</span>
                                                <span class="d-block">{{ $buySell->created_at->format('h:i A') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                echo $buySell->statusBadge;
                                            @endphp
                                        </td>
                                        <td>{{ showAmount($buySell->quantity, currencyFormat: false) }} {{ $buySell->batch && $buySell->batch->product && $buySell->batch->product->unit ? $buySell->batch->product->unit->symbol : 'Unit' }}</td>
                                        <td>{{ showAmount($buySell->amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty-card empty-message="No buy & sell history found" />
                @endif

            </div>
        </div>
    </div>


    @if (auth()->user()->kv == Status::KYC_UNVERIFIED && auth()->user()->kyc_rejection_reason)
        <div class="modal custom--modal fade" id="kycRejectionReason">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">@lang('KYC Document Rejection Reason')</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>{{ auth()->user()->kyc_rejection_reason }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--danger btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";

            @if ($portfolioData['total_asset_quantity'] > 0)
                var options = {
                    series: @json($portfolioData['asset_category_quantity']),
                    chart: {
                        type: 'donut',
                        height: 294,
                    },
                    labels: @json($portfolioData['assets_category_name']),
                    legend: {
                        labels: {
                            colors: '#ffffff',
                            useSeriesColors: false
                        }
                    },
                    colors: ['#ffb22e', '#16C47F', '#CD853F', '#FFD65A', '#23953f', '#B43F3F'],

                    stroke: {
                        show: true,
                        colors: '#a4a19d',
                        width: 1
                    },

                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                var chart = new ApexCharts(document.querySelector("#apex_chart_two"), options);
                chart.render();
            @endif


            // كود التشارت (نفس منطق price tracker)
            let days = {{ $days ?? 90 }};
            
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
                    height: 256,
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
                    position: 'bottom',
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

@push('style')
@endpush

