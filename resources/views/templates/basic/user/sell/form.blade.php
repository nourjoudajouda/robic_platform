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
                    <li class="buy-sell-list__item">
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
                <div class="gold-calculator">
                    <form action="{{ route('user.sell.submit') }}" method="POST">
                        @csrf
                        <div class="gold-calculator__top">
                            <div class="gold-calculator__top-left">
                                <div class="customNiceSelect">
                                    <select name="asset_id" required>
                                        @foreach ($assets as $asset)
                                            <option value="{{ $asset->id }}" data-price="{{ getAmount($asset->category->price) }}">{{ $asset->category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <h4 class="gold-calculator__top-amount"> <span class="currentPrice"></span> {{ __(gs('cur_text')) }} /@lang('gram')</h4>
                            </div>
                            <div class="calculator-switch">
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="goldCalculatorSwitch1" name="purchase_type" checked>
                                    <label class="text" for="goldCalculatorSwitch1">@lang('Sell in USD')</label>
                                </div>
                                <span class="calculator-switch__icon"><i class="fa-solid fa-right-left"></i></span>
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="goldCalculatorSwitch2" name="purchase_type">
                                    <label class="text" for="goldCalculatorSwitch2">@lang('Sell in Quantity')</label>
                                </div>
                            </div>
                        </div>
                        <div class="gold-calculator__bottom">
                            <div class="gold-calculator__inputs">
                                <div class="form-group position-relative">
                                    <input type="number" step="any" min="0" class="form--control" placeholder="00.00" name="amount">
                                    <label class="form--label">{{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="equal"><i class="fa-solid fa-equals"></i></span>
                                <div class="form-group position-relative has-icon">
                                    <span class="icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/23.png') }}" alt="image"></span>
                                    <input type="number" step="any" min="0" class="form--control" placeholder="00.00" name="quantity">
                                    <label class="form--label">@lang('Gram')</label>
                                </div>
                            </div>
                            @if ($chargeLimit->fixed_charge || $chargeLimit->percent_charge)
                                <span class="info">
                                    <span class="info__icon"><i class="fa-solid fa-circle-info me-1"></i></span>
                                    <span class="info__text">
                                        @lang('You will get') <span class="userGetAmount">0.00</span> {{ __(gs('cur_text')) }}
                                        @lang('Charge'): <span class="totalCharge">0.00</span> {{ __(gs('cur_text')) }}
                                        (
                                        @if ($chargeLimit->fixed_charge)
                                            {{ showAmount($chargeLimit->fixed_charge) }}
                                            @if ($chargeLimit->percent_charge)
                                                +
                                            @endif
                                        @endif
                                        @if ($chargeLimit->percent_charge)
                                            {{ showAmount($chargeLimit->percent_charge, currencyFormat: false) }}%
                                        @endif
                                        @lang('charge applicable')
                                        )
                                    </span>
                                </span>
                            @endif
                            <button type="submit" class="btn btn--base w-100" disabled>@lang('Sell Now')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/41.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.sell.history') }}" class="btn btn--base btn--lg"> <i class="fas fa-history"></i> @lang('Sell History')</a>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            let price = 0;
            let gram = 0;
            let amount = 0;

            $('select[name="asset_id"]').on('change', function() {
                price = $(this).find('option:selected').data('price');
                $('.currentPrice').text(price);
                if (amount > 0) {
                    gram = amount / price;
                    $('[name="quantity"]').val(gram.toFixed(4));
                }
            }).trigger('change');

            $('[name="amount"]').on('keyup', function() {
                amount = $(this).val() * 1;
                gram = amount / price;
                $('[name="quantity"]').val(gram.toFixed(4));
                calculateTotalCharge();
                handleSubmitButton();
            });

            $('[name="quantity"]').on('keyup', function() {
                gram = $(this).val();
                amount = gram * price;
                $('[name="amount"]').val(amount.toFixed(2));
                calculateTotalCharge();
                handleSubmitButton();
            });

            let minAmount = {{ $chargeLimit->min_amount }};
            let maxAmount = {{ $chargeLimit->max_amount }};
        
            $('[name="amount"], [name="quantity"]').on('focusout', function() {
                let amount = $('[name="amount"]').val() * 1;
                if (amount <= 0) {
                    $('[name="amount"]').val('');
                    $('[name="quantity"]').val('');
                    return false;
                }

                if (amount < minAmount) {
                    notify('error', `Minimum amount is ${minAmount}`);
                    return false;
                }
                if (amount > maxAmount) {
                    notify('error', `Maximum amount is ${maxAmount}`);
                    return false;
                }
                
                amount = amount.toFixed(2);
                gram = amount / price;
                $('[name="amount"]').val(amount);
                $('[name="quantity"]').val(gram.toFixed(4));
            });


            function calculateTotalCharge() {
                let fixedCharge = {{ $chargeLimit->fixed_charge }};
                let percentCharge = {{ $chargeLimit->percent_charge }};
                let totalCharge = fixedCharge + amount * percentCharge / 100;
                $('.totalCharge').text(totalCharge.toFixed(2));
                let userGetAmount = amount - totalCharge;
                $('.userGetAmount').text(userGetAmount.toFixed(2));
            }

            function handleSubmitButton(){
                if(minAmount <= amount && maxAmount >= amount){
                    $('button[type="submit"]').attr('disabled', false);
                }else{
                    $('button[type="submit"]').attr('disabled', true);
                }
            }
            
        })(jQuery);
    </script>
@endpush
