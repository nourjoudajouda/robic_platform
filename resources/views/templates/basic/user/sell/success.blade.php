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
                            <span class="buy-sell-list__item-text d-block">@lang('Amount to Sell')</span>
                        </span>
                    </li>
                    <li class="buy-sell-list__item active">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">2</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Preview')</span>
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
                        @lang('Congratulations! You’ve successfully sold your gold.')
                    </h4>
                    <div class="buy-sell-confirmation__bottom">
                        <ul class="text-list">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Gold Quantity')</span>
                                <span class="text-list__item-value">{{ showAmount($sellHistory->quantity, 4, currencyFormat:false) }} @lang('Gram')</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Amount')</span>
                                <span class="text-list__item-value">{{ showAmount($sellHistory->amount) }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Charge')</span>
                                <span class="text-list__item-value">{{ showAmount($sellHistory->charge) }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Final Amount')</span>
                                <span class="text-list__item-value">{{ showAmount($sellHistory->amount - $sellHistory->charge) }}</span>
                            </li>
                        </ul>
                        <a href="{{ route('user.sell.form') }}" class="btn btn-outline--base w-100">@lang('Sell Again')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/41.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.sell.history') }}" class="btn btn-outline--base btn--lg"> <i class="fa fa-history"></i> @lang('Sell History')</a>
@endpush

