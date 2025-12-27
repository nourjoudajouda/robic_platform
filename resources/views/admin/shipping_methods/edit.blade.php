@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body">
                    <form action="{{ route('admin.shipping-methods.update', $shippingMethod->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Shipping Method Name') <span class="text--danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $shippingMethod->name) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Cost Per KG') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" step="0.00000001" name="cost_per_kg" class="form-control" value="{{ old('cost_per_kg', $shippingMethod->cost_per_kg) }}" required>
                                        <span class="input-group-text">{{ gs('cur_text') }}/kg</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Status')</label>
                                    <div class="form-check form-switch form-check-primary">
                                        <input class="form-check-input" type="checkbox" name="status" id="status" {{ old('status', $shippingMethod->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">@lang('Active')</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Update')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.shipping-methods.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="la la-undo"></i> @lang('Back')
    </a>
@endpush

