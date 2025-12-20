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
                                    <th>@lang('Sell Order Code')</th>
                                    <th>@lang('Batch Code')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Warehouse')</th>
                                    <th>@lang('Initial Quantity')</th>
                                    <th>@lang('Remaining Quantity')</th>
                                    <th>@lang('Sell Price')</th>
                                    <th>@lang('Total Value')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sellOrders as $sellOrder)
                                    <tr>
                                        <td>{{ $sellOrder->id }}</td>
                                        <td><span class="badge bg--primary">{{ $sellOrder->sell_order_code }}</span></td>
                                        <td>{{ $sellOrder->batch->batch_code ?? '-' }}</td>
                                        <td>{{ $sellOrder->product->name ?? $sellOrder->batch->product->name ?? '-' }}</td>
                                        <td>{{ $sellOrder->warehouse->name ?? $sellOrder->batch->warehouse->name ?? '-' }}</td>
                                        <td>{{ showAmount($sellOrder->quantity, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}</td>
                                        <td>
                                            <span class="badge {{ ($sellOrder->available_quantity ?? $sellOrder->quantity) > 0 ? 'bg--success' : 'bg--danger' }}">
                                                {{ showAmount($sellOrder->available_quantity ?? $sellOrder->quantity, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td>{{ showAmount($sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }} / {{ $sellOrder->unit->symbol ?? 'Unit' }}</td>
                                        <td>{{ showAmount(($sellOrder->available_quantity ?? $sellOrder->quantity) * $sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }}</td>
                                        <td>
                                            @php echo $sellOrder->statusBadge; @endphp
                                        </td>
                                        <td>{{ showDateTime($sellOrder->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.batch-sell-order.edit', $sellOrder->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>
                                                @if ($sellOrder->status == Status::DISABLE)
                                                    <button type="button" class="btn btn-sm btn-outline--success ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.batch-sell-order.status', $sellOrder->id) }}" 
                                                        data-question="@lang('Are you sure to enable this sell order')?">
                                                        <i class="la la-eye"></i>@lang('Enable')
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.batch-sell-order.status', $sellOrder->id) }}" 
                                                        data-question="@lang('Are you sure to disable this sell order')?">
                                                        <i class="la la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to delete this sell order?')"
                                                    data-action="{{ route('admin.batch-sell-order.delete', $sellOrder->id) }}">
                                                    <i class="las la-trash"></i> @lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No data found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($sellOrders->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($sellOrders) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.batch-sell-order.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i> @lang('Add New')
    </a>
    <x-search-form placeholder="Search by ID / Code / Product" />
@endpush

