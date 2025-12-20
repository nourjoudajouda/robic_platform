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
                        <input type="hidden" name="batch_id" value="{{ $selectedBatch->id }}" id="selected_batch_id">
                        <div class="gold-calculator__top">
                            <div class="gold-calculator__top-left">
                                <div class="customNiceSelect">
                                    <select name="batch_id" id="batch_select">
                                        @foreach ($batches as $batch)
                                            @php
                                                $cheapestOrder = $batch->sellOrders->first(); // أرخص sell order
                                                $displayPrice = $cheapestOrder ? $cheapestOrder->sell_price : $batch->sell_price;
                                            @endphp
                                            <option value="{{ $batch->id }}" 
                                                data-price="{{ getAmount($displayPrice) }}"
                                                data-unit="{{ $batch->product->unit->symbol ?? '' }}"
                                                data-quantity="{{ showAmount($batch->units_count, 2, true, false, false) }}"
                                                {{ $batch->id == $selectedBatch->id ? 'selected' : '' }}>
                                                {{ $batch->product->name ?? 'N/A' }} - {{ showAmount($displayPrice, 2, true, false, false) }} {{ $batch->product->currency->code ?? gs('cur_sym') }} / {{ $batch->product->unit->symbol ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <h4 class="gold-calculator__top-amount"> 
                                    <span class="currentPrice">{{ showAmount($cheapestSellOrder->sell_price ?? $selectedBatch->sell_price, 2, true, false, false) }}</span> 
                                    {{ $selectedBatch->product->currency->code ?? gs('cur_sym') }} / {{ $selectedBatch->product->unit->symbol ?? 'Unit' }}
                                </h4>
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
                                    <input type="number" step="any" class="form--control" placeholder="00.00" name="quantity" id="quantity_input">
                                    <label class="form--label">{{ $selectedBatch->product->unit->name ?? 'Quantity' }}</label>
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
