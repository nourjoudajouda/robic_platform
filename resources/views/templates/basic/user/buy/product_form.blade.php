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
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="order_type" id="order_type" value="{{ $cheapestOrder['type'] ?? 'batch' }}">
                        <input type="hidden" name="sell_order_id" id="sell_order_id" value="{{ $cheapestOrder['sell_order_id'] ?? ($cheapestOrder['user_sell_order_id'] ?? '') }}">
                        
                        <!-- معلومات المنتج -->
                        <div class="product-info mb-4 p-3" style="background: rgba(255, 255, 255, 0.05); border-radius: 8px;">
                            <h5 class="text-white mb-3">{{ $product->name ?? 'N/A' }}</h5>
                            <div class="row">
                                @if($qualityGrade)
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted">@lang('Quality Grade'):</small>
                                    <strong class="text-white d-block">{{ $qualityGrade }}</strong>
                                </div>
                                @endif
                                @if($originCountry)
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted">@lang('Origin Country'):</small>
                                    <strong class="text-white d-block">{{ $originCountry }}</strong>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="gold-calculator__top">
                            <div class="gold-calculator__top-left">
                                <h4 class="gold-calculator__top-amount"> 
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
                                    <input type="number" step="any" class="form--control" placeholder="00.00" name="amount" id="amount">
                                    <label class="form--label">{{ __(gs('cur_text')) }}</label>
                                </div>
                                <span class="equal"><i class="fa-solid fa-equals"></i></span>
                                <div class="form-group position-relative has-icon">
                                    <span class="icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/23.png') }}" alt="image"></span>
                                    <input type="number" step="any" class="form--control" placeholder="00.00" name="quantity" id="quantity">
                                    <label class="form--label">{{ $product->unit->name ?? 'Quantity' }}</label>
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

        // دالة لحساب السعر المتوسط مع إعادة تسعير السوق بعد استهلاك كل باتش
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

            // نسخة قابلة للتعديل لحساب السوق المتغير
            let orderBook = allSellOrders.map(o => ({
                ...o,
                price: parseFloat(o.sell_price),
                qty: parseFloat(o.available_quantity),
            }));

            // دالة لحساب متوسط السعر (سعر السوق الحالي) بناءً على الكميات المتبقية
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

            let firstPrice = computeMarketPrice(orderBook);
            let firstPriceQuantity = orderBook.reduce((sum, o) => sum + o.qty, 0);
            let prevPrice = null;

            while (remainingQuantity > 0 && orderBook.length > 0) {
                // سعر السوق الحالي قبل أخذ الكمية
                const currentMarket = computeMarketPrice(orderBook);
                const top = orderBook[0];
                const qtyToTake = Math.min(remainingQuantity, top.qty);

                // سجل تغير السعر إن وجد
                if (prevPrice !== null && currentMarket !== prevPrice) {
                    priceChanges.push({
                        from_price: prevPrice,
                        to_price: currentMarket,
                        quantity_at_old_price: ordersToBuy.reduce((sum, o) => sum + o.quantity, 0),
                        quantity_at_new_price: qtyToTake,
                    });
                }

                ordersToBuy.push({
                    type: top.type,
                    order_id: top.id,
                    quantity: qtyToTake,
                    price: currentMarket,
                });

                totalAmount += qtyToTake * currentMarket;
                remainingQuantity -= qtyToTake;
                prevPrice = currentMarket;

                // خصم الكمية المأخوذة من أعلى باتش
                top.qty -= qtyToTake;
                if (top.qty <= 0) {
                    orderBook.shift();
                }
            }

            const fulfilledQuantity = requestedQuantity - remainingQuantity;
            const averagePrice = fulfilledQuantity > 0 ? totalAmount / fulfilledQuantity : 0;
            const hasInsufficientQuantity = remainingQuantity > 0;
            const pendingPrice = computeMarketPrice(orderBook);

            return {
                success: !hasInsufficientQuantity,
                available_quantity: fulfilledQuantity,
                average_price: averagePrice,
                total_amount: totalAmount,
                orders: ordersToBuy,
                price_changes: priceChanges,
                insufficient: hasInsufficientQuantity,
                first_price: firstPrice || 0,
                first_price_quantity: firstPriceQuantity,
                pending_quantity: remainingQuantity,
                pending_price: pendingPrice || firstPrice || 0,
            };
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
                            // إظهار الخيارين
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
            
            // إظهار ملخص الأسعار والكميات
            if (orders && orders.length > 0) {
                showPriceAlert('info', '@lang("Purchase breakdown")', orders);
            } else if (priceChanges && priceChanges.length > 0) {
                priceChanges.forEach(function(change, index) {
                    if (oldPrice === null || change.from_price !== oldPrice) {
                        showPriceAlert('info', 
                            'Price changed from ' + parseFloat(change.from_price).toFixed(2) + 
                            ' to ' + parseFloat(change.to_price).toFixed(2) + 
                            ' after ' + parseFloat(change.quantity_at_old_price).toFixed(4) + ' units'
                        );
                    }
                });
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
            $('.gold-calculator__top').after(alertHtml);
        }
        
        function hidePriceAlert() {
            $('.price-alert').remove();
        }
        
        function showOptionsModal(priceCalc) {
            const availableQty = priceCalc.available_quantity.toFixed(4);
            const pendingQty = priceCalc.pending_quantity.toFixed(4);
            const firstPrice = priceCalc.first_price.toFixed(2);
            const newPrice = priceCalc.price_changes.length > 0 ? priceCalc.price_changes[0].to_price.toFixed(2) : firstPrice;
            const newTotalAmount = priceCalc.total_amount.toFixed(2);
            
            let modalHtml = `
                <div class="modal fade show" id="buyOptionsModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content" style="background: hsl(var(--section-bg)); border: 1px solid hsl(var(--border-color));">
                            <div class="modal-header" style="border-bottom: 1px solid hsl(var(--border-color));">
                                <h5 class="modal-title text-white">@lang('Insufficient Quantity Available')</h5>
                                <button type="button" class="btn-close btn-close-white" onclick="hideOptionsModal()"></button>
                            </div>
                            <div class="modal-body text-white">
                                <p>@lang('Available quantity at price') <strong>${firstPrice}</strong> {{ $product->currency->code ?? gs('cur_sym') }}: <strong>${availableQty}</strong> {{ $product->unit->symbol ?? 'Unit' }}</p>
                                <p>@lang('Requested quantity'): <strong>${(parseFloat($('#quantity').val()) || (parseFloat($('#amount').val()) / firstPrice)).toFixed(4)}</strong> {{ $product->unit->symbol ?? 'Unit' }}</p>
                                <p class="text-warning">@lang('Pending quantity'): <strong>${pendingQty}</strong> {{ $product->unit->symbol ?? 'Unit' }}</p>
                                
                                <div class="mt-4">
                                    <h6>@lang('Choose an option'):</h6>
                                    
                                    <div class="card mb-3" style="background: rgba(255,255,255,0.05); border: 1px solid hsl(var(--base));">
                                        <div class="card-body">
                                            <h6 class="text-base">@lang('Option 1: Continue with New Price')</h6>
                                            <p class="mb-2">@lang('Complete your purchase with the available quantity at different prices')</p>
                                            <ul class="mb-2">
                                                <li>@lang('Available'): <strong>${availableQty}</strong> {{ $product->unit->symbol ?? 'Unit' }}</li>
                                                <li>@lang('Average Price'): <strong>${priceCalc.average_price.toFixed(2)}</strong> {{ $product->currency->code ?? gs('cur_sym') }}</li>
                                                <li>@lang('Total Amount'): <strong>${newTotalAmount}</strong> {{ $product->currency->code ?? gs('cur_sym') }}</li>
                                            </ul>
                                            <button type="button" class="btn btn--base w-100" onclick="continueWithNewPrice()">
                                                @lang('Continue with New Price')
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="card" style="background: rgba(255,255,255,0.05); border: 1px solid hsl(var(--info));">
                                        <div class="card-body">
                                            <h6 class="text-info">@lang('Option 2: Pending Order')</h6>
                                            <p class="mb-2">@lang('Place a pending order for the remaining quantity at the requested price')</p>
                                            <ul class="mb-2">
                                                <li>@lang('Pending Quantity'): <strong>${pendingQty}</strong> {{ $product->unit->symbol ?? 'Unit' }}</li>
                                                <li>@lang('Requested Price'): <strong>${firstPrice}</strong> {{ $product->currency->code ?? gs('cur_sym') }}</li>
                                                <li class="text-muted"><small>@lang('You will be notified when the quantity becomes available at this price')</small></li>
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
            `;
            
            $('body').append(modalHtml);
            $('#buyOptionsModal').modal('show');
            
            // حفظ بيانات الحساب
            window.currentPriceCalc = priceCalc;
        }
        
        function hideOptionsModal() {
            $('#buyOptionsModal').remove();
            $('.modal-backdrop').remove();
        }
        
        function continueWithNewPrice() {
            // تحديث القيم بناءً على الكمية المتوفرة
            const priceCalc = window.currentPriceCalc;
            if (purchaseType === 'quantity') {
                $('#quantity').val(priceCalc.available_quantity.toFixed(4));
                $('#amount').val(priceCalc.total_amount.toFixed(2));
            } else {
                $('#amount').val(priceCalc.total_amount.toFixed(2));
                $('#quantity').val(priceCalc.available_quantity.toFixed(4));
            }
            
            updatePriceDisplay(priceCalc.average_price, priceCalc.price_changes, priceCalc.orders);
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
                    if (response.success) {
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
                        showPriceAlert('success', response.message || '@lang("Pending order created successfully. You will be notified when the quantity becomes available.")');
                    } else {
                        showPriceAlert('error', response.message || '@lang("Failed to create pending order")');
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || '@lang("An error occurred. Please try again.")';
                    showPriceAlert('error', errorMsg);
                }
            });
        }

        function validateForm() {
            const amount = parseFloat($('#amount').val()) || 0;
            const quantity = parseFloat($('#quantity').val()) || 0;
            let isValid = true;

            if (purchaseType === 'amount') {
                if (amount <= 0) {
                    isValid = false;
                } else {
                    const marketPrice = {{ $marketPrice ?? ($cheapestOrder['sell_price'] ?? 0) }};
                    const calculatedQuantity = amount / marketPrice;
                    if (calculatedQuantity > availableQuantity) {
                        isValid = false;
                    }
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

        $('input[name="purchase_type"]').on('change', function() {
            purchaseType = $(this).attr('id') === 'goldCalculatorSwitch1' ? 'amount' : 'quantity';
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
