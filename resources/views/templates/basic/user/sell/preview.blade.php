@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-8 col-lg-10">
            <div class="buy-sell-card">
                <ul class="buy-sell-list">
                    <li class="buy-sell-list__item active">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">1</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Amount to sell')</span>
                        </span>
                    </li>
                    <li class="buy-sell-list__item active">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">2</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Preview')</span>
                        </span>
                    </li>
                    <li class="buy-sell-list__item">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">3</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Confirmation')</span>
                        </span>
                    </li>
                </ul>
                <div class="buy-sell-payment">
                    <div class="buy-sell-payment__left">
                        <h5 class="buy-sell-payment__title">@lang('Overview')</h5>
                        <ul class="text-list">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Product')</span>
                                <span class="text-list__item-value">{{ $product->name ?? 'N/A' }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Sell Price')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->sell_price, 2, currencyFormat: false) }} {{ $product && $product->currency ? $product->currency->symbol : '' }} / {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Quantity to Sell')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->quantity, 4, currencyFormat: false) }} {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Total Value')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->amount) }}</span>
                            </li>
                            @if ($sellData->charge > 0)
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('Charge')</span>
                                    <span class="text-list__item-value">{{ showAmount($sellData->charge) }}</span>
                                </li>
                            @endif

                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Final Amount')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->final_amount) }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="buy-sell-payment__right">
                        <h5 class="buy-sell-payment__title">@lang('Batch Details')</h5>
                        
                        @if (count($sellData->assets_to_sell) > 1)
                            <div class="alert alert-info mb-3" style="background-color: rgba(23, 162, 184, 0.1); border: 1px solid rgba(23, 162, 184, 0.3);">
                                <small>
                                    <i class="las la-info-circle"></i>
                                    @lang('This order will be distributed across') {{ count($sellData->assets_to_sell) }} @lang('batches')
                                </small>
                            </div>
                        @endif
                        
                        <div class="table-responsive mb-3">
                            <table class="table table--sm">
                                <thead>
                                    <tr>
                                        <th>@lang('Batch')</th>
                                        <th class="text-end">@lang('Quantity')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sellData->assets_to_sell as $assetToSell)
                                        <tr>
                                            <td><span class="badge badge--primary">{{ $assetToSell['batch_code'] }}</span></td>
                                            <td class="text-end">{{ showAmount($assetToSell['quantity'], 4, currencyFormat: false) }} {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-active">
                                        <td><strong>@lang('Total')</strong></td>
                                        <td class="text-end"><strong>{{ showAmount($sellData->quantity, 4, currencyFormat: false) }} {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <ul class="text-list mb-3">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Available Quantity')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->available_quantity, 4, currencyFormat: false) }} {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('After Sell Order')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->available_quantity - $sellData->quantity, 4, currencyFormat: false) }} {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}</span>
                            </li>
                        </ul>
                        
                        <form action="{{ route('user.sell.store') }}" method="POST">
                            @csrf
                            <button class="btn btn--base w-100">@lang('Confirm Sell')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/41.png') }}" alt="image">
@endsection

@endpush
