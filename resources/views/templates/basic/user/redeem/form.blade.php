@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card">
                <h4>@lang('Available Products for Shipping')</h4>
                <div class="dashboard-card-wrapper two">
                    @foreach ($assets as $asset)
                        <div class="dashboard-card two assetCard {{ $loop->first ? 'active' : '' }}" 
                             data-product-id="{{ $asset->product_id }}" 
                             data-price="{{ $asset->price }}"
                             data-warehouse-lat="{{ $asset->warehouse->latitude ?? '' }}"
                             data-warehouse-lng="{{ $asset->warehouse->longitude ?? '' }}"
                             data-warehouse-name="{{ $asset->warehouse->name ?? 'N/A' }}">
                            <div class="dashboard-card__tag">
                                <span class="dashboard-card__tag-icon"><img src="{{ asset($activeTemplateTrue . 'images/icons/29.png') }}" alt="image"></span>
                                <span class="dashboard-card__tag-text">{{ $asset->product ? __($asset->product->name) : 'N/A' }}</span>
                            </div>
                            <h4 class="dashboard-card__bean">{{ showAmount($asset->quantity, 4, currencyFormat: false) }} <sub>{{ $asset->unit ? $asset->unit->symbol : 'Unit' }}</sub></h4>
                            <p class="dashboard-card__desc">{{ showAmount($asset->quantity * $asset->price) }} {{ gs('cur_text') }}</p>
                        </div>
                    @endforeach
                </div>
                <form action="{{ route('user.redeem.store') }}" method="POST" class="withdraw-form mt-4" id="redeemForm">
                    @csrf
                    <input type="hidden" name="product_id" id="selectedProductId" value="{{ $assets->count() ? $assets->first()->product_id : '0' }}">
                    <input type="hidden" name="asset_ids" id="selectedAssetIds" value="{{ $assets->count() ? json_encode($assets->first()->asset_ids) : '[]' }}">
                    <input type="hidden" name="shipping_lat" id="shippingLat">
                    <input type="hidden" name="shipping_lng" id="shippingLng">
                    <input type="hidden" name="shipping_method_id" id="shippingMethodId">
                    <input type="hidden" name="shipping_cost" id="shippingCost">
                    <input type="hidden" name="distance" id="distance">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="quantity" class="form--label">@lang('Quantity') <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.0001" name="quantity" id="quantity" class="form-control" value="0" required min="0" max="{{ $assets->count() ? $assets->first()->quantity : 0 }}">
                                    <span class="input-group-text" id="unitSymbol">{{ $assets->count() && $assets->first()->unit ? $assets->first()->unit->symbol : 'Unit' }}</span>
                                </div>
                                <small class="text-muted">@lang('Available'): <span id="availableQuantity">{{ $assets->count() ? showAmount($assets->first()->quantity, 4, currencyFormat: false) : 0 }}</span></small>
                                        </div>
                                    </div>

                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form--label">@lang('Delivery Type') <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-radio-card">
                                            <input type="radio" name="delivery_type" id="deliveryPickup" value="pickup" checked>
                                            <label for="deliveryPickup" class="custom-radio-label">
                                                <i class="fas fa-store fa-2x mb-2"></i>
                                                <h6>@lang('Pickup')</h6>
                                                <small>@lang('Collect from warehouse')</small>
                                            </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                                        <div class="custom-radio-card">
                                            <input type="radio" name="delivery_type" id="deliveryShipping" value="shipping">
                                            <label for="deliveryShipping" class="custom-radio-label">
                                                <i class="fas fa-truck fa-2x mb-2"></i>
                                                <h6>@lang('Shipping')</h6>
                                                <small>@lang('Deliver to your location')</small>
                                            </label>
                                        </div>
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <div class="col-12" id="shippingDetailsSection" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>@lang('Shipping Details'):</strong>
                                <span id="shippingInfo">@lang('Click "Set Shipping Details" to configure shipping')</span>
                            </div>
                            <button type="button" class="btn btn--base btn-sm mb-3" id="openShippingModal">
                                <i class="fas fa-map-marker-alt"></i> @lang('Set Shipping Details')
                            </button>
                        </div>

                        <div class="col-12">
                            <div class="form-group mb-3">
                                <p class="withdraw-form__desc"> 
                                    <span id="costLabel">@lang('Pickup Fee')</span>: <span class="totalAmount fw-bold">0.00</span> {{ __(gs('cur_text')) }}
                                    <span id="shippingCostDisplay" style="display: none;">
                                        <br><small class="text-muted">(@lang('Distance') × @lang('Rate')/km = <span class="shippingCostText">0.00</span> {{ __(gs('cur_text')) }})</small>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="alert alert-warning" id="balanceWarning" style="color: #333;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong style="color: #000;">@lang('Important'):</strong>
                                <p class="mb-0" style="color: #333;">
                                    @lang('The shipping cost will be deducted from your account balance.')
                                    <br>
                                    <small style="color: #555;">@lang('Your current balance'): <strong style="color: #000;">{{ showAmount(auth()->user()->balance) }}</strong></small>
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

    <!-- Shipping Modal -->
    <div class="modal fade" id="shippingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="background: hsl(var(--section-bg));">
                <div class="modal-header">
                    <h5 class="modal-title text-white">@lang('Shipping Configuration')</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-warning" id="shippingInstructions">
                                <i class="fas fa-info-circle"></i> 
                                <strong>@lang('Required Steps'):</strong>
                                <ol class="mb-0 mt-2">
                                    <li id="step1" style="opacity: 0.5;">@lang('Click on map to select delivery location') ❌</li>
                                    <li id="step2" style="opacity: 0.5;">@lang('Select shipping method') ❌</li>
                                </ol>
                            </div>
                            <label class="form--label">@lang('Delivery Location') <span class="text-danger">*</span></label>
                            <small class="d-block text-muted mb-2">@lang('Click on the map to set your delivery location')</small>
                            <div id="map" style="height: 400px; border-radius: 8px;"></div>
                            <div class="mt-2">
                                <small class="text-info">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    @lang('Warehouse'): <span id="warehouseInfo">-</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form--label">@lang('Latitude')</label>
                                <input type="text" class="form-control" id="modalLat" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form--label">@lang('Longitude')</label>
                                <input type="text" class="form-control" id="modalLng" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form--label">@lang('Shipping Method') <span class="text-danger">*</span></label>
                                <select class="form-control" id="modalShippingMethod" required>
                                    <option value="">@lang('Select Shipping Method')</option>
                                    @foreach($shippingMethods as $method)
                                        <option value="{{ $method->id }}" data-cost="{{ $method->cost_per_kg }}">
                                            {{ $method->name }} ({{ showAmount($method->cost_per_kg, currencyFormat: false) }} {{ gs('cur_text') }}/kg)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-success">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>@lang('Distance'):</strong> <span id="modalDistance">-</span> @lang('km')
                                    </div>
                                    <div class="col-md-6">
                                        <strong>@lang('Shipping Cost'):</strong> <span id="modalShippingCost">-</span> {{ gs('cur_text') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="button" class="btn btn--base" id="confirmShipping">@lang('Confirm')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/40.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.redeem.history') }}" class="btn btn--base btn--lg"> <i class="fa fa-history"></i> @lang('Shipping History')</a>
@endpush

@push('style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .custom-radio-card {
        border: 2px solid hsl(var(--border-color));
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }
    .custom-radio-card input[type="radio"] {
        display: none;
    }
    .custom-radio-card input[type="radio"]:checked + .custom-radio-label {
        border-color: hsl(var(--base));
        background: hsla(var(--base), 0.1);
    }
    .custom-radio-label {
        cursor: pointer;
        display: block;
        padding: 10px;
        border-radius: 6px;
        transition: all 0.3s;
    }
    .custom-radio-card:hover {
        border-color: hsl(var(--base));
    }
    .assetCard {
        cursor: pointer;
    }
    .assetCard.active {
        border: 2px solid hsl(var(--base));
    }
</style>
@endpush

@push('script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function($) {
            "use strict";
        
        let selectedProduct = null;
        let map = null;
        let marker = null;
        let warehouseMarker = null;
        let warehouseLat = null;
        let warehouseLng = null;
        let selectedLat = null;
        let selectedLng = null;
        let selectedShippingMethod = null;
        let costPerKg = 0;
        
        // Initialize first product
        if ($('.assetCard').length > 0) {
            let firstCard = $('.assetCard').first();
            selectedProduct = {
                product_id: firstCard.data('product-id'),
                price: firstCard.data('price'),
                quantity: parseFloat($('#availableQuantity').text()),
                warehouseLat: firstCard.data('warehouse-lat'),
                warehouseLng: firstCard.data('warehouse-lng'),
                warehouseName: firstCard.data('warehouse-name'),
            };
            warehouseLat = selectedProduct.warehouseLat;
            warehouseLng = selectedProduct.warehouseLng;
            
            console.log('=== Warehouse Data Check ===');
            console.log('Warehouse Name:', selectedProduct.warehouseName);
            console.log('Warehouse Lat (from data attr):', firstCard.data('warehouse-lat'));
            console.log('Warehouse Lng (from data attr):', firstCard.data('warehouse-lng'));
            console.log('warehouseLat variable:', warehouseLat);
            console.log('warehouseLng variable:', warehouseLng);
            
            if (!warehouseLat || !warehouseLng) {
                console.error('⚠️ WAREHOUSE COORDINATES NOT SET IN DATABASE!');
                console.error('Admin must add latitude & longitude to the warehouse in Admin Panel → Warehouses → Edit');
            }
        }
        
        // Product card click
            $(document).on('click', '.assetCard', function() {
                $('.assetCard').removeClass('active');
                $(this).addClass('active');
            
            selectedProduct = {
                product_id: $(this).data('product-id'),
                price: $(this).data('price'),
                quantity: parseFloat($(this).find('.dashboard-card__bean').text().trim().split(' ')[0]),
                warehouseLat: $(this).data('warehouse-lat'),
                warehouseLng: $(this).data('warehouse-lng'),
                warehouseName: $(this).data('warehouse-name'),
            };
            
            warehouseLat = selectedProduct.warehouseLat;
            warehouseLng = selectedProduct.warehouseLng;
            
            $('#selectedProductId').val(selectedProduct.product_id);
            $('#availableQuantity').text(selectedProduct.quantity);
            $('#quantity').attr('max', selectedProduct.quantity);
            updateTotal();
        });
        
        // Delivery type change
        $('input[name="delivery_type"]').on('change', function() {
            if ($(this).val() === 'shipping') {
                $('#shippingDetailsSection').show();
                $('#costLabel').text('@lang("Shipping Cost")');
            } else {
                $('#shippingDetailsSection').hide();
                resetShippingData();
                $('#costLabel').text('@lang("Pickup Fee")');
            }
            updateTotal();
        });
        
        // Pickup fee from charge limit
        const pickupFee = {{ $chargeLimit->pickup_fee ?? 0 }};
        
        // Quantity change
        $('#quantity').on('input', function() {
            updateTotal();
        });
        
        // Open shipping modal
        $('#openShippingModal').on('click', function() {
            $('#shippingModal').modal('show');
            setTimeout(initMap, 500);
        });
        
        // Initialize map
        function initMap() {
            if (map) {
                map.remove();
            }
            
            let centerLat = warehouseLat || 24.7136;
            let centerLng = warehouseLng || 46.6753;
            
            map = L.map('map').setView([centerLat, centerLng], 10);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);
            
            // Warehouse marker
            if (warehouseLat && warehouseLng) {
                warehouseMarker = L.marker([warehouseLat, warehouseLng], {
                    icon: L.icon({
                        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                        iconSize: [25, 41],
                        iconAnchor: [12, 41],
                        popupAnchor: [1, -34],
                        shadowSize: [41, 41]
                    })
                }).addTo(map);
                warehouseMarker.bindPopup('<b>Warehouse</b><br>' + selectedProduct.warehouseName);
                $('#warehouseInfo').text(selectedProduct.warehouseName + ' (' + warehouseLat + ', ' + warehouseLng + ')');
            }
            
            // Click to set delivery location
            map.on('click', function(e) {
                selectedLat = e.latlng.lat;
                selectedLng = e.latlng.lng;
                
                if (marker) {
                    map.removeLayer(marker);
                }
                
                marker = L.marker([selectedLat, selectedLng]).addTo(map);
                marker.bindPopup('<b>Delivery Location</b>').openPopup();
                
                $('#modalLat').val(selectedLat.toFixed(6));
                $('#modalLng').val(selectedLng.toFixed(6));
                
                // Update step 1
                $('#step1').css('opacity', '1').html('@lang("Click on map to select delivery location") ✅');
                
                calculateDistance();
            });
        }
        
        // Shipping method change
        $('#modalShippingMethod').on('change', function() {
            selectedShippingMethod = $(this).val();
            costPerKg = parseFloat($(this).find(':selected').data('cost')) || 0;
            console.log('Shipping method selected:', selectedShippingMethod, 'Cost/kg:', costPerKg);
            
            // Update step 2
            if (selectedShippingMethod) {
                $('#step2').css('opacity', '1').html('@lang("Select shipping method") ✅');
            }
            
            calculateDistance();
        });
        
        // Calculate distance and cost
        function calculateDistance() {
            console.log('Calculate Distance called');
            console.log('Warehouse:', warehouseLat, warehouseLng);
            console.log('Selected Location:', selectedLat, selectedLng);
            console.log('Shipping Method:', selectedShippingMethod);
            
            // تحقق من المستودع
            if (!warehouseLat || !warehouseLng || warehouseLat === '' || warehouseLng === '') {
                $('#modalDistance').html('<span class="text-danger">N/A</span>');
                $('#modalShippingCost').html('<span class="text-danger">N/A</span>');
                console.error('❌ Warehouse location not set!');
                console.error('⚠️ Admin must add Latitude & Longitude to warehouse in Admin Panel');
                console.error('Current values - Lat:', warehouseLat, 'Lng:', warehouseLng);
                
                // Show alert to user
                if (!window.warehouseAlertShown) {
                    alert('⚠️ Warehouse coordinates not configured!\n\nPlease contact admin to add Latitude & Longitude for the warehouse.\n\nGo to: Admin Panel → Warehouses → Edit Warehouse');
                    window.warehouseAlertShown = true;
                }
                return;
            }
            
            // تحقق من موقع المستخدم
            if (!selectedLat || !selectedLng) {
                $('#modalDistance').text('-');
                $('#modalShippingCost').text('-');
                console.warn('Please click on map to select delivery location');
                return;
            }
            
            // تحقق من وسيلة الشحن
            if (!selectedShippingMethod || !costPerKg) {
                $('#modalDistance').text('-');
                $('#modalShippingCost').text('-');
                console.warn('Please select a shipping method');
                return;
            }
            
            // Haversine formula
            let R = 6371; // Earth radius in km
            let dLat = (selectedLat - warehouseLat) * Math.PI / 180;
            let dLng = (selectedLng - warehouseLng) * Math.PI / 180;
            let a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                    Math.cos(warehouseLat * Math.PI / 180) * Math.cos(selectedLat * Math.PI / 180) *
                    Math.sin(dLng/2) * Math.sin(dLng/2);
            let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            let distance = R * c;
            
            // حساب تكلفة الشحن: المسافة × تكلفة الكيلو (بدون ضرب الكمية!)
            let shippingCost = distance * costPerKg;
            
            console.log('Distance:', distance, 'km');
            console.log('Cost Per KM:', costPerKg, 'SAR/km');
            console.log('Shipping Cost:', shippingCost, 'SAR');
            
            $('#modalDistance').text(distance.toFixed(2));
            $('#modalShippingCost').text(shippingCost.toFixed(2));
        }
        
        // Confirm shipping
        $('#confirmShipping').on('click', function() {
            if (!selectedLat || !selectedLng) {
                alert('@lang("Please select a delivery location on the map")');
                return;
            }
            if (!selectedShippingMethod) {
                alert('@lang("Please select a shipping method")');
                return;
            }
            
            let distance = $('#modalDistance').text();
            let shippingCost = $('#modalShippingCost').text();
            let methodName = $('#modalShippingMethod option:selected').text();
            
            $('#shippingLat').val(selectedLat);
            $('#shippingLng').val(selectedLng);
            $('#shippingMethodId').val(selectedShippingMethod);
            $('#shippingCost').val(shippingCost);
            $('#distance').val(distance);
            
            $('#shippingInfo').html(
                '<strong>' + methodName + '</strong><br>' +
                'Distance: ' + distance + ' km | Cost: ' + shippingCost + ' {{ gs("cur_text") }}'
            );
            
            $('#shippingModal').modal('hide');
            updateTotal();
        });
        
        // Update total
        function updateTotal() {
            if (!selectedProduct) {
                console.warn('No product selected');
                return;
            }
            
            let quantity = parseFloat($('#quantity').val()) || 0;
            let productCost = 0; // المستخدم لا يشتري - هو يستلم من مخزونه!
            let shippingCost = 0;
            let userBalance = {{ auth()->user()->balance }};
            
            console.log('Update Total Called:');
            console.log('- Quantity:', quantity);
            console.log('- Product Cost:', productCost, '(من مخزونك - بدون تكلفة)');
            
            if ($('#deliveryShipping').is(':checked') && $('#shippingCost').val()) {
                shippingCost = parseFloat($('#shippingCost').val()) || 0;
                $('#shippingCostDisplay').show();
                $('.productCost').text('0.00');
                $('.shippingCostText').text(shippingCost.toFixed(2));
            } else if ($('#deliveryPickup').is(':checked')) {
                // استخدام رسوم الاستلام من الإعدادات
                shippingCost = pickupFee;
                $('#shippingCostDisplay').hide();
                
                // تحديث التنبيه بناءً على الرصيد
                if (shippingCost > userBalance) {
                    $('#balanceWarning')
                        .removeClass('alert-warning')
                        .addClass('alert-danger')
                        .css('color', '#721c24')
                        .html('<i class="fas fa-exclamation-circle"></i> <strong style="color: #000;">@lang("Insufficient Balance")!</strong><p class="mb-0" style="color: #721c24;">@lang("Your balance") (<strong style="color: #000;">' + userBalance.toFixed(2) + ' {{ gs("cur_text") }}</strong>) @lang("is not enough for shipping cost") (<strong style="color: #000;">' + shippingCost.toFixed(2) + ' {{ gs("cur_text") }}</strong>).</p>');
                } else {
                    $('#balanceWarning')
                        .removeClass('alert-danger')
                        .addClass('alert-warning')
                        .css('color', '#333')
                        .html('<i class="fas fa-exclamation-triangle"></i> <strong style="color: #000;">@lang("Important"):</strong><p class="mb-0" style="color: #333;">@lang("The shipping cost of") <strong style="color: #000;">' + shippingCost.toFixed(2) + ' {{ gs("cur_text") }}</strong> @lang("will be deducted from your account balance")<br><small style="color: #555;">@lang("Your current balance"): <strong style="color: #000;">' + userBalance.toFixed(2) + ' {{ gs("cur_text") }}</strong></small></p>');
                }
            } else if ($('#deliveryPickup').is(':checked')) {
                // استخدام رسوم الاستلام من الإعدادات
                shippingCost = pickupFee;
                $('#shippingCostDisplay').hide();
                
                // تحديث التنبيه بناءً على الرصيد
                if (shippingCost > userBalance) {
                    $('#balanceWarning')
                        .removeClass('alert-warning')
                        .addClass('alert-danger')
                        .css('color', '#721c24')
                        .html('<i class="fas fa-exclamation-circle"></i> <strong style="color: #000;">@lang("Insufficient Balance")!</strong><p class="mb-0" style="color: #721c24;">@lang("Your balance") (<strong style="color: #000;">' + userBalance.toFixed(2) + ' {{ gs("cur_text") }}</strong>) @lang("is not enough for pickup fee") (<strong style="color: #000;">' + shippingCost.toFixed(2) + ' {{ gs("cur_text") }}</strong>).</p>');
                } else {
                    $('#balanceWarning')
                        .removeClass('alert-danger')
                        .addClass('alert-warning')
                        .css('color', '#333')
                        .html('<i class="fas fa-exclamation-triangle"></i> <strong style="color: #000;">@lang("Important"):</strong><p class="mb-0" style="color: #333;">@lang("The pickup fee of") <strong style="color: #000;">' + shippingCost.toFixed(2) + ' {{ gs("cur_text") }}</strong> @lang("will be deducted from your account balance")<br><small style="color: #555;">@lang("Your current balance"): <strong style="color: #000;">' + userBalance.toFixed(2) + ' {{ gs("cur_text") }}</strong></small></p>');
                }
            } else {
                $('#shippingCostDisplay').hide();
                $('#balanceWarning')
                    .removeClass('alert-danger')
                    .addClass('alert-warning')
                    .css('color', '#333')
                    .html('<i class="fas fa-exclamation-triangle"></i> <strong style="color: #000;">@lang("Important"):</strong><p class="mb-0" style="color: #333;">@lang("The shipping cost will be deducted from your account balance.")<br><small style="color: #555;">@lang("Your current balance"): <strong style="color: #000;">{{ showAmount(auth()->user()->balance) }}</strong></small></p>');
            }
            
            // Total = Shipping Cost أو Pickup Fee فقط (لأن المنتج من مخزون المستخدم)
            let total = shippingCost;
            $('.totalAmount').text(total.toFixed(2));
        }
        
        // تحديث عند تحميل الصفحة
        updateTotal();
        
        // Reset shipping data
        function resetShippingData() {
            selectedLat = null;
            selectedLng = null;
            selectedShippingMethod = null;
            $('#shippingLat').val('');
            $('#shippingLng').val('');
            $('#shippingMethodId').val('');
            $('#shippingCost').val('');
            $('#distance').val('');
            $('#shippingInfo').text('@lang("Click \"Set Shipping Details\" to configure shipping")');
        }
        
        // Form validation
        $('#redeemForm').on('submit', function(e) {
            if ($('#deliveryShipping').is(':checked')) {
                if (!$('#shippingLat').val() || !$('#shippingMethodId').val()) {
                    e.preventDefault();
                    alert('@lang("Please configure shipping details before continuing")');
                    return false;
                }
            }
        });

        })(jQuery);
    </script>
@endpush
