@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.product.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Name') <span class="text--danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Unit') <span class="text--danger">*</span></label>
                                    <select name="unit_id" class="form-control select2" required>
                                        <option value="">@lang('Select Unit')</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->name }} ({{ $unit->symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">@lang('This unit will be used for all batches of this product')</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Currency') <span class="text--danger">*</span></label>
                                    <select name="currency_id" class="form-control select2" required>
                                        <option value="">@lang('Select Currency')</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->name }} ({{ $currency->symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">@lang('This currency will be used for all batches of this product')</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('SKU')</label>
                                    <input type="text" class="form-control" value="@lang('Will be generated automatically')" disabled>
                                    <small class="form-text text-muted">@lang('SKU will be generated automatically in format: RO-XXX')</small>
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
                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


