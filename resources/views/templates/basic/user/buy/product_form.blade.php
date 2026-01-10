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
                <div class="bean-calculator">
                    <form action="{{ route('user.buy.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="order_type" id="order_type" value="{{ $cheapestOrder['type'] ?? 'batch' }}">
                        <input type="hidden" name="sell_order_id" id="sell_order_id" value="{{ $cheapestOrder['sell_order_id'] ?? ($cheapestOrder['user_sell_order_id'] ?? '') }}">
                        <div class="bean-calculator__top">
                            <div class="bean-calculator__top-left">
                                <h4 class="bean-calculator__top-amount"> 
                                    @if($marketPrice)
                                        <span class="currentPrice">{{ showAmount($marketPrice, 2, true, false, false) }}</span> 
                                        {{ $product->currency->code ?? gs('cur_sym') }} / {{ $product->unit->symbol ?? 'Unit' }}
                                        <div class="mt-1">
                                            <small class="text-muted">@lang('Market Price')</small>
                                        </div>
                                    @else
                                        <span class="currentPrice">{{ showAmount($cheapestOrder['sell_price'] ?? 0, 2, true, false, false) }}</span> 
                                        {{ $product->currency->code ?? gs('cur_sym') }} / {{ $product->unit->symbol ?? 'Unit' }}
                                    @endif
                                </h4>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        @lang('Total Available Quantity'): <strong class="text-info">{{ showAmount($totalAvailableQuantity, 4, currencyFormat: false) }}</strong> {{ $product->unit->symbol ?? 'Unit' }}
                                    </small>
                                </div>
                            </div>
                            <div class="calculator-switch">
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="beanCalculatorSwitch1" name="purchase_type" checked>
                                    <label class="text" for="beanCalculatorSwitch1">@lang('Purchase in '){{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="calculator-switch__icon"><i class="fa-solid fa-right-left"></i></span>
                                <div class="calculator-switch__item">
                                    <input class="form-check-input" type="radio" id="beanCalculatorSwitch2" name="purchase_type">
                                    <label class="text" for="beanCalculatorSwitch2">@lang('Purchase in Quantity')</label>
                                </div>
                            </div>
                        </div>
                        <div class="bean-calculator__bottom">
                            <div class="bean-calculator__inputs">
                                <div class="form-group position-relative">
                                    <input type="number" step="any" class="form--control" placeholder="00.00" name="amount" id="amount">
                                    <label class="form--label">{{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="equal"><i class="fa-solid fa-equals"></i></span>
                <div class="form-group position-relative has-icon">
                    <span class="icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/23.png') }}" alt="image"></span>
                    <input type="number" step="any" class="form--control" placeholder="00.00" name="quantity" id="quantity" max="{{ $totalAvailableQuantity }}">
                    <label class="form--label">{{ $product->unit->name ?? 'Quantity' }}</label>
                    <small class="text-muted">@lang('Max'): <strong>{{ showAmount($totalAvailableQuantity, 4, currencyFormat: false) }}</strong></small>
                </div>
                            </div>
                            <button type="submit" class="btn btn--base w-100" id="submitBtn" disabled>@lang('Submit')</button>
                            <input type="hidden" name="action_type" id="action_type" value="continue">

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

@push('script')
<script>
    (function($) {
        "use strict";
        let allSellOrders = @json($allSellOrders ?? []);
        let availableQuantity = {{ $totalAvailableQuantity ?? 0 }};
        let purchaseType = 'amount';
        let lastCalculatedPrice = null;
        let lastPriceChanges = [];
        
        // التأكد من أن allSellOrders هو array
        if (!Array.isArray(allSellOrders)) {
            allSellOrders = [];
        }
        
        // إذا لم تكن هناك sell orders، استخدم السعر الافتراضي
        if (allSellOrders.length === 0) {
            const defaultPrice = {{ $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0) }};
            if (defaultPrice > 0) {
                allSellOrders = [{
                    type: 'batch',
                    id: 0,
                    sell_price: defaultPrice,
                    available_quantity: availableQuantity
                }];
            }
        }

        // ترتيب أوامر البيع تصاعديًا حسب السعر لضمان بدء الشراء من سعر السوق (الأرخص أولاً)
        allSellOrders = allSellOrders
            .map(function(order) {
                return {
                    ...order,
                    sell_price: parseFloat(order.sell_price),
                    available_quantity: parseFloat(order.available_quantity)
                };
            })
            .sort(function(a, b) {
                return a.sell_price - b.sell_price;
            });

        // دالة لحساب سعر السوق (المتوسط الموزون) والشراء من أرخص سعر
        function calculatePriceFromMultipleOrders(requestedQuantity) {
            if (!requestedQuantity || requestedQuantity <= 0) {
                return {
                    success: false,
                    available_quantity: 0,
                    average_price: 0,
                    total_amount: 0,
                    orders: [],
                    price_changes: [],
                    insufficient: false,
                    first_price: allSellOrders.length > 0 ? parseFloat(allSellOrders[0].sell_price) : 0,
                    first_price_quantity: 0,
                    pending_quantity: 0,
                    pending_price: 0,
                };
            }

            if (allSellOrders.length === 0) {
                return {
                    success: false,
                    available_quantity: 0,
                    average_price: 0,
                    total_amount: 0,
                    orders: [],
                    price_changes: [],
                    insufficient: true,
                    first_price: 0,
                    first_price_quantity: 0,
                    pending_quantity: requestedQuantity,
                    pending_price: 0,
                };
            }

            // دالة لحساب سعر السوق (المتوسط الموزون)
            function computeMarketPrice(book) {
                const totals = book.reduce((acc, o) => {
                    acc.qty += o.qty;
                    acc.amount += o.qty * o.price;
                    return acc;
                }, { qty: 0, amount: 0 });
                return totals.qty > 0 ? totals.amount / totals.qty : 0;
            }

            // نسخة من order book للحسابات
            let orderBook = allSellOrders.map(o => ({
                ...o,
                price: parseFloat(o.sell_price),
                qty: parseFloat(o.available_quantity),
            }));

            // حساب سعر السوق الأولي
            const initialMarketPrice = computeMarketPrice(orderBook);
            
            // أرخص سعر متوفر
            const cheapestPrice = parseFloat(allSellOrders[0].sell_price);
            
            // حساب الكمية المتوفرة بأرخص سعر فقط
            let quantityAtCheapestPrice = 0;
            for (let order of allSellOrders) {
                const orderPrice = parseFloat(order.sell_price);
                if (Math.abs(orderPrice - cheapestPrice) < 0.01) {
                    quantityAtCheapestPrice += parseFloat(order.available_quantity);
                } else {
                    break;
                }
            }
            
            let remainingQuantity = requestedQuantity;
            let totalAmount = 0;
            let ordersToBuy = [];
            let priceChanges = []; // لتخزين أسعار السوق لكل كمية للعرض
            let fulfilledQuantity = 0;
            let pendingQuantity = 0;
            
            // نسخة من order book للحسابات
            let workingOrderBook = orderBook.map(o => ({...o}));
            
            // نشتري من جميع الأوامر بالترتيب حسب السعر (أرخص أولاً) حتى يتم استيفاء الكمية المطلوبة
            while (remainingQuantity > 0 && workingOrderBook.length > 0) {
                // حساب سعر السوق قبل أخذ الكمية (للعرض)
                const marketPriceBefore = computeMarketPrice(workingOrderBook);
                
                const order = workingOrderBook[0];
                const qtyToTake = Math.min(remainingQuantity, order.qty);
                
                // إضافة order للشراء (بالسعر الفعلي للـ order للشراء الفعلي)
                ordersToBuy.push({
                    type: order.type,
                    order_id: order.id,
                    quantity: qtyToTake,
                    price: order.price, // السعر الفعلي للشراء
                    market_price: marketPriceBefore, // سعر السوق للعرض
                });
                
                // حساب المبلغ بالسعر الفعلي (للشراء الفعلي)
                totalAmount += qtyToTake * order.price;
                
                remainingQuantity -= qtyToTake;
                fulfilledQuantity += qtyToTake;
                
                // خصم الكمية من order book
                workingOrderBook[0].qty -= qtyToTake;
                if (workingOrderBook[0].qty <= 0) {
                    workingOrderBook.shift();
                }
                
                // حساب سعر السوق بعد أخذ الكمية
                const marketPriceAfter = workingOrderBook.length > 0 ? computeMarketPrice(workingOrderBook) : marketPriceBefore;
                
                // إضافة price change للعرض (أسعار السوق) - منفصل لكل كمية
                priceChanges.push({
                    from_price: marketPriceBefore,
                    to_price: marketPriceAfter,
                    quantity_at_old_price: fulfilledQuantity - qtyToTake,
                    quantity_at_new_price: qtyToTake,
                });
            }
            
            // حساب السعر المتوسط الموزون
            const averagePrice = fulfilledQuantity > 0 ? totalAmount / fulfilledQuantity : 0;
            
            // حساب سعر السوق الجديد بعد استهلاك الكمية المتوفرة (للمودال)
            const nextAvailablePrice = workingOrderBook.length > 0 ? computeMarketPrice(workingOrderBook) : initialMarketPrice;
            
            // التحقق من نجاح العملية - المودال يظهر عندما تكون الكمية أكبر من quantityAtCheapestPrice
            if (requestedQuantity > quantityAtCheapestPrice) {
                // الكمية المطلوبة أكبر من المتوفرة بأرخص سعر - إظهار المودال
                pendingQuantity = remainingQuantity;
                
                return {
                    success: false,
                    message: 'Insufficient quantity at lowest price',
                    available_quantity: fulfilledQuantity,
                    average_price: averagePrice,
                    total_amount: totalAmount,
                    orders: ordersToBuy,
                    price_changes: priceChanges,
                    insufficient: true,
                    first_price: initialMarketPrice,
                    first_price_quantity: quantityAtCheapestPrice,
                    pending_quantity: pendingQuantity,
                    pending_price: averagePrice,
                    next_available_price: nextAvailablePrice,
                    quantity_at_cheapest_price: quantityAtCheapestPrice,
                };
            } else {
                // تم استيفاء الكمية المطلوبة بالكامل
                return {
                    success: true,
                    available_quantity: fulfilledQuantity,
                    average_price: averagePrice,
                    total_amount: totalAmount,
                    orders: ordersToBuy,
                    price_changes: priceChanges,
                    insufficient: false,
                    first_price: initialMarketPrice,
                    first_price_quantity: quantityAtCheapestPrice,
                    pending_quantity: 0,
                    pending_price: 0,
                    quantity_at_cheapest_price: quantityAtCheapestPrice,
                };
            }
        }

        function calculateValues() {
            // التحقق من وجود sell orders
            if (!allSellOrders || allSellOrders.length === 0) {
                // استخدام سعر السوق إذا لم تكن هناك sell orders
                const marketPrice = {{ $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0) }};
                if (marketPrice > 0) {
                    if (purchaseType === 'amount') {
                        const amount = parseFloat($('#amount').val()) || 0;
                        if (amount > 0) {
                            const quantity = amount / marketPrice;
                            $('#quantity').val(quantity.toFixed(4));
                            updatePriceDisplay(marketPrice, []);
                            validateForm();
                        }
                    } else {
                        const quantity = parseFloat($('#quantity').val()) || 0;
                        if (quantity > 0) {
                            const amount = quantity * marketPrice;
                            $('#amount').val(amount.toFixed(2));
                            updatePriceDisplay(marketPrice, []);
                            validateForm();
                        }
                    }
                }
                return;
            }
            
            if (purchaseType === 'amount') {
                const amount = parseFloat($('#amount').val()) || 0;
                if (amount > 0) {
                    // استخدام السعر الأول المتاح أو سعر السوق
                    const firstPrice = allSellOrders.length > 0 ? parseFloat(allSellOrders[0].sell_price) : 0;
                    const marketPrice = {{ $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0) }};
                    const priceToUse = marketPrice > 0 ? marketPrice : firstPrice;
                    
                    if (priceToUse > 0) {
                        const estimatedQuantity = amount / priceToUse;
                        
                        // حساب السعر الفعلي من عدة sell orders
                        const priceCalc = calculatePriceFromMultipleOrders(estimatedQuantity);
                        
                        if (priceCalc && priceCalc.average_price > 0) {
                            if (priceCalc.success) {
                                $('#quantity').val(priceCalc.available_quantity.toFixed(4));
                                updatePriceDisplay(priceCalc.average_price, priceCalc.price_changes, priceCalc.orders);
                                // لا نعدل قيمة المبلغ الذي أدخله المستخدم، فقط نعرض الكمية
                                hideOptionsModal();
                            } else {
                                // إظهار الخيارين
                                showOptionsModal(priceCalc);
                            }
                        } else {
                            // استخدام السعر البسيط إذا فشل الحساب المعقد
                            $('#quantity').val(estimatedQuantity.toFixed(4));
                            updatePriceDisplay(priceToUse, [], [{ quantity: estimatedQuantity, price: priceToUse }]);
                        }
                    } else {
                        $('#quantity').val('');
                        resetPriceDisplay();
                    }
                } else {
                    $('#quantity').val('');
                    resetPriceDisplay();
                }
            } else {
                const quantity = parseFloat($('#quantity').val()) || 0;
                if (quantity > 0) {
                    const priceCalc = calculatePriceFromMultipleOrders(quantity);
                    
                    if (priceCalc && priceCalc.average_price > 0) {
                        if (priceCalc.success) {
                            $('#amount').val(priceCalc.total_amount.toFixed(2));
                            updatePriceDisplay(priceCalc.average_price, priceCalc.price_changes, priceCalc.orders);
                            hideOptionsModal();
                        } else {
                            // إظهار الخيارين - أيضاً عرض breakdown في الصفحة
                            $('#amount').val(priceCalc.total_amount.toFixed(2));
                            updatePriceDisplay(priceCalc.average_price, priceCalc.price_changes, priceCalc.orders);
                            showOptionsModal(priceCalc);
                        }
                    } else {
                        // استخدام السعر البسيط إذا فشل الحساب المعقد
                        const firstPrice = allSellOrders.length > 0 ? parseFloat(allSellOrders[0].sell_price) : 0;
                        const marketPrice = {{ $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0) }};
                        const priceToUse = marketPrice > 0 ? marketPrice : firstPrice;
                        if (priceToUse > 0) {
                            $('#amount').val((quantity * priceToUse).toFixed(2));
                            updatePriceDisplay(priceToUse, [], [{ quantity: quantity, price: priceToUse }]);
                        }
                    }
                } else {
                    $('#amount').val('');
                    resetPriceDisplay();
                }
            }
            validateForm();
        }
        
        function updatePriceDisplay(averagePrice, priceChanges, orders) {
            const oldPrice = lastCalculatedPrice;
            
            // إظهار ملخص الأسعار والكميات - نعطي الأولوية لـ orders لأنها تحتوي على التفصيل الفعلي لكل batch
            if (orders && Array.isArray(orders) && orders.length > 0) {
                // استخدام السعر الفعلي من orders (price) وليس market_price
                const breakdownOrders = orders.map(function(order) {
                    return {
                        quantity: parseFloat(order.quantity) || 0,
                        price: parseFloat(order.price) || 0, // السعر الفعلي لكل batch
                        type: order.type || 'batch', // نوع الطلب (batch أو user)
                        order_id: order.order_id || 0,
                    };
                });
                showPriceAlert('info', '@lang("Purchase breakdown")', breakdownOrders);
            } else if (priceChanges && Array.isArray(priceChanges) && priceChanges.length > 0) {
                // استخدام price_changes كبديل إذا لم تكن orders متوفرة
                const breakdownOrders = priceChanges.map(function(change) {
                    return {
                        quantity: parseFloat(change.quantity_at_new_price) || 0,
                        price: parseFloat(change.from_price) || 0, // سعر السوق قبل أخذ الكمية
                    };
                });
                showPriceAlert('info', '@lang("Purchase breakdown")', breakdownOrders);
            }
            
            lastCalculatedPrice = averagePrice;
            lastPriceChanges = priceChanges || [];
        }
        
        function resetPriceDisplay() {
            const marketPrice = {{ $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0) }};
            $('.currentPrice').text(parseFloat(marketPrice).toFixed(2));
            lastCalculatedPrice = null;
            lastPriceChanges = [];
            hidePriceAlert();
        }
        
        function showPriceAlert(type, message, orders = []) {
            hidePriceAlert();
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
            const currencyCode = '{{ $product->currency->code ?? gs("cur_sym") }}';
            const unitSymbol = '{{ $product->unit->symbol ?? "Unit" }}';
            
            let breakdownHtml = '';
            if (orders && orders.length > 0) {
                const listItems = orders.map(function(order) {
                    return '<li><strong>' + parseFloat(order.quantity).toFixed(4) + '</strong> ' + unitSymbol +
                        ' @ <strong>' + parseFloat(order.price).toFixed(2) + '</strong> ' + currencyCode + '</li>';
                }).join('');
                breakdownHtml = '<div class="mt-2"><small class="text-muted">@lang("Breakdown")</small><ul class="mb-0 ps-3">' + listItems + '</ul></div>';
            }
            
            const alertHtml = '<div class="alert ' + alertClass + ' mt-2 price-alert" role="alert">' +
                '<i class="fas fa-info-circle me-2"></i>' + message +
                breakdownHtml +
                '</div>';
            $('.bean-calculator__top').after(alertHtml);
        }
        
        function hidePriceAlert() {
            $('.price-alert').remove();
        }
        
        function showOptionsModal(priceCalc) {
            const availableQty = priceCalc.available_quantity.toFixed(4);
            const pendingQty = priceCalc.pending_quantity.toFixed(4);
            const currentMarketPrice = priceCalc.first_price ? priceCalc.first_price.toFixed(2) : '0.00'; // سعر السوق الأولي
            const newMarketPrice = priceCalc.next_available_price ? priceCalc.next_available_price.toFixed(2) : currentMarketPrice;
            
            // حساب الإجمالي من price_changes بسعر السوق (للعرض)
            let estimatedTotal = 0;
            if (priceCalc.price_changes && priceCalc.price_changes.length > 0) {
                // حساب من price_changes بسعر السوق
                estimatedTotal = priceCalc.price_changes.reduce(function(sum, change) {
                    return sum + (parseFloat(change.quantity_at_new_price) * parseFloat(change.from_price));
                }, 0);
            } else {
                // استخدام total_amount كبديل
                estimatedTotal = priceCalc.total_amount || 0;
            }
            estimatedTotal = estimatedTotal.toFixed(2);
            
            // بناء breakdown من orders (التفصيل الفعلي لكل batch)
            let breakdownHtml = '';
            if (priceCalc.orders && priceCalc.orders.length > 0) {
                // استخدام orders للعرض التفصيلي لكل batch
                const listItems = priceCalc.orders.map(function(order) {
                    const qty = parseFloat(order.quantity || 0).toFixed(4);
                    const price = parseFloat(order.price || 0).toFixed(2); // السعر الفعلي لكل batch
                    return '<li><strong>' + qty + '</strong> {{ $product->unit->symbol ?? "Unit" }} @ <strong>' + price + '</strong> {{ $product->currency->code ?? gs("cur_sym") }}</li>';
                }).join('');
                breakdownHtml = '<div class="mt-2"><small class="text-muted">@lang("Breakdown")</small><ul class="mb-0 ps-3">' + listItems + '</ul></div>';
            } else if (priceCalc.price_changes && priceCalc.price_changes.length > 0) {
                // استخدام price_changes كبديل إذا لم تكن orders متوفرة
                const listItems = priceCalc.price_changes.map(function(change) {
                    const qty = parseFloat(change.quantity_at_new_price).toFixed(4);
                    const price = parseFloat(change.from_price).toFixed(2); // سعر السوق قبل أخذ الكمية
                    return '<li><strong>' + qty + '</strong> {{ $product->unit->symbol ?? "Unit" }} @ <strong>' + price + '</strong> {{ $product->currency->code ?? gs("cur_sym") }}</li>';
                }).join('');
                breakdownHtml = '<div class="mt-2"><small class="text-muted">@lang("Breakdown")</small><ul class="mb-0 ps-3">' + listItems + '</ul></div>';
            }
            
            let modalHtml = `
                <div class="modal fade show" id="buyOptionsModal" tabindex="-1" style="display: block;">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="background: hsl(var(--section-bg)); border: 1px solid hsl(var(--border-color));">
                            <div class="modal-header" style="border-bottom: 1px solid hsl(var(--border-color));">
                                <h5 class="modal-title text-white">@lang('Insufficient Quantity Available')</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-white">
                                <p>@lang('Available quantity at current market price') <strong>${currentMarketPrice}</strong> {{ $product->currency->code ?? gs('cur_sym') }}: <strong>${availableQty}</strong> {{ $product->unit->symbol ?? 'Unit' }}</p>
                                <p>@lang('Requested quantity'): <strong>${(parseFloat($('#quantity').val()) || (parseFloat($('#amount').val()) / currentMarketPrice)).toFixed(4)}</strong> {{ $product->unit->symbol ?? 'Unit' }}</p>
                                <p class="text-warning">@lang('Pending quantity'): <strong>${pendingQty}</strong> {{ $product->unit->symbol ?? 'Unit' }}</p>
                                
                                <div class="mt-4">
                                    <h6>@lang('Choose an option'):</h6>
                                    
                                    <div class="card mb-3" style="background: rgba(255,255,255,0.05); border: 1px solid hsl(var(--base));">
                                        <div class="card-body">
                                            <h6 class="text-base">@lang('Option 1: Continue with New Price')</h6>
                                            <p class="mb-2">@lang('Buy all quantity - market price will change')</p>
                                            ${breakdownHtml}
                                            <ul class="mb-2 mt-2">
                                                <li class="text-info">@lang('Estimated Total'): <strong>${estimatedTotal}</strong> {{ $product->currency->code ?? gs('cur_sym') }}</li>
                                            </ul>
                                            <button type="button" class="btn btn--base w-100" onclick="continueWithNewPrice()">
                                                @lang('Continue with New Price')
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="card" style="background: rgba(255,255,255,0.05); border: 1px solid hsl(var(--info));">
                                        <div class="card-body">
                                            <h6 class="text-info">@lang('Option 2: Pending Order')</h6>
                                            <p class="mb-2">@lang('Buy available now, wait for remaining at current market price')</p>
                                            <ul class="mb-2">
                                                <li>@lang('Buy Now'): <strong>${availableQty}</strong> {{ $product->unit->symbol ?? 'Unit' }} @lang('at') <strong>${currentMarketPrice}</strong> {{ $product->currency->code ?? gs('cur_sym') }}</li>
                                                <li>@lang('Pending'): <strong>${pendingQty}</strong> {{ $product->unit->symbol ?? 'Unit' }} @lang('at') <strong>${currentMarketPrice}</strong> {{ $product->currency->code ?? gs('cur_sym') }}</li>
                                                <li class="text-muted"><small>@lang('You will be notified when quantity available at this market price')</small></li>
                                            </ul>
                                            <button type="button" class="btn btn-outline--info w-100" onclick="createPendingOrder()">
                                                @lang('Create Pending Order')
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-backdrop fade show"></div>
            `;
            
            // إزالة أي modal سابق
            hideOptionsModal();
            
            // إضافة modal جديد
            $('body').append(modalHtml);
            
            // حفظ بيانات الحساب
            window.currentPriceCalc = priceCalc;
            
            // إضافة event listener للإغلاق
            $('#buyOptionsModal .btn-close, .modal-backdrop').on('click', function() {
                hideOptionsModal();
            });
        }
        
        function hideOptionsModal() {
            $('#buyOptionsModal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('overflow', '');
            $('body').css('padding-right', '');
        }
        
        function continueWithNewPrice() {
            // حساب الكمية الكاملة المطلوبة بسعر السوق المتغير
            const priceCalc = window.currentPriceCalc;
            const requestedQuantity = priceCalc.available_quantity + priceCalc.pending_quantity;
            
            // إعادة حساب من جميع الـ orders (مش بس من أرخص سعر)
            // نحسب بنفس طريقة Backend - المتوسط الموزون
            let orderBook = allSellOrders.map(o => ({
                ...o,
                price: parseFloat(o.sell_price),
                qty: parseFloat(o.available_quantity),
            }));
            
            function computeMarketPrice(book) {
                const totals = book.reduce((acc, o) => {
                    acc.qty += o.qty;
                    acc.amount += o.qty * o.price;
                    return acc;
                }, { qty: 0, amount: 0 });
                return totals.qty > 0 ? totals.amount / totals.qty : 0;
            }
            
            let remainingQuantity = requestedQuantity;
            let totalAmount = 0;
            let ordersToBuy = [];
            let priceChanges = [];
            
            while (remainingQuantity > 0 && orderBook.length > 0) {
                // حساب سعر السوق قبل أخذ الكمية (للعرض)
                const marketPriceBefore = computeMarketPrice(orderBook);
                const top = orderBook[0];
                const qtyToTake = Math.min(remainingQuantity, top.qty);

                // إضافة order للشراء (بالسعر الفعلي للشراء)
                ordersToBuy.push({
                    type: top.type,
                    order_id: top.id,
                    quantity: qtyToTake,
                    price: top.price, // السعر الفعلي للشراء
                    market_price: marketPriceBefore, // سعر السوق للعرض
                });

                // حساب المبلغ بالسعر الفعلي (للشراء الفعلي)
                totalAmount += qtyToTake * top.price;

                // إضافة price change للعرض (أسعار السوق)
                if (priceChanges.length > 0) {
                    const lastPriceChange = priceChanges[priceChanges.length - 1];
                    if (Math.abs(lastPriceChange.to_price - marketPriceBefore) > 0.01) {
                        priceChanges.push({
                            from_price: lastPriceChange.to_price,
                            to_price: marketPriceBefore,
                            quantity_at_old_price: ordersToBuy.reduce((sum, o) => sum + o.quantity, 0) - qtyToTake,
                            quantity_at_new_price: qtyToTake,
                        });
                    } else {
                        priceChanges[priceChanges.length - 1].quantity_at_new_price += qtyToTake;
                    }
                } else {
                    priceChanges.push({
                        from_price: marketPriceBefore,
                        to_price: marketPriceBefore,
                        quantity_at_old_price: 0,
                        quantity_at_new_price: qtyToTake,
                    });
                }

                remainingQuantity -= qtyToTake;

                top.qty -= qtyToTake;
                if (top.qty <= 0) {
                    orderBook.shift();
                }

                // حساب سعر السوق بعد أخذ الكمية (للـ price change التالي)
                if (orderBook.length > 0) {
                    const marketPriceAfter = computeMarketPrice(orderBook);
                    if (Math.abs(marketPriceAfter - marketPriceBefore) > 0.01) {
                        priceChanges[priceChanges.length - 1].to_price = marketPriceAfter;
                    }
                }
            }
            
            const averagePrice = requestedQuantity > 0 ? totalAmount / requestedQuantity : 0;
            
            // تحديث القيم
            if (purchaseType === 'quantity') {
                $('#quantity').val(requestedQuantity.toFixed(4));
                $('#amount').val(totalAmount.toFixed(2));
            } else {
                $('#amount').val(totalAmount.toFixed(2));
                $('#quantity').val(requestedQuantity.toFixed(4));
            }
            
            updatePriceDisplay(averagePrice, priceChanges, ordersToBuy);
            hideOptionsModal();
            validateForm();
        }
        
        function createPendingOrder() {
            const priceCalc = window.currentPriceCalc;
            const requestedQuantity = parseFloat($('#quantity').val() || ($('#amount').val() / priceCalc.first_price));
            const pendingQuantity = priceCalc.pending_quantity;
            const requestedPrice = priceCalc.first_price;
            const fulfilledQuantity = priceCalc.available_quantity;
            
            // إرسال طلب لإنشاء pending order
            $.ajax({
                url: '{{ route("user.buy.pending.create") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    product_id: {{ $product->id }},
                    requested_quantity: requestedQuantity,
                    requested_price: requestedPrice,
                    pending_quantity: pendingQuantity,
                    fulfilled_quantity: fulfilledQuantity,
                },
                success: function(response) {
                    hideOptionsModal();
                    // تحديث القيم للكمية المتوفرة فقط (الخيار الأول)
                    if (purchaseType === 'quantity') {
                        $('#quantity').val(fulfilledQuantity.toFixed(4));
                        $('#amount').val(priceCalc.total_amount.toFixed(2));
                    } else {
                        $('#amount').val(priceCalc.total_amount.toFixed(2));
                        $('#quantity').val(fulfilledQuantity.toFixed(4));
                    }
                    updatePriceDisplay(priceCalc.average_price, priceCalc.price_changes, priceCalc.orders);
                    validateForm();
                    
                    // عرض رسالة نجاح
                    showPriceAlert('success', response.message || '@lang("Pending order created successfully. You will be notified when the quantity becomes available.")');
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || '@lang("An error occurred. Please try again.")';
                    showPriceAlert('error', errorMsg);
                }
            });
        }

        // جعل الدوال متاحة في النطاق العام (global scope)
        window.hideOptionsModal = hideOptionsModal;
        window.continueWithNewPrice = continueWithNewPrice;
        window.createPendingOrder = createPendingOrder;

        function validateForm() {
            const amount = parseFloat($('#amount').val()) || 0;
            const quantity = parseFloat($('#quantity').val()) || 0;
            const totalMarketQuantity = {{ $totalAvailableQuantity ?? 0 }};
            let isValid = true;

            // إزالة رسائل الخطأ السابقة
            $('.quantity-error').remove();

            if (purchaseType === 'amount') {
                if (amount <= 0) {
                    isValid = false;
                } else {
                    const marketPrice = {{ $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0) }};
                    const calculatedQuantity = amount / marketPrice;
                    if (calculatedQuantity > totalMarketQuantity) {
                        isValid = false;
                        showQuantityError(calculatedQuantity, totalMarketQuantity);
                    }
                }
            } else {
                if (quantity <= 0) {
                    isValid = false;
                } else if (quantity > totalMarketQuantity) {
                    isValid = false;
                    showQuantityError(quantity, totalMarketQuantity);
                }
            }

            $('button[type="submit"]').attr('disabled', !isValid);
        }

        function showQuantityError(requested, available) {
            const errorHtml = '<div class="alert alert-danger mt-2 quantity-error" role="alert">' +
                '<i class="fas fa-exclamation-triangle me-2"></i>' +
                '@lang("Requested quantity") (<strong>' + requested.toFixed(4) + '</strong> {{ $product->unit->symbol ?? "Unit" }}) ' +
                '@lang("exceeds available quantity in market") (<strong>' + available.toFixed(4) + '</strong> {{ $product->unit->symbol ?? "Unit" }})' +
                '</div>';
            $('.bean-calculator__inputs').after(errorHtml);
        }

        $('input[name="purchase_type"]').on('change', function() {
            purchaseType = $(this).attr('id') === 'beanCalculatorSwitch1' ? 'amount' : 'quantity';
            // إعادة تعيين القيم عند تغيير نوع الشراء
            if (purchaseType === 'amount') {
                $('#quantity').val('');
            } else {
                $('#amount').val('');
            }
            resetPriceDisplay();
            validateForm();
        });

        // ربط الأحداث مباشرة - الحساب الفوري في كلا الاتجاهين
        // استخدام jQuery ready داخل الـ IIFE
        $(function() {
            // التحقق من وجود الحقول
            const amountField = $('#amount');
            const quantityField = $('#quantity');
            
            if (amountField.length && quantityField.length) {
                // عند تغيير المبلغ - احسب الكمية
                amountField.on('input keyup paste change', function() {
                    const amount = parseFloat($(this).val()) || 0;
                    if (amount > 0) {
                        purchaseType = 'amount';
                        calculateValues();
                    } else {
                        quantityField.val('');
                        resetPriceDisplay();
                        validateForm();
                    }
                });

                // عند تغيير الكمية - احسب المبلغ
                quantityField.on('input keyup paste change', function() {
                    const quantity = parseFloat($(this).val()) || 0;
                    if (quantity > 0) {
                        purchaseType = 'quantity';
                        calculateValues();
                    } else {
                        amountField.val('');
                        resetPriceDisplay();
                        validateForm();
                    }
                });
                
                // Initialize
                validateForm();
            }
        });
    })(jQuery);
</script>
@endpush
