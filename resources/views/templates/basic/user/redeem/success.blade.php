@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-8 col-lg-10">
            <div class="buy-sell-confirmation withdraw">
                <h4 class="buy-sell-confirmation__title">@lang('Congratulations! You placed your shipping order successfully.')</h4>
                <div class="buy-sell-confirmation__bottom">

                    <ul class="text-list mb-4">
                        <li class="text-list__item">
                            <span class="text-list__item-title">@lang('Product')</span>
                            <span class="text-list__item-value">{{ __($redeemHistory->product ? $redeemHistory->product->name : 'N/A') }}</span>
                        </li>
                        <li class="text-list__item">
                            <span class="text-list__item-title">@lang('Green Coffee Quantity')</span>
                            <span class="text-list__item-value">{{ showAmount($redeemHistory->quantity, currencyFormat: false) }} {{ $redeemHistory->product && $redeemHistory->product->unit ? $redeemHistory->product->unit->symbol : 'Unit' }}</span>
                        </li>
                        <li class="text-list__item">
                            <span class="text-list__item-title">@lang('Shipping Cost')</span>
                            <span class="text-list__item-value">{{ showAmount($redeemHistory->charge) }}</span>
                        </li>
                    </ul>

                    @if($redeemHistory->redeemData)
                    <div class="withdraw-option-item mb-4">
                        <h6 class="withdraw-option-item__title">
                            @if($redeemHistory->redeemData->delivery_type === 'shipping')
                                @lang('Home Delivery')
                            @else
                                @lang('Pickup from Warehouse')
                            @endif
                        </h6>
                        <p class="withdraw-option-item__desc">{{ $redeemHistory->redeemData->delivery_address }}</p>
                        
                        @if($redeemHistory->redeemData->delivery_type === 'shipping' && $redeemHistory->redeemData->shippingMethod)
                        <hr>
                        <h5 class="withdraw-option-item__title mt-2">@lang('Shipping Details')</h5>
                        <ul class="text-list">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Shipping Method')</span>
                                <span class="text-list__item-value">{{ $redeemHistory->redeemData->shippingMethod->name }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Distance')</span>
                                <span class="text-list__item-value">{{ showAmount($redeemHistory->redeemData->distance, 2, currencyFormat: false) }} @lang('km')</span>
                            </li>
                        </ul>
                                @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/40.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.redeem.history') }}" class="btn btn--base btn--lg"> <i class="fa fa-history"></i> @lang('Shipping History')</a>
@endpush
