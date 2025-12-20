@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="dashboard-card">
                <div class="dashboard-card__body">
                    <div class="text-center mb-4">
                        <div class="success-icon mb-3">
                            <i class="fas fa-check-circle" style="font-size: 64px; color: hsl(var(--base));"></i>
                        </div>
                        <h4 class="text-white mb-3">
                            @lang("Congratulations! You've successfully purchased Green Coffee!")
                        </h4>
                    </div>
                    
                    <div class="purchase-summary">
                        <ul class="text-list">
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Green Coffee Quantity')</span>
                                <span class="text-list__item-value">{{ showAmount($purchaseData['quantity'], 4, true, false, false) }} {{ $purchaseData['unit_symbol'] }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Green Coffee Value')</span>
                                <span class="text-list__item-value">{{ showAmount($purchaseData['amount']) }}</span>
                            </li>
                            @if ($purchaseData['charge'] > 0)
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('Charge')</span>
                                    <span class="text-list__item-value">{{ showAmount($purchaseData['charge']) }}</span>
                                </li>
                            @endif
                            @if ($purchaseData['vat'] > 0)
                                <li class="text-list__item">
                                    <span class="text-list__item-title">@lang('VAT')</span>
                                    <span class="text-list__item-value">{{ showAmount($purchaseData['vat']) }}</span>
                                </li>
                            @endif
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Total Amount')</span>
                                <span class="text-list__item-value">{{ showAmount($purchaseData['total_amount']) }}</span>
                            </li>
                            <li class="text-list__item">
                                <span class="text-list__item-title">@lang('Transaction ID')</span>
                                <span class="text-list__item-value">{{ $purchaseData['trx'] }}</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <a href="{{ route('user.buy.form') }}" class="btn btn--base btn--lg me-2">
                            <i class="fas fa-shopping-cart me-2"></i> @lang('Buy Again')
                        </a>
                        <a href="{{ route('user.buy.history') }}" class="btn btn-outline--base btn--lg">
                            <i class="fas fa-history me-2"></i> @lang('View History')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

