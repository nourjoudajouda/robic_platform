@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-8 col-lg-10">
            <div class="buy-sell-confirmation withdraw">
                <h4 class="buy-sell-confirmation__title">@lang('Congratulations! You placed your redeem order successfully.')</h4>
                <div class="buy-sell-confirmation__bottom">

                    <ul class="text-list mb-4">
                        <li class="text-list__item">
                            <span class="text-list__item-title">@lang('Category')</span>
                            <span class="text-list__item-value">{{ __($redeemHistory->category->name) }}</span>
                        </li>
                        <li class="text-list__item">
                            <span class="text-list__item-title">@lang('Gold Quantity')</span>
                            <span class="text-list__item-value">{{ showAmount($redeemHistory->quantity, currencyFormat: false) }} @lang('Gram')</span>
                        </li>
                        <li class="text-list__item">
                            <span class="text-list__item-title">@lang('Charge')</span>
                            <span class="text-list__item-value">{{ showAmount($redeemHistory->charge) }}</span>
                        </li>
                    </ul>

                    <div class="withdraw-option-item mb-4">
                        <h6 class="withdraw-option-item__title">@lang('Home Delivery')</h6>
                        <p class="withdraw-option-item__desc">{{ $redeemHistory->redeemData->delivery_address }}</p>
                        <hr>
                        <h5 class="withdraw-option-item__title mt-2">@lang('Order Details')</h5>
                        <p class="withdraw-option-item__desc">
                            @foreach ($redeemHistory->redeemData->order_details->items as $redeemUnit)
                                {{ $redeemUnit->text }} @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/40.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.redeem.history') }}" class="btn btn--base btn--lg"> <i class="fa fa-history"></i> @lang('Redeem History')</a>
@endpush
