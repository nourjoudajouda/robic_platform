@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.warehouse.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Name') <span class="text--danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Location') <span class="text--danger">*</span></label>
                                    <input type="text" name="location" class="form-control" value="{{ old('location') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Code')</label>
                                    <input type="text" class="form-control" value="@lang('Will be generated automatically')" disabled>
                                    <small class="form-text text-muted">@lang('Code will be generated automatically in format: WH-XXX')</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Manager Name') <span class="text--danger">*</span></label>
                                    <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Mobile') <span class="text--danger">*</span></label>
                                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Max Capacity Unit')</label>
                                    <input type="text" name="max_capacity_unit" class="form-control" value="{{ old('max_capacity_unit') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Max Capacity (KG)')</label>
                                    <input type="number" step="0.01" name="max_capacity_kg" class="form-control" value="{{ old('max_capacity_kg') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Area (SQM)')</label>
                                    <input type="number" step="0.01" name="area_sqm" class="form-control" value="{{ old('area_sqm') }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Address')</label>
                                    <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
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

