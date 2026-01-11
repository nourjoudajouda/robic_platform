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
                    <li class="buy-sell-list__item active">
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
                <div class="buy-sell-payment">
                    <div class="buy-sell-payment__left">
                        <h5 class="buy-sell-payment__title">@lang('Overview')</h5>
                        <ul class="text-list">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Green Coffee Quantity')</span>
                                <span class="text-list__item-value">{{ showAmount($buyData->quantity, 4, true, false, false) }} {{ $product && $product->unit ? $product->unit->symbol : ($batch && $batch->product && $batch->product->unit ? $batch->product->unit->symbol : 'Unit') }}</span>
                            </li>
                            @if(isset($buyData->multiple_orders) && is_array($buyData->multiple_orders) && count($buyData->multiple_orders) > 1)
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('Purchase Breakdown')</span>
                                    <span class="text-list__item-value">
                                        <ul class="mb-0 ps-3" style="list-style: none; padding-left: 0;">
                                            @foreach($buyData->multiple_orders as $order)
                                                <li style="margin-bottom: 5px;">
                                                    <strong>{{ showAmount($order['quantity'] ?? 0, 4, true, false, false) }}</strong> {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }} 
                                                    @ <strong>{{ showAmount($order['price'] ?? 0, 2, true, false, false) }}</strong> {{ $product && $product->currency ? $product->currency->code : gs('cur_sym') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </span>
                                </li>
                            @endif
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Green Coffee Value')</span>
                                <span class="text-list__item-value">{{ showAmount($buyData->amount) }}</span>
                            </li>
                            @if ($buyData->charge > 0)
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('Charge')</span>
                                    <span class="text-list__item-value">{{ showAmount($buyData->charge) }}</span>
                                </li>
                            @endif
                            @if ($buyData->vat > 0)
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('VAT') ({{ showAmount($chargeLimit->vat, 2, true, false, false) }}%)</span>
                                    <span class="text-list__item-value">{{ showAmount($buyData->vat) }}</span>
                                </li>
                            @endif
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Total Amount')</span>
                                <span class="text-list__item-value">{{ showAmount($buyData->total_amount) }}</span>
                            </li>
                            <li class="text-list__item paymentInfo">
                                <span class="text-list__item-title">@lang('Limit')</span>
                                <span class="text-list__item-value gateway-limit">@lang('00.00')</span>
                            </li>
                            <li class="text-list__item paymentInfo">
                                <span class="text-list__item-title">@lang('Processing Charge')</span>
                                <span class="text-list__item-value"> <span class="processing-fee">@lang('00.00')</span> {{ __(gs('cur_text')) }}</span>
                            </li>
                            <li class="text-list__item paymentInfo">
                                <span class="text-list__item-title">@lang('Total')</span>
                                <span class="text-list__item-value"> <span class="final-amount">@lang('00.00')</span> {{ __(gs('cur_text')) }}</span>
                            </li>
                            <li class="text-list__item gateway-conversion d-none total-amount paymentInfo">
                                <span class="text-list__item-title">@lang('Conversion')</span>
                                <span class="text-list__item-value gateway-conversion-text"> </span>
                            </li>
                            <li class="text-list__item conversion-currency d-none total-amount paymentInfo">
                                <span class="text-list__item-title">
                                    @lang('In') <span class="gateway-currency"></span>
                                </span>
                                <span class="text-list__item-value"> <span class="in-currency"></span> </span>
                            </li>
                            <li class="text-list__item d-none crypto-message paymentInfo">
                                <span class="text-list__item-title">
                                    @lang('Conversion with') <span class="gateway-currency"></span> @lang('and final value will Show on next step')
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="buy-sell-payment__right">
                        <h5 class="buy-sell-payment__title">@lang('Payment Method')</h5>
                        <form action="{{ route('user.buy.payment.submit') }}" method="POST">
                            @csrf
                            <input type="hidden" name="currency" value="{{ gs('cur_text') }}">
                            <input type="hidden" name="amount" class="amount" value="{{ $buyData->total_amount }}">
                            <input type="hidden" name="batch_id" value="{{ $buyData->batch_id }}">
                            <div class="form-group">
                                <div class="form--radio">
                                    <label class="form-check-label" for="main-balance">
                                        <div class="payment-icon-wrapper">
                                            <span class="payment-icon">
                                                <i class="fa fa-user"></i>
                                            </span>
                                        </div>
                                        <span class="payment-name">@lang('Main Balance') ({{ showAmount(auth()->user()->balance) }})</span>
                                    </label>
                                    <input class="form-check-input gateway-input" type="radio" id="main-balance" value="main" type="radio" name="gateway" @checked(old('gateway')) checked>
                                </div>
                                @foreach ($gatewayCurrency as $data)
                                    <div class="form--radio">
                                        <label class="form-check-label" for="{{ titleToKey($data->name) }}">
                                            <div class="payment-icon-wrapper">
                                                <span class="payment-icon"><img src="{{ getImage(getFilePath('gateway') . '/' . $data->method->image) }}" alt="image"></span>
                                            </div>
                                            <span class="payment-name">{{ __($data->name) }}</span>
                                        </label>
                                        <input class="form-check-input gateway-input" type="radio" id="{{ titleToKey($data->name) }}" data-gateway='@json($data)' type="radio" name="gateway" value="{{ $data->method_code }}" @checked(old('gateway') == $data->method_code) data-min-amount="{{ showAmount($data->min_amount) }}" data-max-amount="{{ showAmount($data->max_amount) }}">
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="verification_code" id="verification_code">
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn--base w-100" id="btn-submit">@lang('Proceed to Payment')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="otpModal" tabindex="-1" role="dialog" aria-labelledby="otpModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otpModalLabel">@lang('Verification Code')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>@lang('A verification code has been sent to your email. Please enter it below.')</p>
                    <div class="form-group">
                        <label for="otp_code">@lang('Code')</label>
                        <input type="text" class="form-control" id="otp_code" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="button" class="btn btn--primary" id="verifyOtp">@lang('Verify & Submit')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/22.png') }}" alt="image">
@endsection

@push('script')
    <script>
        "use strict";
        (function($) {

            $('form').on('submit', function(e) {
                var code = $('#verification_code').val();
                if (!code) {
                    e.preventDefault();
                     $.ajax({
                        url: "{{ route('user.send.otp') }}",
                        method: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            if(response.error){
                                notify('error', response.error);
                            }else{
                                notify('success', response.success);
                                $('#otpModal').modal('show');
                            }
                        }
                    });
                }
            });

            $('#verifyOtp').on('click', function() {
                var code = $('#otp_code').val();
                if (code) {
                    $('#verification_code').val(code);
                    $('#otpModal').modal('hide');
                    $('form').unbind('submit').submit();
                } else {
                    notify('error', 'Please enter the code');
                }
            });

            var amount = parseFloat($('.amount').val() || 0);
            var gateway, minAmount, maxAmount;

            $('.amount').on('input', function(e) {
                amount = parseFloat($(this).val());
                if (!amount) {
                    amount = 0;
                }
                calculation();
            });

            $('.gateway-input').on('change', function(e) {
                gatewayChange();
            });

            function gatewayChange() {
                let gatewayElement = $('.gateway-input:checked');
                let methodCode = gatewayElement.val();

                if (methodCode == 'main') {
                    $('.paymentInfo').hide();
                    return false;
                } else {
                    $('.paymentInfo').show();
                }

                gateway = gatewayElement.data('gateway');
                minAmount = gatewayElement.data('min-amount');
                maxAmount = gatewayElement.data('max-amount');

                let processingFeeInfo =
                    `${parseFloat(gateway.percent_charge).toFixed(2)}% with ${parseFloat(gateway.fixed_charge).toFixed(2)} {{ __(gs('cur_text')) }} charge for payment gateway processing fees`
                $(".proccessing-fee-info").attr("data-bs-original-title", processingFeeInfo);
                calculation();
            }

            gatewayChange();

            $(".more-gateway-option").on("click", function(e) {
                let paymentList = $(".gateway-option-list");
                paymentList.find(".gateway-option").removeClass("d-none");
                $(this).addClass('d-none');
                paymentList.animate({
                    scrollTop: (paymentList.height() - 60)
                }, 'slow');
            });

            function calculation() {
                if (!gateway) return;
                $(".gateway-limit").text(minAmount + " - " + maxAmount);

                let percentCharge = 0;
                let fixedCharge = 0;
                let totalPercentCharge = 0;

                if (amount) {
                    percentCharge = parseFloat(gateway.percent_charge);
                    fixedCharge = parseFloat(gateway.fixed_charge);
                    totalPercentCharge = parseFloat(amount / 100 * percentCharge);
                }

                let totalCharge = parseFloat(totalPercentCharge + fixedCharge);
                let totalAmount = parseFloat((amount || 0) + totalPercentCharge + fixedCharge);

                $(".final-amount").text(totalAmount.toFixed(2));
                $(".processing-fee").text(totalCharge.toFixed(2));
                $("input[name=currency]").val(gateway.currency);
                $(".gateway-currency").text(gateway.currency);

                if (amount < Number(gateway.min_amount) || amount > Number(gateway.max_amount)) {
                    $(".deposit-form button[type=submit]").attr('disabled', true);
                } else {
                    $(".deposit-form button[type=submit]").removeAttr('disabled');
                }

                if (gateway.currency != "{{ gs('cur_text') }}" && gateway.method.crypto != 1) {
                    $('.deposit-form').addClass('adjust-height')

                    $(".gateway-conversion, .conversion-currency").removeClass('d-none');
                    $(".gateway-conversion").find('.gateway-conversion-text').html(
                        `1 {{ __(gs('cur_text')) }} = <span class="rate">${parseFloat(gateway.rate).toFixed(2)}</span>  <span class="method_currency">${gateway.currency}</span>`
                    );
                    $('.in-currency').text(parseFloat(totalAmount * gateway.rate).toFixed(gateway.method.crypto == 1 ? 8 : 2))
                } else {
                    $(".gateway-conversion, .conversion-currency").addClass('d-none');
                    $('.deposit-form').removeClass('adjust-height')
                }

                if (gateway.method.crypto == 1) {
                    $('.crypto-message').removeClass('d-none');
                } else {
                    $('.crypto-message').addClass('d-none');
                }
            }

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
            $('.gateway-input').change();
        })(jQuery);
    </script>
@endpush
