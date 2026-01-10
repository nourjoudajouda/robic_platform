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
                                    <th>@lang('User')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Batch Code')</th>
                                    <th>@lang('Warehouse')</th>
                                    <th>@lang('Initial Quantity')</th>
                                    <th>@lang('Available Quantity')</th>
                                    <th>@lang('Sold Quantity')</th>
                                    <th>@lang('Buy Price')</th>
                                    <th>@lang('Sell Price')</th>
                                    <th>@lang('Total Value')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sellOrders as $sellOrder)
                                    @php
                                        $soldQuantity = ($sellOrder->quantity ?? 0) - ($sellOrder->available_quantity ?? $sellOrder->quantity ?? 0);
                                        $availableQty = $sellOrder->available_quantity ?? $sellOrder->quantity ?? 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $sellOrder->id }}</td>
                                        <td><span class="badge bg--primary">{{ $sellOrder->sell_order_code }}</span></td>
                                        <td>
                                            @if($sellOrder->user)
                                                <a href="{{ route('admin.users.detail', $sellOrder->user_id) }}" class="text--base">
                                                    {{ $sellOrder->user->username ?? 'N/A' }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ $sellOrder->product->name ?? ($sellOrder->batch ? $sellOrder->batch->product->name : 'N/A') }}</td>
                                        <td>
                                            @if($sellOrder->batch)
                                                <span class="badge badge--info">{{ $sellOrder->batch->batch_code }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $sellOrder->warehouse->name ?? ($sellOrder->batch ? $sellOrder->batch->warehouse->name : '-') }}</td>
                                        <td>{{ showAmount($sellOrder->quantity, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}</td>
                                        <td>
                                            <span class="badge {{ $availableQty > 0 ? 'bg--success' : 'bg--danger' }}">
                                                {{ showAmount($availableQty, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg--info">
                                                {{ showAmount($soldQuantity, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td>{{ showAmount($sellOrder->buy_price ?? 0, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }}</td>
                                        <td>{{ showAmount($sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }} / {{ $sellOrder->unit->symbol ?? 'Unit' }}</td>
                                        <td>{{ showAmount($availableQty * $sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }}</td>
                                        <td>
                                            @php echo $sellOrder->statusBadge; @endphp
                                        </td>
                                        <td>{{ showDateTime($sellOrder->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.user-sell-order.show', $sellOrder->id) }}" class="btn btn-sm btn-outline--info">
                                                    <i class="la la-eye"></i> @lang('View')
                                                </a>
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
    <x-search-form placeholder="Search by ID / Code / User / Product" />
    
    <div class="d-flex gap-2">
        <select name="status" class="form-control form--control" style="width: auto;" onchange="this.form.submit()">
            <option value="">@lang('All Status')</option>
            <option value="{{ Status::SELL_ORDER_ACTIVE }}" {{ request('status') == Status::SELL_ORDER_ACTIVE ? 'selected' : '' }}>@lang('Active')</option>
            <option value="{{ Status::SELL_ORDER_INACTIVE }}" {{ request('status') == Status::SELL_ORDER_INACTIVE ? 'selected' : '' }}>@lang('Inactive')</option>
            <option value="{{ Status::SELL_ORDER_SOLD }}" {{ request('status') == Status::SELL_ORDER_SOLD ? 'selected' : '' }}>@lang('Sold')</option>
            <option value="{{ Status::SELL_ORDER_CANCELLED }}" {{ request('status') == Status::SELL_ORDER_CANCELLED ? 'selected' : '' }}>@lang('Cancelled')</option>
        </select>
        
        <select name="product_id" class="form-control form--control" style="width: auto;" onchange="this.form.submit()">
            <option value="">@lang('All Products')</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
@endpush

