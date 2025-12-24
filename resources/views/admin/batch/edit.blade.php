@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.batch.update', $batch->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Product') <span class="text--danger">*</span></label>
                                    <select name="product_id" class="form-control select2" required>
                                        <option value="">@lang('Select Product')</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('product_id', $batch->product_id) == $product->id ? 'selected' : '' }}
                                                data-unit-id="{{ $product->unit_id }}"
                                                data-unit-symbol="{{ $product->unit->symbol ?? '' }}"
                                                data-currency-id="{{ $product->currency_id }}"
                                                data-currency-symbol="{{ $product->currency->symbol ?? '' }}">
                                                {{ $product->name }} ({{ $product->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Warehouse') <span class="text--danger">*</span></label>
                                    <select name="warehouse_id" class="form-control select2" required>
                                        <option value="">@lang('Select Warehouse')</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $batch->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Units Count') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="units_count" id="units_count" class="form-control" value="{{ old('units_count', $batch->units_count) }}" required style="flex: 1;">
                                        <span class="input-group-text" id="unit_display">{{ $batch->product->unit->symbol ?? 'Unit' }}</span>
                                    </div>
                                    <small class="form-text text-muted">@lang('Unit and currency are set from the selected product')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Batch Code')</label>
                                    <input type="text" class="form-control" value="{{ $batch->batch_code }}" disabled>
                                    <small class="form-text text-muted">@lang('Batch code cannot be modified')</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Quality Grade')</label>
                                    @php
                                        $standardGrades = ['Premium', 'Excellent', 'Very Good', 'Grade 1', 'Grade 2', 'Grade 3', 'Commercial'];
                                        $currentGrade = old('quality_grade', $batch->quality_grade);
                                        $isCustomGrade = $currentGrade && !in_array($currentGrade, $standardGrades);
                                    @endphp
                                    <select name="quality_grade" class="form-control" id="quality_grade_select">
                                        <option value="">@lang('Select Quality Grade')</option>
                                        <option value="Premium" {{ $currentGrade == 'Premium' ? 'selected' : '' }}>@lang('Premium') (90-100)</option>
                                        <option value="Excellent" {{ $currentGrade == 'Excellent' ? 'selected' : '' }}>@lang('Excellent') (85-89.9)</option>
                                        <option value="Very Good" {{ $currentGrade == 'Very Good' ? 'selected' : '' }}>@lang('Very Good') (80-84.9)</option>
                                        <option value="Grade 1" {{ $currentGrade == 'Grade 1' ? 'selected' : '' }}>@lang('Grade 1') / @lang('Grade A')</option>
                                        <option value="Grade 2" {{ $currentGrade == 'Grade 2' ? 'selected' : '' }}>@lang('Grade 2') / @lang('Grade B')</option>
                                        <option value="Grade 3" {{ $currentGrade == 'Grade 3' ? 'selected' : '' }}>@lang('Grade 3') / @lang('Grade C')</option>
                                        <option value="Commercial" {{ $currentGrade == 'Commercial' ? 'selected' : '' }}>@lang('Commercial Grade')</option>
                                        <option value="Other" {{ $isCustomGrade ? 'selected' : '' }}>@lang('Other')</option>
                                    </select>
                                    <input type="text" name="quality_grade_custom" id="quality_grade_custom" class="form-control mt-2" value="{{ $isCustomGrade ? $currentGrade : old('quality_grade_custom') }}" placeholder="@lang('Enter custom quality grade')" style="display: {{ $isCustomGrade ? 'block' : 'none' }};">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Origin Country')</label>
                                    <input type="text" name="origin_country" class="form-control" value="{{ old('origin_country', $batch->origin_country) }}" placeholder="@lang('e.g., Ethiopia, Colombia, etc.')">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Expiration Date')</label>
                                    <input type="date" name="exp_date" class="form-control" value="{{ old('exp_date', $batch->exp_date) }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Buy Price')</label>
                                    <input type="number" step="0.01" name="buy_price" id="buy_price" class="form-control" value="{{ old('buy_price', $batch->buy_price) }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Status') <span class="text--danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="1" {{ old('status', $batch->status) == 1 ? 'selected' : '' }}>@lang('Active')</option>
                                        <option value="0" {{ old('status', $batch->status) == 0 ? 'selected' : '' }}>@lang('Not Active')</option>
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
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="text-muted">@lang('Total Items')</label>
                                                    <div class="h5" id="total_items">0</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-2">
                                                    <label class="text-muted">@lang('Total Buy Price')</label>
                                                    <div class="h5 text--info" id="total_buy_price">0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-3">
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
        
        function updateProductInfo() {
            const productId = $('#product_id').val();
            if (productId) {
                const option = $('#product_id option:selected');
                const unitSymbol = option.data('unit-symbol') || '{{ $batch->product->unit->symbol ?? "Unit" }}';
                
                $('#unit_display').text(unitSymbol);
            } else {
                $('#unit_display').text('{{ $batch->product->unit->symbol ?? "Unit" }}');
            }
        }
        
        function calculateTotal() {
            const unitsCount = parseFloat($('#units_count').val()) || 0;
            const buyPrice = parseFloat($('#buy_price').val()) || 0;
            
            // حساب إجمالي سعر الشراء
            const totalBuyPrice = unitsCount * buyPrice;
            
            // عرض النتائج
            $('#total_items').text(unitsCount.toFixed(2));
            $('#total_buy_price').text(totalBuyPrice.toFixed(2));
        }
        
        // تحديث معلومات المنتج عند تغييره
        $('#product_id').on('change', function() {
            updateProductInfo();
            calculateTotal();
        });
        
        // حساب تلقائي عند تغيير أي حقل
        $('#units_count, #buy_price').on('input', function() {
            calculateTotal();
        });
        
        // تحديث عند تحميل الصفحة
        updateProductInfo();
        
        // حساب عند تحميل الصفحة
        calculateTotal();
        
        // Handle quality grade select change
        $('#quality_grade_select').on('change', function() {
            if ($(this).val() === 'Other') {
                $('#quality_grade_custom').show().focus();
            } else {
                $('#quality_grade_custom').hide().val('');
            }
        });
        
        // Initialize on page load
        if ($('#quality_grade_select').val() === 'Other') {
            $('#quality_grade_custom').show();
        }
        
        // Before form submit, if "Other" is selected, use custom value
        $('form').on('submit', function(e) {
            if ($('#quality_grade_select').val() === 'Other') {
                var customValue = $('#quality_grade_custom').val();
                if (customValue && customValue.trim() !== '') {
                    // Create hidden input with the custom value
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'quality_grade',
                        value: customValue.trim()
                    }).appendTo($(this));
                    // Remove the select from submission
                    $('#quality_grade_select').prop('disabled', true);
                } else {
                    e.preventDefault();
                    alert('@lang("Please enter a custom quality grade")');
                    return false;
                }
            }
        });
        
    })(jQuery);
</script>
@endpush

