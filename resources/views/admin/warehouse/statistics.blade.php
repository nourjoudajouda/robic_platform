@extends('admin.layouts.app')

@php
    use App\Constants\Status;
@endphp

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <!-- Warehouse Info Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="mb-3">@lang('Warehouse Information')</h4>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">@lang('Name'):</th>
                                    <td>{{ __($warehouse->name) }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Code'):</th>
                                    <td><span class="badge bg--primary">{{ $warehouse->code }}</span></td>
                                </tr>
                                <tr>
                                    <th>@lang('Location'):</th>
                                    <td>{{ $warehouse->location }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Manager'):</th>
                                    <td>{{ $warehouse->manager_name }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('Mobile'):</th>
                                    <td>{{ $warehouse->mobile }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4 class="mb-3">@lang('Statistics Overview')</h4>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="widget-card bg--primary">
                                        <div class="widget-card-left">
                                            <div class="widget-card-icon">
                                                <i class="las la-boxes"></i>
                                            </div>
                                            <div class="widget-card-content">
                                                <h6 class="widget-card-amount">{{ showAmount($totalQuantity, 4, currencyFormat: false) }}</h6>
                                                <p class="widget-card-title">@lang('Total Quantity in Warehouse')</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="widget-card bg--success">
                                        <div class="widget-card-left">
                                            <div class="widget-card-icon">
                                                <i class="las la-arrow-circle-up"></i>
                                            </div>
                                            <div class="widget-card-content">
                                                <h6 class="widget-card-amount">{{ $incomingTransactions->total() }}</h6>
                                                <p class="widget-card-title">@lang('Total Incoming Transactions')</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="widget-card bg--info">
                                        <div class="widget-card-left">
                                            <div class="widget-card-icon">
                                                <i class="las la-arrow-circle-down"></i>
                                            </div>
                                            <div class="widget-card-content">
                                                <h6 class="widget-card-amount">{{ $outgoingTransactions->total() }}</h6>
                                                <p class="widget-card-title">@lang('Total Outgoing Transactions')</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-3">
                                    <div class="widget-card bg--warning">
                                        <div class="widget-card-left">
                                            <div class="widget-card-icon">
                                                <i class="las la-box"></i>
                                            </div>
                                            <div class="widget-card-content">
                                                <h6 class="widget-card-amount">{{ $batches->total() }}</h6>
                                                <p class="widget-card-title">@lang('Total Batches')</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="widget-card bg--success">
                                        <div class="widget-card-left">
                                            <div class="widget-card-icon">
                                                <i class="las la-users"></i>
                                            </div>
                                            <div class="widget-card-content">
                                                <h6 class="widget-card-amount">{{ $userQuantities->count() }}</h6>
                                                <p class="widget-card-title">@lang('Number of Users')</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Incoming Transactions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Incoming Transactions') (@lang('Buy'))</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Date & Time')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Trx')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($incomingTransactions as $transaction)
                                    <tr>
                                        <td>
                                            {{ showDateTime($transaction->created_at) }}<br>
                                            <small class="text-muted">{{ diffForHumans($transaction->created_at) }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $transaction->user->fullname ?? 'N/A' }}</span><br>
                                            @if($transaction->user)
                                            <small>@<a href="{{ route('admin.users.detail', $transaction->user->id) }}">{{ $transaction->user->username }}</a></small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->product)
                                                {{ __($transaction->product->name) }}
                                            @elseif($transaction->asset && $transaction->asset->product)
                                                {{ __($transaction->asset->product->name) }}
                                            @elseif($transaction->batch && $transaction->batch->product)
                                                {{ __($transaction->batch->product->name) }}
                                            @else
                                                @lang('N/A')
                                            @endif
                                        </td>
                                        <td>
                                            {{ showAmount($transaction->quantity, 4, currencyFormat: false) }}
                                            {{ $transaction->itemUnit->symbol ?? ($transaction->asset && $transaction->asset->itemUnit ? $transaction->asset->itemUnit->symbol : 'Unit') }}
                                        </td>
                                        <td>{{ showAmount($transaction->amount) }}</td>
                                        <td><span class="font-monospace">{{ $transaction->trx }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No incoming transactions found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($incomingTransactions->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($incomingTransactions) }}
                    </div>
                @endif
            </div>

            <!-- Outgoing Transactions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Outgoing Transactions') (@lang('Sell & Redeem'))</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Date & Time')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Trx')</th>
                                    @if(request()->has('show_redeem_status'))
                                    <th>@lang('Status')</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($outgoingTransactions as $transaction)
                                    <tr>
                                        <td>
                                            {{ showDateTime($transaction->created_at) }}<br>
                                            <small class="text-muted">{{ diffForHumans($transaction->created_at) }}</small>
                                        </td>
                                        <td>
                                            @php echo $transaction->statusBadge; @endphp
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $transaction->user->fullname ?? 'N/A' }}</span><br>
                                            @if($transaction->user)
                                            <small>@<a href="{{ route('admin.users.detail', $transaction->user->id) }}">{{ $transaction->user->username }}</a></small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->product)
                                                {{ __($transaction->product->name) }}
                                            @elseif($transaction->asset && $transaction->asset->product)
                                                {{ __($transaction->asset->product->name) }}
                                            @elseif($transaction->batch && $transaction->batch->product)
                                                {{ __($transaction->batch->product->name) }}
                                            @else
                                                @lang('N/A')
                                            @endif
                                        </td>
                                        <td>
                                            {{ showAmount($transaction->quantity, 4, currencyFormat: false) }}
                                            {{ $transaction->itemUnit->symbol ?? ($transaction->asset && $transaction->asset->itemUnit ? $transaction->asset->itemUnit->symbol : 'Unit') }}
                                        </td>
                                        <td>{{ showAmount($transaction->amount) }}</td>
                                        <td><span class="font-monospace">{{ $transaction->trx }}</span></td>
                                        @if(request()->has('show_redeem_status') && $transaction->type == Status::REDEEM_HISTORY)
                                        <td>
                                            @if($transaction->redeemData)
                                                @php echo $transaction->redeemData->statusBadge; @endphp
                                            @else
                                                <span class="badge badge--secondary">@lang('N/A')</span>
                                            @endif
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No outgoing transactions found')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($outgoingTransactions->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($outgoingTransactions) }}
                    </div>
                @endif
            </div>

            <!-- Batches -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Batches in Warehouse')</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('ID')</th>
                                    <th>@lang('Batch Code')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Quality Grade')</th>
                                    <th>@lang('Origin Country')</th>
                                    <th>@lang('Units Count')</th>
                                    <th>@lang('Buy Price')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Exp Date')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $batch)
                                    <tr>
                                        <td>{{ $batch->id }}</td>
                                        <td><span class="badge bg--primary">{{ $batch->batch_code }}</span></td>
                                        <td>{{ __($batch->product->name ?? 'N/A') }}</td>
                                        <td>{{ $batch->quality_grade ?? 'N/A' }}</td>
                                        <td>{{ $batch->origin_country ?? 'N/A' }}</td>
                                        <td>{{ showAmount($batch->units_count, currencyFormat: false) }} {{ $batch->unit->symbol ?? '' }}</td>
                                        <td>
                                            @if($batch->buy_price)
                                                {{ showAmount($batch->buy_price * $batch->units_count, currencyFormat: false) }} {{ $batch->currency->symbol ?? '' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php echo $batch->statusBadge; @endphp
                                        </td>
                                        <td>{{ $batch->exp_date ? showDateTime($batch->exp_date, 'Y-m-d') : '-' }}</td>
                                        <td>{{ showDateTime($batch->created_at) }}</td>
                                        <td>
                                            <a href="{{ route('admin.batch.edit', $batch->id) }}" class="btn btn-sm btn-outline--primary">
                                                <i class="la la-pen"></i> @lang('Edit')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No batches found in this warehouse')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($batches->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($batches) }}
                    </div>
                @endif
            </div>

            <!-- Users Quantities -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Quantities per User')</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Total Quantity')</th>
                                    <th>@lang('Products')</th>
                                    <th>@lang('Details')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userQuantities as $userData)
                                    <tr>
                                        <td>
                                            @if($userData['user'])
                                                <span class="fw-bold">{{ $userData['user']->fullname }}</span><br>
                                                <small>@<a href="{{ route('admin.users.detail', $userData['user']->id) }}">{{ $userData['user']->username }}</a></small>
                                            @else
                                                @lang('Unknown User')
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text--primary">{{ showAmount($userData['total_quantity'], 4, currencyFormat: false) }}</span>
                                        </td>
                                        <td>
                                            @if($userData['assets'] && $userData['assets']->count() > 0)
                                                <div class="d-flex flex-column gap-1">
                                                    @foreach($userData['assets']->take(3) as $asset)
                                                        <div>
                                                            <small>
                                                                {{ $asset->product ? __($asset->product->name) : 'N/A' }}: 
                                                                <strong>{{ showAmount($asset->quantity, 4, currencyFormat: false) }}</strong>
                                                                {{ $asset->itemUnit->symbol ?? 'Unit' }}
                                                            </small>
                                                        </div>
                                                    @endforeach
                                                    @if($userData['assets']->count() > 3)
                                                        <small class="text-muted">+ {{ $userData['assets']->count() - 3 }} @lang('more')</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">@lang('No products')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--info viewDetailsBtn" 
                                                data-user-id="{{ $userData['user']->id ?? 0 }}"
                                                data-assets="{{ $userData['assets']->map(function($asset) {
                                                    return [
                                                        'product' => $asset->product ? $asset->product->name : 'N/A',
                                                        'quantity' => $asset->quantity,
                                                        'unit' => $asset->itemUnit->symbol ?? 'Unit'
                                                    ];
                                                })->toJson() }}">
                                                <i class="las la-eye"></i> @lang('View Details')
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">@lang('No users found with assets in this warehouse')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Assets Details Modal -->
    <div class="modal fade" id="userAssetsModal" tabindex="-1" aria-labelledby="userAssetsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userAssetsModalLabel">@lang('User Assets Details')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="userAssetsContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.warehouse.index') }}" class="btn btn-outline--primary">
        <i class="las la-arrow-left"></i> @lang('Back to Warehouses')
    </a>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";
        
        $('.viewDetailsBtn').on('click', function() {
            const userId = $(this).data('user-id');
            const assets = $(this).data('assets');
            
            let html = '<table class="table table-sm">';
            html += '<thead><tr><th>@lang("Product")</th><th>@lang("Quantity")</th><th>@lang("Unit")</th></tr></thead>';
            html += '<tbody>';
            
            if (assets && assets.length > 0) {
                assets.forEach(function(asset) {
                    html += '<tr>';
                    html += '<td>' + asset.product + '</td>';
                    html += '<td>' + parseFloat(asset.quantity).toFixed(4) + '</td>';
                    html += '<td>' + asset.unit + '</td>';
                    html += '</tr>';
                });
            } else {
                html += '<tr><td colspan="3" class="text-center text-muted">@lang("No assets found")</td></tr>';
            }
            
            html += '</tbody></table>';
            
            $('#userAssetsContent').html(html);
            $('#userAssetsModal').modal('show');
        });
    })(jQuery);
</script>
@endpush

