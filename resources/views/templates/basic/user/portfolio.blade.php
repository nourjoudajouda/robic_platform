@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="dashboard-card-wrapper">
        @foreach ($groupedAssets as $groupedAsset)
            <div class="dashboard-card gradient-one gradient-two">
                <div class="dashboard-card__top">
                    <div class="dashboard-card__tag">
                        <span class="dashboard-card__tag-icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/29.png') }}" alt="image"></span>
                        <span class="dashboard-card__tag-text">{{ __($groupedAsset->product->name ?? 'N/A') }}</span>
                        @if ($groupedAsset->batches_count > 1)
                            <span class="badge badge--success ms-2">{{ $groupedAsset->batches_count }} @lang('Batches')</span>
                        @endif
                    </div>
                    <button type="button" class="dashboard-card__link" data-bs-toggle="modal" data-bs-target="#batchDetailsModal{{ $groupedAsset->product_id }}">
                        <i class="las la-info-circle"></i>
                    </button>
                </div>
                <h2 class="dashboard-card__gold">{{ showAmount($groupedAsset->total_quantity, 4, currencyFormat: false) }} <sub>{{ $groupedAsset->product->unit->symbol ?? 'Unit' }}</sub></h2>
                <p class="dashboard-card__desc" style="margin-bottom: 5px;">
                    <small style="display: block; margin-bottom: 8px; opacity: 0.8; font-size: 0.85rem;">
                        <i class="las la-chart-line"></i> @lang('Market Price'): {{ showAmount($groupedAsset->current_market_price) }}/{{ $groupedAsset->product->unit->symbol ?? 'Unit' }}
                    </small>
                    <span style="font-size: 1.1rem; font-weight: 600;">{{ showAmount($groupedAsset->current_market_value) }}</span>
                    @php
                        $cardProfit = $groupedAsset->current_market_value - $groupedAsset->total_value;
                    @endphp
                    @if ($cardProfit != 0)
                        <br>
                        <small class="{{ $cardProfit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.9rem; font-weight: 500;">
                            <i class="las la-{{ $cardProfit >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ showAmount(abs($cardProfit)) }}
                        </small>
                    @endif
                </p>
            </div>

            {{-- Modal لعرض تفاصيل الـ batches --}}
            <div class="modal fade" id="batchDetailsModal{{ $groupedAsset->product_id }}" tabindex="-1" aria-labelledby="batchDetailsModalLabel{{ $groupedAsset->product_id }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content" style="background-color: #1a1a1a; color: #ffffff;">
                        <div class="modal-header" style="border-bottom: 1px solid #333;">
                            <h5 class="modal-title" id="batchDetailsModalLabel{{ $groupedAsset->product_id }}" style="color: #ffffff;">
                                @lang('Batch Details') - {{ $groupedAsset->product->name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            {{-- عرض معلومات إجمالية --}}
                            <div class="alert mb-3" style="background-color: #2a2a2a; border: 1px solid #444; color: #ffffff;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong style="color: #ffc107;">@lang('Total Quantity'):</strong><br>
                                        <span style="font-size: 1.1rem;">{{ showAmount($groupedAsset->total_quantity, 4, currencyFormat: false) }} {{ $groupedAsset->product->unit->symbol ?? 'Unit' }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong style="color: #17a2b8;">@lang('Purchase Value'):</strong><br>
                                        <span style="font-size: 1.1rem;">{{ showAmount($groupedAsset->total_value) }}</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong style="color: #28a745;">@lang('Current Market Value'):</strong><br>
                                        <span style="font-size: 1.1rem;">{{ showAmount($groupedAsset->current_market_value) }}</span>
                                        @php
                                            $profit = $groupedAsset->current_market_value - $groupedAsset->total_value;
                                            $profitPercent = $groupedAsset->total_value > 0 ? ($profit / $groupedAsset->total_value * 100) : 0;
                                        @endphp
                                        <br>
                                        <small class="{{ $profit >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.95rem;">
                                            <i class="las la-{{ $profit >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                            {{ showAmount(abs($profit)) }} ({{ number_format(abs($profitPercent), 2) }}%)
                                        </small>
                                        <br>
                                        <small style="color: #aaa; font-size: 0.85rem;">
                                            @lang('Market Price'): {{ showAmount($groupedAsset->current_market_price) }}/{{ $groupedAsset->product->unit->symbol ?? 'Unit' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table--responsive--sm" style="color: #ffffff;">
                                    <thead style="background-color: #2a2a2a;">
                                        <tr>
                                            <th style="color: #ffffff;">@lang('Batch Code')</th>
                                            <th style="color: #ffffff;">@lang('Quantity')</th>
                                            <th style="color: #ffffff;">@lang('Buy Price')</th>
                                            <th style="color: #ffffff;">@lang('Purchase Value')</th>
                                            <th style="color: #ffffff;">@lang('Actions')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($groupedAsset->batches as $asset)
                                            @php
                                                $purchaseValue = $asset->quantity * $asset->buy_price;
                                            @endphp
                                            <tr style="border-bottom: 1px solid #333;">
                                                <td>
                                                    <span class="badge badge--primary">
                                                        {{ $asset->batch ? $asset->batch->batch_code : 'N/A' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ showAmount($asset->quantity, 4, currencyFormat: false) }} 
                                                    {{ $asset->batch && $asset->batch->product && $asset->batch->product->unit ? $asset->batch->product->unit->symbol : 'Unit' }}
                                                </td>
                                                <td>{{ showAmount($asset->buy_price) }}</td>
                                                <td>{{ showAmount($purchaseValue) }}</td>
                                                <td>
                                                    <a href="{{ route('user.asset.logs', ['asset_id' => $asset->id]) }}" class="btn btn--sm btn--base">
                                                        <i class="las la-history"></i> @lang('History')
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- عرض العمليات الشرائية المجمعة حسب trx --}}
                            @php
                                // جلب كل عمليات الشراء للمنتج مع تجميع حسب trx
                                $buyHistories = \App\Models\BeanHistory::buy()
                                    ->where('user_id', auth()->id())
                                    ->where('product_id', $groupedAsset->product_id)
                                    ->with('batch')
                                    ->orderBy('created_at', 'desc')
                                    ->get()
                                    ->groupBy('trx');
                            @endphp

                            @if ($buyHistories->count() > 0)
                                <hr style="border-color: #333;">
                                <h6 class="mb-3" style="color: #ffffff;">@lang('Purchase Transactions')</h6>
                                <div class="accordion" id="purchaseAccordion{{ $groupedAsset->product_id }}">
                                    @foreach ($buyHistories as $trx => $histories)
                                        @php
                                            $totalQty = $histories->sum('quantity');
                                            $totalAmount = $histories->sum('amount');
                                            $totalCharge = $histories->sum('charge');
                                            $totalVat = $histories->sum('vat');
                                            $firstHistory = $histories->first();
                                        @endphp
                                        <div class="accordion-item" style="background-color: #2a2a2a; border: 1px solid #333; margin-bottom: 10px;">
                                            <h2 class="accordion-header" id="heading{{ $trx }}">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $trx }}" aria-expanded="false" aria-controls="collapse{{ $trx }}" style="background-color: #2a2a2a; color: #ffffff; border: none;">
                                                    <div class="d-flex justify-content-between w-100 me-3">
                                                        <span>
                                                            <strong>TRX:</strong> {{ $trx }}
                                                            <span class="badge badge--info ms-2">{{ $histories->count() }} @lang('Batch')</span>
                                                        </span>
                                                        <span>
                                                            <strong>{{ showAmount($totalQty, 4, currencyFormat: false) }}</strong> {{ $groupedAsset->product->unit->symbol ?? 'Unit' }}
                                                            | {{ showAmount($totalAmount + $totalCharge + $totalVat) }}
                                                        </span>
                                                    </div>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $trx }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $trx }}" data-bs-parent="#purchaseAccordion{{ $groupedAsset->product_id }}">
                                                <div class="accordion-body" style="background-color: #1a1a1a; color: #ffffff;">
                                                    <div class="row mb-2">
                                                        <div class="col-6"><strong style="color: #ffffff;">@lang('Date'):</strong></div>
                                                        <div class="col-6" style="color: #ffffff;">{{ showDateTime($firstHistory->created_at) }}</div>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm" style="color: #ffffff;">
                                                            <thead style="background-color: #2a2a2a;">
                                                                <tr>
                                                                    <th style="color: #ffffff;">@lang('Batch')</th>
                                                                    <th style="color: #ffffff;">@lang('Qty')</th>
                                                                    <th style="color: #ffffff;">@lang('Amount')</th>
                                                                    <th style="color: #ffffff;">@lang('Charge')</th>
                                                                    <th style="color: #ffffff;">@lang('VAT')</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($histories as $history)
                                                                    <tr style="border-bottom: 1px solid #333;">
                                                                        <td>{{ $history->batch ? $history->batch->batch_code : 'N/A' }}</td>
                                                                        <td>{{ showAmount($history->quantity, 4, currencyFormat: false) }}</td>
                                                                        <td>{{ showAmount($history->amount) }}</td>
                                                                        <td>{{ showAmount($history->charge) }}</td>
                                                                        <td>{{ showAmount($history->vat) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                                <tr style="background-color: #2a2a2a; border-top: 2px solid #555;">
                                                                    <td><strong style="color: #ffffff;">@lang('Total')</strong></td>
                                                                    <td><strong>{{ showAmount($totalQty, 4, currencyFormat: false) }}</strong></td>
                                                                    <td><strong>{{ showAmount($totalAmount) }}</strong></td>
                                                                    <td><strong>{{ showAmount($totalCharge) }}</strong></td>
                                                                    <td><strong>{{ showAmount($totalVat) }}</strong></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #333;">
                            <button type="button" class="btn btn--secondary btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row gy-4 mt-3">
        <div class="col-lg-5">
            <div class="dashboard-card is-top-border h-100">
                <div class="dashboard-card__top">
                    <h4 class="dashboard-card__title lg mb-0">@lang('Portfolio Overview')</h4>
                </div>
                @if ($groupedAssets->count())
                    <div id="apex_chart_five"></div>
                @else
                    <x-empty-card />
                @endif
            </div>
        </div>
        <div class="col-lg-7">
            <div class="dashboard-card  is-top-border h-100">
                @if ($groupedAssets->count())
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
                                        <td>{{ showAmount($assetLog->quantity, 4, currencyFormat: false) }} {{ $assetLog->batch && $assetLog->batch->product && $assetLog->batch->product->unit ? $assetLog->batch->product->unit->symbol : 'Unit' }}</td>
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
    <a href="{{ route('user.buy.form') }}" class="btn btn--success btn--lg"><i class="fas fa-circle-dollar-to-slot"></i> @lang('Buy Green Coffee')</a>
    <a href="{{ route('user.sell.form') }}" class="btn btn--danger btn--lg"><i class="fas fa-money-bill-trend-up"></i> @lang('Sell Green Coffee')</a>
    @if (gs('redeem_option'))
        <a href="{{ route('user.redeem.form') }}" class="btn btn--orange btn--lg"><i class="fas fa-truck"></i> @lang('Redeem Green Coffee')</a>
    @endif
    <a href="{{ route('user.gift.form') }}" class="btn btn--warning btn--lg"><i class="fas fa-gift"></i> @lang('Gift Green Coffee')</a>
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
