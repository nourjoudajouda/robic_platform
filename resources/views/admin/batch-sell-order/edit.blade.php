@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.batch-sell-order.update', $sellOrder->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Batch') <span class="text--danger">*</span></label>
                                    <select name="batch_id" id="batch_id" class="form-control select2" required>
                                        <option value="">@lang('Select Batch')</option>
                                        @foreach($batches as $batch)
                                            @php
                                                $availableQty = $availableQuantities[$batch->id] ?? $batch->getAvailableQuantityForSellOrder();
                                            @endphp
                                            <option value="{{ $batch->id }}" {{ old('batch_id', $sellOrder->batch_id) == $batch->id ? 'selected' : '' }}
                                                data-product="{{ $batch->product->name ?? '' }}"
                                                data-warehouse="{{ $batch->warehouse->name ?? '' }}"
                                                data-unit="{{ $batch->product->unit->symbol ?? '' }}"
                                                data-currency="{{ $batch->product->currency->symbol ?? '' }}"
                                                data-units-count="{{ $batch->units_count }}"
                                                data-total-quantity="{{ $batch->units_count }}"
                                                data-available-quantity="{{ $availableQty }}">
                                                {{ $batch->batch_code }} - {{ $batch->product->name ?? 'N/A' }} ({{ showAmount($batch->units_count, 4, currencyFormat: false) }} {{ $batch->product->unit->symbol ?? 'Unit' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">@lang('Select the batch for this sell order')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Quantity to Sell') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.0001" name="quantity" id="quantity" class="form-control" value="{{ old('quantity', $sellOrder->quantity) }}" required style="flex: 1;" min="0">
                                        <span class="input-group-text" id="unit_display">{{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                    </div>
                                    <div class="mt-2 p-2 border rounded bg-light">
                                        <small class="d-block mb-1">
                                            <span class="text-primary">@lang('Initial Quantity'): <strong>{{ showAmount($sellOrder->quantity, 4, currencyFormat: false) }}</strong> {{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                        </small>
                                        <small class="d-block mb-1">
                                            <span class="text-danger">@lang('Sold (Used) Quantity'): <strong>{{ showAmount($sellOrder->quantity - ($sellOrder->available_quantity ?? $sellOrder->quantity), 4, currencyFormat: false) }}</strong> {{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                        </small>
                                        <small class="d-block">
                                            <span class="text-success">@lang('Remaining (Unsold) Quantity'): <strong>{{ showAmount($sellOrder->available_quantity ?? $sellOrder->quantity, 4, currencyFormat: false) }}</strong> {{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                        </small>
                                    </div>
                                    @php
                                        $currentBatch = $batches->firstWhere('id', $sellOrder->batch_id);
                                        if ($currentBatch) {
                                            $batchAvailable = $availableQuantities[$currentBatch->id] ?? 0;
                                            $batchTotal = $currentBatch->units_count ?? 0;
                                            $batchUsed = $batchTotal - $batchAvailable;
                                        }
                                    @endphp
                                    @if(isset($currentBatch))
                                    <div class="mt-2 p-2 border rounded" style="background-color: #f8f9fa;">
                                        <strong class="d-block mb-2 text-info">@lang('Batch Information'):</strong>
                                        <small class="d-block mb-1">
                                            <span class="text-muted">@lang('Total batch quantity'): <strong>{{ showAmount($batchTotal, 4, currencyFormat: false) }}</strong> {{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                        </small>
                                        <small class="d-block mb-1">
                                            <span class="text-danger">@lang('Already in all sell orders'): <strong>{{ showAmount($batchUsed, 4, currencyFormat: false) }}</strong> {{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                        </small>
                                        <small class="d-block">
                                            <span class="text-success">@lang('Available in batch now'): <strong>{{ showAmount($batchAvailable, 4, currencyFormat: false) }}</strong> {{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                        </small>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Sell Price') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="sell_price" id="sell_price" class="form-control" value="{{ old('sell_price', $sellOrder->sell_price) }}" required style="flex: 1;" min="0">
                                        <span class="input-group-text" id="currency_display">{{ $sellOrder->currency->symbol ?? 'Currency' }}</span>
                                        <span class="input-group-text">/</span>
                                        <span class="input-group-text" id="unit_display3">{{ $sellOrder->unit->symbol ?? 'Unit' }}</span>
                                    </div>
                                    <small class="form-text text-muted">@lang('Price per unit (item_unit)')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Sell Order Code')</label>
                                    <input type="text" class="form-control" value="{{ $sellOrder->sell_order_code }}" disabled>
                                    <small class="form-text text-muted">@lang('Sell order code cannot be modified')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Status') <span class="text--danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="1" {{ old('status', $sellOrder->status) == 1 ? 'selected' : '' }}>@lang('Active')</option>
                                        <option value="0" {{ old('status', $sellOrder->status) == 0 ? 'selected' : '' }}>@lang('Not Active')</option>
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
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Update')</button>
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
                const itemUnit = option.data('unit') || '{{ $sellOrder->unit->symbol ?? "Unit" }}';
                const currency = option.data('currency') || '{{ $sellOrder->currency->symbol ?? "Currency" }}';
                
                $('#item_unit_display').text(itemUnit);
                $('#item_unit_display3').text(itemUnit);
                $('#available_unit_display').text(itemUnit);
                $('#currency_display').text(currency);
                
                // عرض الكمية المتاحة
                const availableQuantity = parseFloat(option.data('available-quantity')) || 0;
                if (availableQuantity > 0) {
                    $('#available_quantity_hint').html(
                        '<span class="text-info">@lang("Available from batch"): <strong>' + 
                        availableQuantity.toFixed(4) + ' ' + itemUnit + 
                        '</strong></span>'
                    );
                } else {
                    $('#available_quantity_hint').html(
                        '<span class="text-danger">@lang("No available quantity. All quantity is already in sell orders.")</span>'
                    );
                }
            } else {
                $('#available_quantity_hint').html('');
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
        }
        
        $('#batch_id').on('change', function() {
            updateDisplay();
            calculateTotal();
        });
        
        $('#quantity').on('input', function() {
            calculateTotal();
        });
        
        $('#sell_price').on('input', function() {
            calculateTotal();
        });
        
        // Initialize عند تحميل الصفحة
        $(document).ready(function() {
            updateDisplay();
            calculateTotal();
        });
        
    })(jQuery);
</script>
@endpush

