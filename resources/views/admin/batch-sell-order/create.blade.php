@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.batch-sell-order.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Batch') <span class="text--danger">*</span></label>
                                    <select name="batch_id" id="batch_id" class="form-control select2" required>
                                        <option value="">@lang('Select Batch')</option>
                                        @foreach($batches as $batch)
                                            @php
                                                // استخدام الكمية المحسوبة من الـ controller (fresh calculation)
                                                $availableQty = $availableQuantities[$batch->id] ?? 0;
                                                $totalQty = $batch->units_count ?? 0;
                                            @endphp
                                            <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}
                                                data-product="{{ $batch->product->name ?? '' }}"
                                                data-warehouse="{{ $batch->warehouse->name ?? '' }}"
                                                data-unit="{{ $batch->product->unit->symbol ?? '' }}"
                                                data-currency="{{ $batch->product->currency->symbol ?? '' }}"
                                                data-units-count="{{ $totalQty }}"
                                                data-total-quantity="{{ $totalQty }}"
                                                data-available-quantity="{{ $availableQty }}">
                                                {{ $batch->batch_code }} - {{ $batch->product->name ?? 'N/A' }} 
                                                (Total: {{ showAmount($totalQty, 4, currencyFormat: false) }} {{ $batch->product->unit->symbol ?? 'Unit' }}, 
                                                Available: {{ showAmount($availableQty, 4, currencyFormat: false) }} {{ $batch->product->unit->symbol ?? 'Unit' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">@lang('Select the batch to create a sell order from')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Quantity to Sell') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.0001" name="quantity" id="quantity" class="form-control" value="{{ old('quantity') }}" required style="flex: 1;" min="0">
                                        <span class="input-group-text" id="unit_display">@lang('Unit')</span>
                                    </div>
                                    <small class="form-text text-muted" id="available_quantity_hint">
                                        <span class="text-info">@lang('Available from batch'): <strong id="available_from_batch">0</strong> <span id="available_unit_display">@lang('Unit')</span></span>
                                    </small>
                                    <small class="form-text text-muted" id="remaining_quantity_hint" style="display: none;">
                                        <span class="text-muted">@lang('Remaining'): <strong id="remaining_quantity">0</strong> <span id="remaining_unit_display">@lang('Unit')</span></span>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Sell Price') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="sell_price" id="sell_price" class="form-control" value="{{ old('sell_price') }}" required style="flex: 1;" min="0">
                                        <span class="input-group-text" id="currency_display">@lang('Currency')</span>
                                        <span class="input-group-text">/</span>
                                        <span class="input-group-text" id="unit_display3">@lang('Unit')</span>
                                    </div>
                                    <small class="form-text text-muted">@lang('Price per unit (item_unit)')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Sell Order Code')</label>
                                    <input type="text" class="form-control" value="@lang('Will be generated automatically')" disabled>
                                    <small class="form-text text-muted">@lang('Sell order code will be generated automatically in format: BSO-XXX')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Status') <span class="text--danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>@lang('Active')</option>
                                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>@lang('Not Active')</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- حساب التوتال -->
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="card bg--light">
                                    <div class="card-body">
                                        <h6 class="mb-3">@lang('Total Calculation')</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-2">
                                                    <label class="text-muted">@lang('Total Value')</label>
                                                    <div class="h5" id="total_value">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    (function($) {
        "use strict";
        
        function updateDisplay() {
            const batchId = $('#batch_id').val();
            if (batchId) {
                const option = $('#batch_id option:selected');
                const unit = option.data('unit') || '@lang("Unit")';
                const currency = option.data('currency') || '@lang("Currency")';
                const totalQuantity = parseFloat(option.data('total-quantity')) || 0;
                const availableQuantity = parseFloat(option.data('available-quantity')) || 0;
                
                $('#unit_display').text(unit);
                $('#unit_display3').text(unit);
                $('#available_unit_display').text(unit);
                $('#remaining_unit_display').text(unit);
                $('#currency_display').text(currency);
                
                // عرض الكمية المتاحة
                $('#available_from_batch').text(availableQuantity.toFixed(4));
                
                // تحديث الحد الأقصى للكمية
                if (availableQuantity > 0) {
                    $('#quantity').attr('max', availableQuantity);
                    $('#available_quantity_hint').html(
                        '<span class="text-info">@lang("Available from batch"): <strong>' + 
                        availableQuantity.toFixed(4) + ' ' + unit + 
                        '</strong></span>'
                    );
                } else {
                    $('#quantity').removeAttr('max');
                    $('#available_quantity_hint').html(
                        '<span class="text-danger">@lang("No available quantity. All quantity is already in sell orders.")</span>'
                    );
                }
                
                // تحديث الكمية المتبقية
                updateRemainingQuantity();
            } else {
                $('#unit_display').text('@lang("Unit")');
                $('#unit_display3').text('@lang("Unit")');
                $('#available_unit_display').text('@lang("Unit")');
                $('#currency_display').text('@lang("Currency")');
                $('#available_from_batch').text('0');
                $('#quantity').removeAttr('max');
                $('#available_quantity_hint').html('');
                $('#remaining_quantity_hint').hide();
            }
        }
        
        function calculateTotal() {
            const quantity = parseFloat($('#quantity').val()) || 0;
            const sellPrice = parseFloat($('#sell_price').val()) || 0;
            const totalValue = quantity * sellPrice;
            
            // تحديث القيمة الإجمالية مع تنسيق أفضل
            if (totalValue > 0) {
                $('#total_value').text(totalValue.toFixed(2)).removeClass('text-muted').addClass('text-success');
            } else {
                $('#total_value').text('0.00').removeClass('text-success').addClass('text-muted');
            }
            
            // حساب الكمية المتبقية
            updateRemainingQuantity();
        }
        
        function updateRemainingQuantity() {
            const batchId = $('#batch_id').val();
            if (batchId) {
                const option = $('#batch_id option:selected');
                const availableQuantity = parseFloat(option.data('available-quantity')) || 0;
                const enteredQuantity = parseFloat($('#quantity').val()) || 0;
                const unit = option.data('unit') || '@lang("Unit")';
                
                if (enteredQuantity > 0 && enteredQuantity <= availableQuantity) {
                    const remaining = availableQuantity - enteredQuantity;
                    $('#remaining_quantity').text(remaining.toFixed(4));
                    $('#remaining_unit_display').text(unit);
                    $('#remaining_quantity_hint').show();
                } else {
                    $('#remaining_quantity_hint').hide();
                }
            } else {
                $('#remaining_quantity_hint').hide();
            }
        }
        
        function validateQuantity() {
            const batchId = $('#batch_id').val();
            if (batchId) {
                const option = $('#batch_id option:selected');
                const availableQuantity = parseFloat(option.data('available-quantity')) || 0;
                const enteredQuantity = parseFloat($('#quantity').val()) || 0;
                const unit = option.data('unit') || '@lang("Unit")';
                
                // إزالة رسائل الخطأ السابقة
                $('#available_quantity_hint .text-danger').remove();
                $('#quantity').removeClass('is-invalid');
                
                if (enteredQuantity > availableQuantity) {
                    $('#quantity').addClass('is-invalid');
                    $('#available_quantity_hint').append(
                        '<div class="text-danger mt-1">@lang("Quantity exceeds available amount. Maximum: ") ' + 
                        availableQuantity.toFixed(4) + ' ' + unit + '</div>'
                    );
                }
            }
        }
        
        $('#batch_id').on('change', function() {
            updateDisplay();
            calculateTotal();
            validateQuantity();
        });
        
        $('#quantity').on('input', function() {
            calculateTotal();
            validateQuantity();
            updateRemainingQuantity();
        });
        
        $('#sell_price').on('input', function() {
            calculateTotal();
        });
        
        // Initialize عند تحميل الصفحة
        $(document).ready(function() {
            updateDisplay();
            calculateTotal();
            validateQuantity();
        });
        
    })(jQuery);
</script>
@endpush

