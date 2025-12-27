@extends('admin.layouts.app')

@php
    use App\Constants\Status;
@endphp

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('ID')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Location')</th>
                                    <th>@lang('Code')</th>
                                    <th>@lang('Manager Name')</th>
                                    <th>@lang('Mobile')</th>
                                    <th>@lang('Max Capacity (KG)')</th>
                                    <th>@lang('Area (SQM)')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouses as $warehouse)
                                    <tr>
                                        <td>{{ $warehouse->id }}</td>
                                        <td>{{ __($warehouse->name) }}</td>
                                        <td>{{ $warehouse->location }}</td>
                                        <td><span class="badge bg--primary">{{ $warehouse->code }}</span></td>
                                        <td>{{ $warehouse->manager_name }}</td>
                                        <td>{{ $warehouse->mobile }}</td>
                                        <td>{{ $warehouse->max_capacity_kg ? showAmount($warehouse->max_capacity_kg, currencyFormat: false) . ' KG' : '-' }}</td>
                                        <td>{{ $warehouse->area_sqm ? showAmount($warehouse->area_sqm, currencyFormat: false) . ' SQM' : '-' }}</td>
                                        <td>
                                            @php echo $warehouse->statusBadge; @endphp
                                        </td>
                                        <td>{{ showDateTime($warehouse->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.warehouse.statistics', $warehouse->id) }}" class="btn btn-sm btn-outline--info">
                                                    <i class="las la-chart-bar"></i> @lang('Statistics')
                                                </a>
                                                <a href="{{ route('admin.warehouse.edit', $warehouse->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>
                                                @if ($warehouse->status == Status::DISABLE)
                                                    <button type="button" class="btn btn-sm btn-outline--success ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.warehouse.status', $warehouse->id) }}" 
                                                        data-question="@lang('Are you sure to enable this warehouse')?">
                                                        <i class="la la-eye"></i>@lang('Enable')
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.warehouse.status', $warehouse->id) }}" 
                                                        data-question="@lang('Are you sure to disable this warehouse')?">
                                                        <i class="la la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to delete this warehouse?')"
                                                    data-action="{{ route('admin.warehouse.delete', $warehouse->id) }}">
                                                    <i class="las la-trash"></i> @lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($warehouses->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($warehouses) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.warehouse.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i> @lang('Add New')
    </a>
    <x-search-form placeholder="Search by ID / Name / Location / Code / Manager / Mobile" />
@endpush

