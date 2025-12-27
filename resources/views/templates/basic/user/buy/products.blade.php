@extends($activeTemplate . 'layouts.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-xxl-12">
        <div class="row">
            @forelse($productsWithSellOrders as $productId => $productData)
                @php
                    $product = $productData['product'];
                    $sellOrders = $productData['sell_orders'];
                    $cheapestOrder = $sellOrders[0] ?? null;
                    $marketPrice = $marketPrices[$productId] ?? null;
                    $displayPrice = $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0);
                    $totalAvailableQuantity = array_sum(array_column($sellOrders, 'available_quantity'));
                @endphp
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="product-card">
                        <div class="product-card__header">
                            <div class="product-card__icons">
                                <img src="{{ asset($activeTemplateTrue . 'images/icons/coffee-bean.png') }}" alt="Coffee Bean">
                            </div>
                        </div>
                        <div class="product-card__body">
                            <h5 class="product-card__title">{{ $product->name ?? 'N/A' }}</h5>
                            <div class="product-card__price">
                                <span class="price">{{ showAmount($displayPrice, 2, true, false, false) }}</span>
                                <span class="currency">{{ $product->currency->code ?? gs('cur_sym') }}</span>
                                <span class="separator">/</span>
                                <span class="unit">{{ $product->unit->symbol ?? 'Unit' }}</span>
                                @if($marketPrice)
                                    <div class="market-price-badge">
                                        <small>@lang('Market Price')</small>
                                    </div>
                                @endif
                            </div>
                            <div class="product-card__info">
                                @php
                                    // جلب batch من أول sell order لهذا المنتج المحدد
                                    // بما أن sell orders تم فلترتها حسب product_id في Controller،
                                    // يمكننا استخدام أول batch مباشرة
                                    $batch = null;
                                    
                                    // البحث في sell orders - استخدام أول batch مرتبط بنفس المنتج
                                    if (!empty($sellOrders)) {
                                        foreach ($sellOrders as $sellOrder) {
                                            if (isset($sellOrder['order'])) {
                                                $orderObj = $sellOrder['order'];
                                                
                                                // التحقق من product_id من order مباشرة
                                                $orderProductId = $orderObj->product_id ?? null;
                                                
                                                // إذا كان product_id يطابق المنتج الحالي
                                                if ($orderProductId == $productId) {
                                                    // إذا كان batch sell order
                                                    if ($sellOrder['type'] == 'batch' && $orderObj->batch) {
                                                        // التأكد من أن batch مرتبط بنفس المنتج
                                                        $batchProductId = $orderObj->batch->product_id ?? null;
                                                        if ($batchProductId == $productId) {
                                                            $batch = $orderObj->batch;
                                                            break;
                                                        }
                                                    }
                                                    // إذا كان user sell order
                                                    elseif ($sellOrder['type'] == 'user' && $orderObj->batch) {
                                                        // التأكد من أن batch مرتبط بنفس المنتج
                                                        $batchProductId = $orderObj->batch->product_id ?? null;
                                                        if ($batchProductId == $productId) {
                                                            $batch = $orderObj->batch;
                                                            break;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // إذا لم يوجد batch من orders، جرب batches array (التي تم فلترتها في Controller)
                                    if (!$batch && !empty($productData['batches'])) {
                                        foreach ($productData['batches'] as $batchItem) {
                                            if ($batchItem && isset($batchItem->product_id) && $batchItem->product_id == $productId) {
                                                $batch = $batchItem;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    $qualityGrade = $batch ? ($batch->quality_grade ?? null) : null;
                                    $originCountry = $batch ? ($batch->origin_country ?? null) : null;
                                @endphp
                                @if($qualityGrade)
                                <p class="info-item">
                                    <span class="info-label">@lang('Quality Grade')</span>
                                    <span class="info-value">{{ $qualityGrade }}</span>
                                </p>
                                @endif
                                @if($originCountry)
                                <p class="info-item">
                                    <span class="info-label">@lang('Origin Country')</span>
                                    <span class="info-value">{{ $originCountry }}</span>
                                </p>
                                @endif
                                <p class="info-item">
                                    <span class="info-label">@lang('Available Quantity')</span>
                                    <span class="info-value">{{ showAmount($totalAvailableQuantity, 4, currencyFormat: false) }} {{ $product->unit->symbol ?? 'Unit' }}</span>
                                </p>
                            </div>
                            <a href="{{ route('user.buy.product', $productId) }}" class="btn btn--base w-100 product-card__btn">
                                @lang('Buy Now')
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>
                        @lang('No products available at the moment.')
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/22.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.buy.history') }}" class="btn btn--base btn--lg">
        <i class="fas fa-history"></i> @lang('Buy History')
    </a>
@endpush

@push('style')
<style>
    .product-card {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 25px;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .product-card__header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .product-card__icons {
        display: flex;
        justify-content: center;
        gap: 10px;
        align-items: center;
    }
    
    .product-card__icons .coffee-bean-icon {
        width: 30px;
        height: 30px;
        object-fit: contain;
    }
    
    .product-card__body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .product-card__title {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 15px;
        text-align: center;
    }
    
    .product-card__price {
        text-align: center;
        margin-bottom: 20px;
        color: #fff;
        font-size: 16px;
    }
    
    .product-card__price .price {
        font-weight: 700;
        font-size: 18px;
        color: hsl(var(--base));
    }
    
    .product-card__price .currency {
        font-weight: 600;
        margin-left: 2px;
    }
    
    .product-card__price .separator {
        margin: 0 5px;
    }
    
    .product-card__price .unit {
        margin-left: 3px;
        text-transform: capitalize;
    }
    
    .product-card__info {
        flex: 1;
        margin-bottom: 20px;
    }
    
    .product-card__info .info-item {
        color: #fff;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
    }
    
    .product-card__info .info-label {
        color: #999;
    }
    
    .product-card__info .info-value {
        color: #fff;
        font-weight: 500;
    }
    
    .product-card__btn {
        margin-top: auto;
        font-weight: 600;
        padding: 12px;
        border-radius: 8px;
    }
    
    .market-price-badge {
        margin-top: 5px;
        text-align: center;
    }
    
    .market-price-badge small {
        color: hsl(var(--base));
        font-size: 11px;
        font-weight: 500;
    }
</style>
@endpush
