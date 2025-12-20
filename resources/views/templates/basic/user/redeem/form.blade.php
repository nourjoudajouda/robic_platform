@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card">
                <h4>@lang('Available for Withdraw')</h4>
                <div class="dashboard-card-wrapper two">
                    @foreach ($assets as $asset)
                        <div class="dashboard-card two assetCard {{ $loop->first ? 'active' : '' }}" data-id="{{ $asset->id }}" data-price="{{ $asset->category->price }}">
                            <div class="dashboard-card__tag">
                                <span class="dashboard-card__tag-icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/29.png') }}" alt="image"></span>
                                <span class="dashboard-card__tag-text">{{ __($asset->category->name) }}</span>
                            </div>
                            <h4 class="dashboard-card__gold">{{ showAmount($asset->quantity, 4, currencyFormat: false) }} <sub>{{ $asset->batch && $asset->batch->product && $asset->batch->product->unit ? $asset->batch->product->unit->symbol : 'Unit' }}</sub></h4>
                            <p class="dashboard-card__desc">{{ showAmount($asset->quantity * $asset->category->price) }}</p>
                        </div>
                    @endforeach
                </div>
                <form action="{{ route('user.redeem.store') }}" method="POST" class="withdraw-form mt-4">
                    @csrf
                    <input type="hidden" name="asset_id" value="{{ $assets->count() ? $assets->first()->id : '0' }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="" class="form--label">@lang('Green Coffee in Bar')</label>
                                @foreach ($redeemUnits->where('type', Status::REDEEM_UNIT_BAR) as $redeemUnit)
                                    <div class="withdraw-gold-item mb-3">
                                        <h6 class="withdraw-gold-item__title"><span class="withdraw-gold-item__icon"> <img src="{{ asset($activeTemplateTrue . 'images/icons/45.png') }}" alt="image"> </span> {{ showAmount($redeemUnit->quantity, 2, currencyFormat: false) }} @lang('Gram Gold')</h6>
                                        <div class="counter">
                                            <button type="button" class="counter__decrement counter__btn"><i class="fas fa-minus"></i></button>
                                            <input type="number" class="counter__field form--control redeemUnitCount" name="redeem_unit_quantity[{{ $redeemUnit->id }}]" value="0">
                                            <button type="button" class="counter__increment counter__btn"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <span class="redeemUnitQuantity d-none">{{ getAmount($redeemUnit->quantity) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="" class="form--label">@lang('Gold in Coin')</label>
                                @foreach ($redeemUnits->where('type', Status::REDEEM_UNIT_COIN) as $redeemUnit)
                                    <div class="withdraw-gold-item mb-3">
                                        <h6 class="withdraw-gold-item__title"><span class="withdraw-gold-item__icon"> <img src="{{ asset($activeTemplateTrue . 'images/icons/46.png') }}" alt="image"> </span>{{ showAmount($redeemUnit->quantity, 2, currencyFormat: false) }} @lang('Gram Coins')</h6>
                                        <div class="counter">
                                            <button type="button" class="counter__decrement counter__btn"><i class="fas fa-minus"></i></button>
                                            <input type="number" class="counter__field form--control redeemUnitCount" name="redeem_unit_quantity[{{ $redeemUnit->id }}]" value="0">
                                            <button type="button" class="counter__increment counter__btn"><i class="fas fa-plus"></i></button>
                                        </div>
                                        <span class="redeemUnitQuantity d-none">{{ getAmount($redeemUnit->quantity) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <p class="withdraw-form__desc"> @lang('Total Charge'): <span class="totalCharge">0.00</span> {{ __(gs('cur_text')) }} ({{ showAmount($chargeLimit->fixed_charge) .' + ' . showAmount($chargeLimit->percent_charge, currencyFormat: false) }}%)
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn--base w-100" @disabled(!$assets->count())>@lang('Continue')</button>
                            </div>
                        </div>
                    </div>
                </form>
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

@push('script')
    <script>
        (function($) {
            "use strict";
            let chargeLimit = @json($chargeLimit);
            let price = `{{ $assets->count() ? $assets->first()->category->price : '0' }}`;

            $(document).on('click', '.assetCard', function() {
                let assetId = $(this).data('id');
                price = $(this).data('price');
                $('[name="asset_id"]').val(assetId);
                $('.assetCard').removeClass('active');
                $(this).addClass('active');
                $('.redeemUnitCount').trigger('change');
            });

            $('.redeemUnitCount').on('change', function() {
                let totalQuantity = 0;
                $('.redeemUnitCount').each(function() {
                    let count = $(this).val();
                    let quantity = $(this).closest('.withdraw-gold-item').find('.redeemUnitQuantity').text();
                    totalQuantity += (count * quantity);
                });

                let totalAmount = totalQuantity * price;
                let charge = parseFloat(chargeLimit.fixed_charge) + (parseFloat(totalAmount) * parseFloat(chargeLimit.percent_charge) / 100);
                $('.totalCharge').text(parseFloat(charge).toFixed(2));
            });

            $('.counter__btn').on('click', function() {
                $(this).closest('.counter').find('.redeemUnitCount').trigger('change');
            });


        })(jQuery);
    </script>
@endpush
