@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="dashboard-card-wrapper">
        @foreach ($assets as $asset)
            <div class="dashboard-card gradient-one gradient-two">
                <div class="dashboard-card__top">
                    <div class="dashboard-card__tag">
                        <span class="dashboard-card__tag-icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/29.png') }}" alt="image"></span>
                        <span class="dashboard-card__tag-text">{{ __($asset->category->name) }}</span>
                    </div>
                    <a href="{{ route('user.asset.logs', ['asset_id' => $asset->id]) }}" class="dashboard-card__link"><i class="las la-arrow-right"></i></a>
                </div>
                <h2 class="dashboard-card__gold">{{ showAmount($asset->quantity, currencyFormat: false) }} <sub>@lang('Gram Gold')</sub></h2>
                <p class="dashboard-card__desc">
                    <span>{{ showAmount($asset->quantity * $asset->category->price) }}</span>
                    @if ($asset->category->change_90d > 0)
                        <span class="text--success">
                            <i class="fa-solid fa-caret-up"></i>
                            {{ showAmount(($asset->quantity * $asset->category->price * $asset->category->change_90d) / 100) }}
                            {{ showAmount($asset->category->change_90d, currencyFormat: false) }}%
                            (@lang('90D'))
                        </span>
                    @else
                        <span class="text--danger">
                            <i class="fa-solid fa-caret-down"></i>
                            {{ showAmount(($asset->quantity * $asset->category->price * abs($asset->category->change_90d)) / 100) }}
                            {{ showAmount(abs($asset->category->change_90d), currencyFormat: false) }}%
                            (@lang('90D'))
                        </span>
                    @endif
                </p>
            </div>
        @endforeach
    </div>
    <div class="row gy-4 mt-3">
        <div class="col-lg-5">
            <div class="dashboard-card is-top-border h-100">
                <div class="dashboard-card__top">
                    <h4 class="dashboard-card__title lg mb-0">@lang('Portfolio Overview')</h4>
                </div>
                @if ($assets->count())
                    <div id="apex_chart_five"></div>
                @else
                    <x-empty-card />
                @endif
            </div>
        </div>
        <div class="col-lg-7">
            <div class="dashboard-card  is-top-border h-100">
                @if ($assets->count())
                    <div class="dashboard-card__top">
                        <h4 class="dashboard-card__title lg mb-0">@lang('Asset Logs')</h4>
                        <a href="{{ route('user.asset.logs') }}" class="btn btn--base btn--sm">@lang('View All')</a>
                    </div>
                    <div class="dashboard-table">
                        <table class="table table--responsive--sm">
                            <thead>
                                <tr>
                                    <th>@lang('Date & Time')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Charge')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assetLogs as $assetLog)
                                    <tr>
                                        <td>
                                            <div>
                                                <span class="d-block">{{ showDateTime($assetLog->created_at, 'd M, Y') }}</span>
                                                <span class="d-block">{{ showDateTime($assetLog->created_at, 'h:i A') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                echo $assetLog->statusBadge;
                                            @endphp
                                        </td>
                                        <td>{{ showAmount($assetLog->quantity, 4, currencyFormat: false) }} @lang('Gram')</td>
                                        <td>{{ showAmount($assetLog->amount) }}</td>
                                        <td>{{ showAmount($assetLog->charge) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">@lang('No data found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="dashboard-card__top">
                        <h4 class="dashboard-card__title lg mb-0">@lang('Asset Logs')</h4>
                    </div>
                    <x-empty-card />
                @endif

            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/31.png') }}" alt="image">
@endsection


@push('pageHeaderButton')
    <a href="{{ route('user.buy.form') }}" class="btn btn--success btn--lg"><i class="fas fa-circle-dollar-to-slot"></i> @lang('Buy Gold')</a>
    <a href="{{ route('user.sell.form') }}" class="btn btn--danger btn--lg"><i class="fas fa-money-bill-trend-up"></i> @lang('Sell Gold')</a>
    @if (gs('redeem_option'))
        <a href="{{ route('user.redeem.form') }}" class="btn btn--orange btn--lg"><i class="fas fa-truck"></i> @lang('Redeem Gold')</a>
    @endif
    <a href="{{ route('user.gift.form') }}" class="btn btn--warning btn--lg"><i class="fas fa-gift"></i> @lang('Gift Gold')</a>
@endpush

@if ($portfolioData['total_asset_quantity'] > 0)
    @push('script')
        <script>
            (function($) {
                "use strict";

                var options = {
                    series: @json($portfolioData['asset_category_quantity']),
                    chart: {
                        width: 380,
                        type: 'pie',
                    },
                    labels: @json($portfolioData['assets_category_name']),
                    legend: {
                        position: 'right',
                        labels: {
                            colors: ['#FFFFFF'],
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

                var chart = new ApexCharts(document.querySelector("#apex_chart_five"), options);
                chart.render();

            })(jQuery);
        </script>
    @endpush
@endif
