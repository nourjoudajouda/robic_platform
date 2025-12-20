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
                                    <select name="product_id" id="product_id" required>
                                        @foreach ($groupedAssets as $groupedAsset)
                                            <option value="{{ $groupedAsset->product_id }}" 
                                                data-market-price="{{ getAmount($groupedAsset->current_market_price) }}"
                                                data-buy-price="{{ getAmount($groupedAsset->average_buy_price) }}"
                                                data-total-quantity="{{ $groupedAsset->total_quantity }}"
                                                data-available-quantity="{{ $groupedAsset->available_quantity }}"
                                                data-unit="{{ $groupedAsset->product->unit->symbol ?? 'Unit' }}"
                                                data-currency="{{ $groupedAsset->product->currency->symbol ?? '' }}"
                                                data-batches-count="{{ $groupedAsset->batches_count }}">
                                                {{ $groupedAsset->product->name ?? 'N/A' }} ({{ showAmount($groupedAsset->available_quantity, 4, currencyFormat: false) }} {{ $groupedAsset->product->unit->symbol ?? 'Unit' }})
                                                @if ($groupedAsset->batches_count > 1)
                                                    - {{ $groupedAsset->batches_count }} Batches
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-3">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <small class="text-muted d-block">@lang('Market Price'):</small>
                                            <strong class="text-info" id="market_price_display">0</strong> <span class="currency-symbol">{{ $groupedAssets->first()->product->currency->symbol ?? '' }}</span> / <span class="unit-symbol">{{ $groupedAssets->first()->product->unit->symbol ?? 'Unit' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">@lang('Buy Price'):</small>
                                            <strong class="text-success" id="buy_price_display">0</strong> <span class="currency-symbol">{{ $groupedAssets->first()->product->currency->symbol ?? '' }}</span> / <span class="unit-symbol">{{ $groupedAssets->first()->product->unit->symbol ?? 'Unit' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="calculator-switch">
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="goldCalculatorSwitch1" name="purchase_type" value="amount" checked>
                                    <label class="text" for="goldCalculatorSwitch1">@lang('Sell in USD')</label>
                                </div>
                                <span class="calculator-switch__icon"><i class="fa-solid fa-right-left"></i></span>
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="goldCalculatorSwitch2" name="purchase_type" value="quantity">
                                    <label class="text" for="goldCalculatorSwitch2">@lang('Sell in Quantity')</label>
                                </div>
                            </div>
                        </div>
                        <div class="gold-calculator__bottom">
                            <div class="gold-calculator__inputs">
                                <div class="form-group position-relative">
                                    <input type="number" step="any" min="0" class="form--control" placeholder="00.00" name="amount" id="amount">
                                    <label class="form--label">{{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="equal"><i class="fa-solid fa-equals"></i></span>
                                <div class="form-group position-relative has-icon">
                                    <span class="icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/23.png') }}" alt="image"></span>
                                    <input type="number" step="any" min="0" class="form--control" placeholder="00.00" name="quantity" id="quantity">
                                    <label class="form--label unit-label">{{ $groupedAssets->first()->product->unit->name ?? 'Quantity' }}</label>
                                </div>
                            </div>
                            
                            <div class="form-group mt-3">
                                <label class="form--label">@lang('Sell Price') <span class="text--danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0" class="form--control" placeholder="00.00" name="sell_price" id="sell_price" required>
                                    <span class="input-group-text currency-symbol">{{ $groupedAssets->first()->product->currency->symbol ?? '' }}</span>
                                    <span class="input-group-text">/</span>
                                    <span class="input-group-text unit-symbol">{{ $groupedAssets->first()->product->unit->symbol ?? 'Unit' }}</span>
                                </div>
                                <small class="form-text text-muted">@lang('Enter the price you want to sell at')</small>
                            </div>
                            
                            <div class="form-group mt-2">
                                <small class="form-text text-muted">
                                    <span class="text-info">@lang('Total Quantity'): <strong id="total_quantity_display">0</strong> <span class="unit-symbol">{{ $groupedAssets->first()->product->unit->symbol ?? 'Unit' }}</span></span>
                                    <br>
                                    <span class="text-success">@lang('Available Quantity'): <strong id="available_quantity_display">0</strong> <span class="unit-symbol">{{ $groupedAssets->first()->product->unit->symbol ?? 'Unit' }}</span></span>
                                </small>
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
                            <button type="submit" class="btn btn--base w-100" disabled>@lang('Create Sell Order')</button>
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
            let marketPrice = 0;
            let buyPrice = 0;
            let sellPrice = 0;
            let quantity = 0;
            let amount = 0;
            let totalQuantity = 0;
            let availableQuantity = 0;
            let unitSymbol = '';
            let currencySymbol = '';
            let purchaseType = 'amount'; // 'amount' or 'quantity'

            function updateAssetInfo() {
                let selectedOption = $('#product_id option:selected');
                marketPrice = parseFloat(selectedOption.data('market-price')) || 0;
                buyPrice = parseFloat(selectedOption.data('buy-price')) || 0;
                totalQuantity = parseFloat(selectedOption.data('total-quantity')) || 0;
                availableQuantity = parseFloat(selectedOption.data('available-quantity')) || 0;
                unitSymbol = selectedOption.data('unit') || 'Unit';
                currencySymbol = selectedOption.data('currency') || '';

                // تحديث العرض
                $('#market_price_display').text(marketPrice);
                $('#buy_price_display').text(buyPrice);
                $('#total_quantity_display').text(totalQuantity);
                $('#available_quantity_display').text(availableQuantity);
                $('.unit-symbol').text(unitSymbol);
                $('.currency-symbol').text(currencySymbol);
                
                // تحديث max للكمية
                $('#quantity').attr('max', availableQuantity);
                
                // تحديث السعر الافتراضي إلى سعر السوق
                if (marketPrice > 0) {
                    $('#sell_price').val(marketPrice);
                    sellPrice = marketPrice;
                }
                
                calculateValues();
            }

            function calculateValues() {
                sellPrice = parseFloat($('#sell_price').val()) || 0;
                
                if (purchaseType === 'amount') {
                    amount = parseFloat($('#amount').val()) || 0;
                    if (amount > 0 && sellPrice > 0) {
                        quantity = amount / sellPrice;
                        $('#quantity').val(quantity.toFixed(4));
                    } else {
                        $('#quantity').val('');
                    }
                } else {
                    quantity = parseFloat($('#quantity').val()) || 0;
                    if (quantity > 0 && sellPrice > 0) {
                        amount = quantity * sellPrice;
                        $('#amount').val(amount.toFixed(2));
                    } else {
                        $('#amount').val('');
                    }
                }
                
                calculateTotalCharge();
                validateForm();
            }

            function calculateTotalCharge() {
                let fixedCharge = {{ $chargeLimit->fixed_charge }};
                let percentCharge = {{ $chargeLimit->percent_charge }};
                let totalCharge = fixedCharge + amount * percentCharge / 100;
                $('.totalCharge').text(totalCharge.toFixed(2));
                let userGetAmount = amount - totalCharge;
                $('.userGetAmount').text(userGetAmount.toFixed(2));
            }

            function validateForm() {
                let isValid = true;
                
                // التحقق من السعر
                if (sellPrice <= 0) {
                    isValid = false;
                }
                
                // التحقق من الكمية أو المبلغ
                if (purchaseType === 'amount') {
                    if (amount <= 0) {
                        isValid = false;
                    }
                } else {
                    if (quantity <= 0) {
                        isValid = false;
                    } else if (quantity > availableQuantity) {
                        isValid = false;
                    }
                }
                
                $('button[type="submit"]').attr('disabled', !isValid);
            }

            // تحديث المعلومات عند تغيير المنتج
            $('#product_id').on('change', function() {
                updateAssetInfo();
            });

            // تغيير نوع الشراء (amount أو quantity)
            $('input[name="purchase_type"]').on('change', function() {
                purchaseType = $(this).val();
                if (purchaseType === 'amount') {
                    $('#amount').prop('required', true);
                    $('#quantity').prop('required', false);
                } else {
                    $('#amount').prop('required', false);
                    $('#quantity').prop('required', true);
                }
                calculateValues();
            });

            // تحديث القيم عند تغيير المبلغ
            $('#amount').on('input', function() {
                if (purchaseType === 'amount') {
                    calculateValues();
                }
            });

            // تحديث القيم عند تغيير الكمية
            $('#quantity').on('input', function() {
                if (purchaseType === 'quantity') {
                    calculateValues();
                }
            });

            // تحديث القيم عند تغيير السعر
            $('#sell_price').on('input', function() {
                calculateValues();
            });

            // التحقق من الكمية عند focusout
            $('#quantity').on('focusout', function() {
                quantity = parseFloat($(this).val()) || 0;
                if (quantity > availableQuantity) {
                    notify('error', `Available quantity is ${availableQuantity} ${unitSymbol}`);
                    $(this).val(availableQuantity);
                    calculateValues();
                }
            });

            // تهيئة عند تحميل الصفحة
            updateAssetInfo();
            
        })(jQuery);
    </script>
@endpush
