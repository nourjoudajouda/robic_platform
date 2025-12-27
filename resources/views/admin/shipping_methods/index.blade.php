@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('ID')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Cost Per KG')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shippingMethods as $method)
                                    <tr>
                                        <td>{{ $method->id }}</td>
                                        <td><strong>{{ $method->name }}</strong></td>
                                        <td>
                                            <span class="text--primary fw-bold">
                                                {{ showAmount($method->cost_per_kg, currencyFormat: false) }} 
                                                {{ gs('cur_text') }}/kg
                                            </span>
                                        </td>
                                        <td>
                                            @if($method->status == 1)
                                                <span class="badge badge--success">@lang('Active')</span>
                                            @else
                                                <span class="badge badge--danger">@lang('Inactive')</span>
                                            @endif
                                        </td>
                                        <td>{{ showDateTime($method->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.shipping-methods.edit', $method->id) }}" 
                                                   class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>
                                                @if ($method->status == 0)
                                                    <button type="button" class="btn btn-sm btn-outline--success ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.shipping-methods.status', $method->id) }}" 
                                                        data-question="@lang('Are you sure to activate this shipping method')?">
                                                        <i class="la la-check"></i>@lang('Activate')
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline--warning ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.shipping-methods.status', $method->id) }}" 
                                                        data-question="@lang('Are you sure to deactivate this shipping method')?">
                                                        <i class="la la-times"></i>@lang('Deactivate')
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to delete this shipping method?')"
                                                    data-action="{{ route('admin.shipping-methods.delete', $method->id) }}">
                                                    <i class="las la-trash"></i> @lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No shipping methods found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($shippingMethods->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($shippingMethods) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.shipping-methods.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus"></i>@lang('Add New')
    </a>
@endpush

