@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card">
                <h4>@lang('Available for Gift')</h4>
                <div class="dashboard-card-wrapper two">
                    @foreach ($assets as $asset)
                        <div class="dashboard-card two">
                            <div class="dashboard-card__tag">
                                <span class="dashboard-card__tag-icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/29.png') }}" alt="image"></span>
                                <span class="dashboard-card__tag-text">{{ __($asset->category->name) }}</span>
                            </div>
                            <h4 class="dashboard-card__gold">{{ showAmount($asset->quantity, 4, currencyFormat: false) }} <sub>{{ $asset->batch && $asset->batch->product && $asset->batch->product->unit ? $asset->batch->product->unit->symbol : 'Unit' }}</sub></h4>
                            <p class="dashboard-card__desc">{{ showAmount($asset->quantity * $asset->category->price) }}</p>
                        </div>
                    @endforeach
                </div>
                <form action="{{ route('user.gift.store') }}" method="POST" class="gift-form mt-4">
                    @csrf
                    <div class="row gy-3">
                        <div class="col-12">
                            <div class="form-group lg">
                                <div class="customNiceSelect">
                                    <select name="asset_id">
                                        <option value="" disabled selected>@lang('Select asset')</option>
                                        @forelse ($assets as $asset)
                                            <option value="{{ $asset->id }}" data-price="{{ $asset->category->price }}">{{ __($asset->category->name) }}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                                <input type="number" step="any" name="amount" value="{{ old('amount') }}" class="form--control" placeholder="0.00">
                                <span class="label">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5 class="buy-sell-payment__title">@lang('Overview')</h5>
                            <ul class="text-list">
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('Green Coffee Quantity')</span>
                                    <span class="text-list__item-value"><span class="quantity">0.00</span> @lang('Gram')</span>
                                </li>
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('Charge') ({{ showAmount($chargeLimit->fixed_charge) }} + {{ showAmount($chargeLimit->percent_charge, currencyFormat: false) }}%)</span>
                                    <span class="text-list__item-value"><span class="charge">0.00</span> {{ __(gs('cur_text')) }}</span>
                                </li>
                            </ul>

                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-3">
                                <span class="icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/36.png') }}" alt="image"></span>
                                <input type="text" name="user" class="form--control" placeholder="@lang('Recipient\'s Email / Username')" required>
                            </div>
                            <div class="form-group mb-3">
                                <span class="icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/37.png') }}" alt="image"></span>
                                <input type="text" class="form--control userFullname" placeholder="@lang('Recipient\'s Name')" readonly>
                            </div>
                            <div class="form-group mb-3">
                                <p class="gift-form__desc"><i class="fa-solid fa-circle-info"></i>
                                    @lang('Please make sure the recipient has a account with this above email.')
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn--base w-100" disabled>@lang('Send')</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/35.png') }}" alt="image">
@endsection


@push('pageHeaderButton')
    <a href="{{ route('user.gift.history') }}" class="btn btn--base btn--lg">
        <i class="fa-solid fa-clock-rotate-left"></i>
        @lang('Gift History')
    </a>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            let chargeLimit = @json($chargeLimit);
            let price = 0,
                amount = 0,
                quantity = 0,
                charge = 0;

            $('[name=asset_id]').on('change', function() {
                price = $(this).find('option:selected').data('price');
                calculate();
            }).change();

            $('[name=amount]').on('keyup', function() {
                amount = $('[name=amount]').val() * 1;
                calculate();
                handleSubmitButton();
            });

            function calculate() {
                if (price == 0 || amount == 0) {
                    $('.quantity').text(0);
                    $('.charge').text(0);
                    return false;
                }
                quantity = amount / price;
                charge = parseFloat(chargeLimit.fixed_charge) + amount * parseFloat(chargeLimit.percent_charge) / 100;
                $('.quantity').text(quantity.toFixed(4));
                $('.charge').text(charge);
            }
            let minAmount = parseFloat(chargeLimit.min_amount);
            let maxAmount = parseFloat(chargeLimit.max_amount);

            $('[name=amount]').on('focusout', function() {
                let amount = parseFloat($(this).val());
                if (amount < minAmount) {
                    notify('error', 'Amount must be greater than ' + minAmount);
                    return false;
                }
                if (amount > maxAmount) {
                    notify('error', 'Amount must be less than ' + maxAmount);
                    return false;
                }
            });

            let validUser = false;

            $('[name=user]').on('focusout', function() {
                let user = $(this).val();
                let $this = $(this);
                $this.removeClass('border--danger');
                $('.userFullname').val('');

                if (user == '') {
                    return false;
                }


                $.get("{{ route('user.gift.checkUser') }}", {
                    user: user
                }, function(response) {

                    if (response.status == 'error') {
                        $this.addClass('border--danger');
                        $('.userFullname').val('');
                        validUser = false;
                        handleSubmitButton();
                        return false;
                    }

                    if (response.status == 'success') {
                        $this.removeClass('border--danger');
                        $('.userFullname').val(response.data.user.firstname + ' ' + response.data.user.lastname);
                        validUser = true;
                        handleSubmitButton();
                    }
                });
            });

            function handleSubmitButton() {
                if (minAmount <= amount && maxAmount >= amount && validUser) {
                    $('button[type="submit"]').attr('disabled', false);
                } else {
                    $('button[type="submit"]').attr('disabled', true);
                }
            }

        })(jQuery);
    </script>
@endpush
