@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--md table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Order Code')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Requested Qty')</th>
                                    <th>@lang('Pending Qty')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Total Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Expires At')</th>
                                    <th>@lang('Created')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="fw-bold text--primary">{{ $order->order_code }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $order->user->fullname ?? 'N/A' }}</span>
                                            <br>
                                            <span class="small">
                                                <a href="{{ route('admin.users.detail', $order->user_id) }}">
                                                    <span>@</span>{{ $order->user->username }}
                                                </a>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $order->product->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span>
                                                {{ showAmount($order->requested_quantity, 4, currencyFormat: false) }}
                                                {{ $order->product->unit->symbol ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text--warning">
                                                {{ showAmount($order->pending_quantity, 4, currencyFormat: false) }}
                                                {{ $order->product->unit->symbol ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span>
                                                {{ showAmount($order->requested_price, 2) }}
                                                {{ $order->product->currency->code ?? gs('cur_sym') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">
                                                {{ showAmount($order->pending_quantity * $order->requested_price, 2) }}
                                                {{ $order->product->currency->code ?? gs('cur_sym') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($order->status == \App\Constants\Status::PENDING_BUY_ORDER)
                                                <span class="badge badge--warning">@lang('Pending')</span>
                                            @elseif($order->status == \App\Constants\Status::PENDING_BUY_FULFILLED)
                                                <span class="badge badge--success">@lang('Fulfilled')</span>
                                            @elseif($order->status == \App\Constants\Status::PENDING_BUY_CANCELLED)
                                                <span class="badge badge--danger">@lang('Cancelled')</span>
                                            @elseif($order->status == \App\Constants\Status::PENDING_BUY_EXPIRED)
                                                <span class="badge badge--dark">@lang('Expired')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->expires_at)
                                                <span>{{ showDateTime($order->expires_at, 'd M Y') }}</span>
                                                @if($order->expires_at->isPast())
                                                    <br><span class="badge badge--danger badge--sm">@lang('Expired')</span>
                                                @else
                                                    <br><small class="text--info">{{ $order->expires_at->diffForHumans() }}</small>
                                                @endif
                                            @else
                                                <span>@lang('No Expiry')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span>{{ showDateTime($order->created_at, 'd M Y') }}</span>
                                            <br><small>{{ $order->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="las la-ellipsis-v"></i>@lang('Action')
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="{{ route('admin.pending-buy-orders.show', $order->id) }}" class="dropdown-item">
                                                    <i class="las la-eye text--info"></i>@lang('Details')
                                                </a>
                                                
                                                @if($order->status == \App\Constants\Status::PENDING_BUY_ORDER)
                                                    <button type="button" class="dropdown-item confirmationBtn" data-action="{{ route('admin.pending-buy-orders.approve', $order->id) }}" data-question="@lang('Are you sure to approve this pending order?')">
                                                        <i class="las la-check text--success"></i>@lang('Approve')
                                                    </button>
                                                    
                                                    <button type="button" class="dropdown-item rejectBtn" data-id="{{ $order->id }}">
                                                        <i class="las la-times text--danger"></i>@lang('Reject')
                                                    </button>
                                                    
                                                    <button type="button" class="dropdown-item confirmationBtn" data-action="{{ route('admin.pending-buy-orders.mark-expired', $order->id) }}" data-question="@lang('Are you sure to mark this order as expired?')">
                                                        <i class="las la-clock text--warning"></i>@lang('Mark as Expired')
                                                    </button>
                                                @endif
                                                
                                                <button type="button" class="dropdown-item confirmationBtn" data-action="{{ route('admin.pending-buy-orders.destroy', $order->id) }}" data-question="@lang('Are you sure to delete this pending order?')">
                                                    <i class="las la-trash text--danger"></i>@lang('Delete')
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="11">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($pendingOrders->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($pendingOrders) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Pending Order')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted">@lang('Are you sure you want to reject this pending order?')</p>
                        <div class="form-group">
                            <label>@lang('Reason (Optional)')</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="@lang('Enter reason for rejection')"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Confirm Reject')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            $('.rejectBtn').on('click', function() {
                var modal = $('#rejectModal');
                var orderId = $(this).data('id');
                var action = '{{ route("admin.pending-buy-orders.reject", "") }}/' + orderId;
                modal.find('#rejectForm').attr('action', action);
                modal.modal('show');
            });

        })(jQuery);
    </script>
@endpush

