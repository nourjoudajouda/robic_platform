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
                                <span class="text-list__item-title">@lang('Gold Quantity')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->quantity, 4, currencyFormat: false) }} @lang('Gram')</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Gold Value')</span>
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
                        <h5 class="buy-sell-payment__title">@lang('Preview')</h5>
                        <ul class="text-list">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Current Asset')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->current_asset, 4, currencyFormat: false) }} @lang('Gram')</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('After Sell Asset')</span>
                                <span class="text-list__item-value">{{ showAmount($sellData->net_asset, 4, currencyFormat: false) }} @lang('Gram')</span>
                            </li>
                        </ul>
                        <form action="{{ route('user.sell.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="asset_id" value="{{ $sellData->asset_id }}">
                            <input type="hidden" name="amount" value="{{ $sellData->amount }}">
                            <button class="btn btn--base w-100 mt-3">@lang('Confirm Sell')</button>
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
