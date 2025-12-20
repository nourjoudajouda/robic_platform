@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card h-auto">
                <div class="dashboard-table">
                    <table class="table table--responsive--sm">
                        <thead>
                            <tr>
                                <th>@lang('Order Code')</th>
                                <th>@lang('Date Time')</th>
                                <th>@lang('Product')</th>
                                <th>@lang('Batch')</th>
                                <th>@lang('Sell Price')</th>
                                <th>@lang('Total Qty')</th>
                                <th>@lang('Available')</th>
                                <th>@lang('Sold')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellOrders as $sellOrder)
                                @php
                                    $product = $sellOrder->product ?? ($sellOrder->batch ? $sellOrder->batch->product : null);
                                    $soldQuantity = $sellOrder->quantity - $sellOrder->available_quantity;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge badge--primary">{{ $sellOrder->sell_order_code ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="d-block">{{ showDateTime($sellOrder->created_at, 'd M, Y') }}</span>
                                            <span class="d-block">{{ showDateTime($sellOrder->created_at, 'h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $product ? $product->name : 'N/A' }}</td>
                                    <td>
                                        @if($sellOrder->batch)
                                            <span class="badge badge--info">{{ $sellOrder->batch->batch_code }}</span>
                                        @else
                                            <span class="text-muted">@lang('Mixed')</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ showAmount($sellOrder->sell_price, 2, currencyFormat: false) }} 
                                        {{ $product && $product->currency ? $product->currency->symbol : '' }}
                                        / {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}
                                    </td>
                                    <td>
                                        {{ showAmount($sellOrder->quantity, 4, currencyFormat: false) }} 
                                        {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}
                                    </td>
                                    <td>
                                        <span class="text-success">
                                            {{ showAmount($sellOrder->available_quantity, 4, currencyFormat: false) }} 
                                            {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($soldQuantity > 0)
                                            <span class="text-info">
                                                {{ showAmount($soldQuantity, 4, currencyFormat: false) }} 
                                                {{ $product && $product->unit ? $product->unit->symbol : 'Unit' }}
                                            </span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($sellOrder->status == 1)
                                            <span class="badge badge--success">@lang('Active')</span>
                                        @elseif($sellOrder->status == 2)
                                            <span class="badge badge--info">@lang('Completed')</span>
                                        @elseif($sellOrder->status == 0)
                                            <span class="badge badge--danger">@lang('Cancelled')</span>
                                        @else
                                            <span class="badge badge--warning">@lang('Unknown')</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <x-empty-card empty-message="No sell orders found" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xxl-10">
            <div class="pagination-wrapper">
                {{ paginateLinks($sellOrders) }}
            </div>
        </div>

    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/41.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.sell.form') }}" class="btn btn--danger btn--lg"> <i class="fas fa-money-bill-trend-up"></i> @lang('Sell Green Coffee')</a>
@endpush
