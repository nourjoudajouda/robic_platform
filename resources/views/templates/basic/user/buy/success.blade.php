@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-8 col-lg-10">
            <div class="buy-sell-card position-relative">
                <span class="buy-sell-card-shape"><img src="{{ asset($activeTemplateTrue.'images/buy-sell-card-shape.png') }}" alt="image"></span>
                <ul class="buy-sell-list">
                    <li class="buy-sell-list__item active">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">1</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Amount to buy')</span>
                        </span>
                    </li>
                    <li class="buy-sell-list__item active">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">2</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Payment Method')</span>
                        </span>
                    </li>
                    <li class="buy-sell-list__item active">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">3</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Confirmation')</span>
                        </span>
                    </li>
                </ul>
                <div class="buy-sell-confirmation">
                    <h4 class="buy-sell-confirmation__title">
                        @lang('Congratulations! You’ve successfully purchased gold.')
                    </h4>
                    <div class="buy-sell-confirmation__bottom">
                        <ul class="text-list">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Gold Quantity')</span>
                                <span class="text-list__item-value">{{ showAmount($buyHistory->quantity, 4, currencyFormat:false) }} @lang('Gram')</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Amount')</span>
                                <span class="text-list__item-value">{{ showAmount($buyHistory->amount) }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Charge')</span>
                                <span class="text-list__item-value">{{ showAmount($buyHistory->charge) }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('VAT')</span>
                                <span class="text-list__item-value">{{ showAmount($buyHistory->vat) }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Total Amount')</span>
                                <span class="text-list__item-value">{{ showAmount($buyHistory->amount + $buyHistory->charge + $buyHistory->vat) }}</span>
                            </li>
                        </ul>
                        <a href="{{ route('user.buy.form') }}" class="btn btn-outline--base w-100">@lang('Buy Again')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/22.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.buy.history') }}" class="btn btn-outline--base btn--lg"> <i class="las la-history"></i> @lang('Buy History')</a>
@endpush

