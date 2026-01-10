@extends('admin.layouts.app')

@php
    use App\Constants\Status;
    // حساب الكمية المباعة والكمية المتاحة بشكل صحيح
    $initialQuantity = $sellOrder->quantity ?? 0;
    $availableQuantity = $sellOrder->available_quantity ?? $initialQuantity;
    $soldQuantity = max(0, $initialQuantity - $availableQuantity);
    $availableQty = $availableQuantity;
@endphp

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('User Sell Order Details')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table--light">
                                <tr>
                                    <th width="40%">@lang('Sell Order Code')</th>
                                    <td><span class="badge bg--primary">{{ $sellOrder->sell_order_code }}</span></td>
                                </tr>
                                <tr>
                                    <th>@lang('User')</th>
                                    <td>
                                        @if($sellOrder->user)
                                            <a href="{{ route('admin.users.detail', $sellOrder->user_id) }}" class="text--base">
                                                {{ $sellOrder->user->username ?? 'N/A' }} ({{ $sellOrder->user->email ?? 'N/A' }})
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('Product')</th>
                                    <td>{{ $sellOrder->product->name ?? ($sellOrder->batch ? $sellOrder->batch->product->name : 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Batch Code')</th>
                                    <td>
                                        @if($sellOrder->batch)
                                            <span class="badge badge--info">{{ $sellOrder->batch->batch_code }}</span>
                                        @else
                                            <span class="text-muted">@lang('N/A')</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('Warehouse')</th>
                                    <td>{{ $sellOrder->warehouse->name ?? ($sellOrder->batch ? $sellOrder->batch->warehouse->name : 'N/A') }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Status')</th>
                                    <td>@php echo $sellOrder->statusBadge; @endphp</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table--light">
                                <tr>
                                    <th width="40%">@lang('Initial Quantity')</th>
                                    <td><strong>{{ showAmount($sellOrder->quantity, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>@lang('Available Quantity')</th>
                                    <td>
                                        <span class="badge {{ $availableQty > 0 ? 'bg--success' : 'bg--danger' }}">
                                            {{ showAmount($availableQty, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('Sold Quantity')</th>
                                    <td>
                                        <span class="badge bg--info">
                                            {{ showAmount($soldQuantity, 4, currencyFormat: false) }} {{ $sellOrder->unit->symbol ?? 'Unit' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>@lang('Buy Price')</th>
                                    <td>{{ showAmount($sellOrder->buy_price ?? 0, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }} / {{ $sellOrder->unit->symbol ?? 'Unit' }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Sell Price')</th>
                                    <td><strong>{{ showAmount($sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }} / {{ $sellOrder->unit->symbol ?? 'Unit' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>@lang('Total Value (Available)')</th>
                                    <td><strong>{{ showAmount($availableQty * $sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }}</strong></td>
                                </tr>
                                @if($soldQuantity > 0)
                                <tr>
                                    <th>@lang('Total Value (Sold)')</th>
                                    <td><strong class="text-info">{{ showAmount($soldQuantity * $sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }}</strong></td>
                                </tr>
                                @endif
                                <tr>
                                    <th>@lang('Total Value (Initial)')</th>
                                    <td><strong>{{ showAmount($sellOrder->quantity * $sellOrder->sell_price, 2, currencyFormat: false) }} {{ $sellOrder->currency->symbol ?? '' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>@lang('Created At')</th>
                                    <td>{{ showDateTime($sellOrder->created_at) }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Updated At')</th>
                                    <td>{{ showDateTime($sellOrder->updated_at) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.user-sell-order.index') }}" class="btn btn--primary">
                        <i class="la la-arrow-left"></i> @lang('Back to List')
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

