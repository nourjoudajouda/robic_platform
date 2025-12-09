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
                                        <img src="{{ asset($activeTemplateTrue . 'images/icons/16.png') }}" alt="image">
                                    </span>
                                    <span class="dashboard-card__title-text">
                                        @lang('Total Gold Holdings')
                                    </span>
                                </p>
                                @if ($assets->count())
                                    <div class="customNiceSelect">
                                        <select name="asset">
                                            @foreach ($assets as $asset)
                                                <option value="{{ $asset->id }}" data-quantity="{{ showAmount($asset->quantity, currencyFormat: false) }}" data-total_amount="{{ showAmount($asset->quantity * $asset->category->price) }}" data-price_change="{{ getAmount(($asset->quantity * $asset->category->price * $asset->category->change_90d) / 100) }}" data-percent_change="{{ $asset->category->change_90d }}">{{ $asset->category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>
                            @if ($assets->count())
                                <h2 class="dashboard-card__gold"><span class="goldQuantity"></span> <sub>@lang('Gram Gold')</sub></h2>
                                <p class="dashboard-card__desc">
                                    <span class="goldTotalAmount"></span>
                                    <span class="text--success percentChangeBadge"><i class="fa-solid fa-caret-down"></i>
                                        <span class="goldPriceChange"></span> <span class="goldPercentChange"></span>
                                        <span>(@lang('90 days'))</span>
                                    </span>
                                </p>
                            @else
                                <x-empty-card empty-message="No asset found" style="2" />
                            @endif


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
                            @if ($assets->sum('quantity') > 0)
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
                                        @lang('Gold price')
                                    </span>
                                </p>
                                <div class="dashboard-card__top-right">
                                    <div class="customNiceSelect">
                                        <select name="days">
                                            <option value="7">@lang('7 Days')</option>
                                            <option value="30">@lang('30 Days')</option>
                                            <option value="90" selected>@lang('90 Days')</option>
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
                        <a href="{{ route('user.sell.form') }}" class="btn btn--danger btn--lg"><i class="fas fa-money-bill-trend-up"></i> @lang('Sell Gold')</a>
                        @if (gs('redeem_option'))
                            <a href="{{ route('user.redeem.form') }}" class="btn btn--orange btn--lg"><i class="fas fa-truck"></i> @lang('Redeem Gold')</a>
                        @else
                            <a href="{{ route('user.gift.form') }}" class="btn btn--orange btn--lg"><i class="fas fa-gift"></i> @lang('Gift Gold')</a>
                        @endif
                    </div>
                    <div class="dashboard-card gradient-three gradient-four">
                        <p class="dashboard-card__title">
                            <span class="dashboard-card__title-icon">
                                <img src="{{ asset($activeTemplateTrue . 'images/icons/19.png') }}" alt="image">
                            </span>
                            <span class="dashboard-card__title-text">
                                @lang('Buy Gold')
                            </span>
                        </p>
                        <form action="{{ route('user.buy.store') }}" method="POST" class="dashboard-calculator-form">
                            @csrf
                            <div class="customNiceSelect">
                                <select name="category_id">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" data-price="{{ getAmount($category->price) }}">{{ __($category->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="calculator-switch">
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" name="goldCalculatorSwitch" id="goldCalculatorSwitch1" checked>
                                    <label class="text" for="goldCalculatorSwitch1">@lang('Purchase in '){{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="calculator-switch__icon"><i class="fa-solid fa-right-left"></i></span>
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" name="goldCalculatorSwitch" id="goldCalculatorSwitch2">
                                    <label class="text" for="goldCalculatorSwitch2">@lang('Purchase in Quantity')</label>
                                </div>
                            </div>
                            <div class="inputs">
                                <div class="form-group w-100">
                                    <label class="form--label">{{ __(gs('cur_text')) }}</label>
                                    <input type="text" class="form--control" placeholder="0.00" name="amount">
                                </div>
                                <span class="equal"><i class="fa-solid fa-equals"></i></span>
                                <div class="form-group w-100">
                                    <label class="form--label">@lang('Gram Gold')</label>
                                    <input type="text" class="form--control" placeholder="0.00" name="gram">
                                </div>
                            </div>
                            <div class="bottom">
                                <button type="submit" class="btn btn--base w-100">@lang('Buy Now')</button>
                                @if ($chargeLimit->fixed_charge || $chargeLimit->percent_charge || $chargeLimit->vat)
                                    <p class="bottom__info">
                                        <i class="fa-solid fa-circle-info me-1"></i>
                                        <span class="text">{{ getChargeText($chargeLimit) }}</span>
                                    </p>
                                @endif
                            </div>
                        </form>
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
                            @lang(gs('redeem_option') ? 'Recent Redeem & Gift History' : 'Recent Gift History')
                        </span>
                    </p>
                    <a href="{{ route('user.asset.logs') }}?data=redeem-gift" class="btn btn--base btn--sm">@lang('View All')</a>
                </div>

                <ul class="gift-withdraw-list">
                    @forelse ($giftRedeems as $giftRedeem)
                        <li class="gift-withdraw-list__item">
                            <div class="status">
                                <h6 class="status__title">{{ $giftRedeem->type == Status::GIFT_HISTORY ? 'Gift Gold' : 'Redeem Gold' }}</h6>
                                <span class="status__date d-block">{{ $giftRedeem->created_at->format('M d, Y') }}</span>
                                <span class="status__time d-block">{{ $giftRedeem->created_at->format('h:i A') }}</span>
                            </div>
                            <div class="content">
                                <span class="content__title">@lang('Amount')</span>
                                <p class="content__info">{{ showAmount($giftRedeem->quantity, currencyFormat: false) }} @lang('Gram')</p>
                            </div>
                            @if ($giftRedeem->type == Status::GIFT_HISTORY)
                                <div class="content">
                                    <span class="content__title">@lang('Recipient')</span>
                                    <p class="content__info">{{ @$giftRedeem->recipient->email }}</p>
                                </div>
                                <div class="gift-withdraw-list__item-badges">
                                    <span class="badge badge--success">@lang('Completed')</span>
                                </div>
                            @else
                                <div class="content">
                                    <span class="content__title">@lang('Delivery Address')</span>
                                    <p class="content__info">{{ __(strLimit(substr($giftRedeem->redeemData->delivery_address, 9), 15)) }}</p>
                                </div>
                                <div class="gift-withdraw-list__item-badges">
                                    @php
                                        echo $giftRedeem->redeemData->statusBadge;
                                    @endphp
                                </div>
                            @endif
                        </li>
                    @empty
                        @if (gs('redeem_option'))
                            <x-empty-card empty-message="No redeem & gift found" />
                        @else
                            <x-empty-card empty-message="No gift found" />
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
                                        <td>{{ showAmount($buySell->quantity, currencyFormat: false) }} @lang('Gram')</td>
                                        <td>{{ showAmount($buySell->quantity * $buySell->category->price) }}</td>
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

@include($activeTemplate . 'user.buy.amount_quantity_script')

@push('script')
    <script>
        (function($) {
            "use strict";

            $('[name=asset]').on('change', function() {
                let selectedOption = $(this).find('option:selected');
                let quantity = selectedOption.data('quantity');
                let totalAmount = selectedOption.data('total_amount');
                let priceChange = selectedOption.data('price_change');
                let percentChange = selectedOption.data('percent_change');


                $('.goldQuantity').text(quantity ?? '0.00');
                $('.goldTotalAmount').text(totalAmount ?? '0.00');
                $('.goldPriceChange').text(Math.abs(priceChange ?? 0));
                $('.goldPercentChange').text(`${Math.abs(percentChange ?? 0)}%`);

                if (percentChange >= 0) {
                    $('.percentChangeBadge i').removeClass('fa-caret-down').addClass('fa-caret-up');
                    $('.percentChangeBadge').removeClass('text--danger').addClass('text--success');
                } else {
                    $('.percentChangeBadge i').removeClass('fa-caret-up').addClass('fa-caret-down');
                    $('.percentChangeBadge').removeClass('text--success').addClass('text--danger');
                }
            }).change();

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


            $('[name=days]').on('change', function() {
                let days = $(this).find('option:selected').val();
                $.get("{{ route('user.price.history') }}", {
                    days
                }, function(response) {
                    chart.updateOptions({
                        series: [{
                            name: 'Gold Price',
                            data: response.prices
                        }],
                        xaxis: {
                            categories: response.dates
                        }
                    });
                });
            }).change();

            let baseColor = `#{{ gs('base_color') }}`;
            let secondaryColor = `#{{ gs('secondary_color') }}`;

            var options = {
                series: [{
                    name: 'Gold Price',
                    data: []
                }],
                chart: {
                    height: 256,
                    type: 'area',
                    toolbar: {
                        show: false
                    },
                },

                dataLabels: {
                    enabled: false
                },
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
                stroke: {
                    curve: 'smooth'
                },
                xaxis: {
                    type: 'datetime',
                    categories: [],
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
            var chart = new ApexCharts(document.querySelector("#apex_chart_three"), options);
            chart.render();


        })(jQuery);
    </script>
@endpush
