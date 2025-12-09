@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-8 col-lg-10">
            <div class="buy-sell-card">
                <ul class="buy-sell-list">
                    <li class="buy-sell-list__item active">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">1</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Amount to buy')</span>
                        </span>
                    </li>
                    <li class="buy-sell-list__item">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">2</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Payment Method')</span>
                        </span>
                    </li>
                    <li class="buy-sell-list__item">
                        <span class="buy-sell-list__item-link">
                            <span class="buy-sell-list__item-number d-block">3</span>
                            <span class="buy-sell-list__item-text d-block">@lang('Confirmation')</span>
                        </span>
                    </li>
                </ul>
                <div class="gold-calculator">
                    <form action="{{ route('user.buy.store') }}" method="POST">
                        @csrf
                        <div class="gold-calculator__top">
                            <div class="gold-calculator__top-left">
                                <div class="customNiceSelect">
                                    <select name="category_id">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" data-price="{{ getAmount($category->price) }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <h4 class="gold-calculator__top-amount"> <span class="currentPrice"></span> {{ __(gs('cur_text')) }} /@lang('gram')</h4>
                            </div>
                            <div class="calculator-switch">
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="goldCalculatorSwitch1" name="purchase_type" checked>
                                    <label class="text" for="goldCalculatorSwitch1">@lang('Purchase in '){{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="calculator-switch__icon"><i class="fa-solid fa-right-left"></i></span>
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="goldCalculatorSwitch2" name="purchase_type">
                                    <label class="text" for="goldCalculatorSwitch2">@lang('Purchase in Quantity')</label>
                                </div>
                            </div>
                        </div>
                        <div class="gold-calculator__bottom">
                            <div class="gold-calculator__inputs">
                                <div class="form-group position-relative">
                                    <input type="number" step="any" class="form--control" placeholder="00.00" name="amount">
                                    <label class="form--label">{{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="equal"><i class="fa-solid fa-equals"></i></span>
                                <div class="form-group position-relative has-icon">
                                    <span class="icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/23.png') }}" alt="image"></span>
                                    <input type="number" step="any" class="form--control" placeholder="00.00" name="gram">
                                    <label class="form--label">@lang('Gram')</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn--base w-100" disabled>@lang('Submit')</button>

                            @if ($chargeLimit->fixed_charge || $chargeLimit->percent_charge || $chargeLimit->vat)
                                <span class="info mt-1">
                                    <span class="info__icon"><i class="fa-solid fa-circle-info me-1"></i></span>
                                    <span class="info__text">
                                        {{ getChargeText($chargeLimit) }}
                                    </span>
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/22.png') }}" alt="image">
@endsection


@push('pageHeaderButton')
    <a href="{{ route('user.buy.history') }}" class="btn btn--base btn--lg"> <i class="fas fa-history"></i> @lang('Buy History')</a>
@endpush

@include($activeTemplate . 'user.buy.amount_quantity_script')
