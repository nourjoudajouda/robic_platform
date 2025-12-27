@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-8 col-md-8 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('Pending Order Information')</h5>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Order Code')
                            <span class="fw-bold text--primary">{{ $pendingOrder->order_code }}</span>
                        </li>
                        
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('User')
                            <span>
                                <a href="{{ route('admin.users.detail', $pendingOrder->user_id) }}">
                                    {{ $pendingOrder->user->fullname }} (<span>@</span>{{ $pendingOrder->user->username }})
                                </a>
                            </span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Product')
                            <span class="fw-bold">{{ $pendingOrder->product->name ?? 'N/A' }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Requested Quantity')
                            <span>{{ showAmount($pendingOrder->requested_quantity, 4, currencyFormat: false) }} {{ $pendingOrder->product->unit->symbol ?? 'Unit' }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Fulfilled Quantity')
                            <span class="text--success">{{ showAmount($pendingOrder->fulfilled_quantity, 4, currencyFormat: false) }} {{ $pendingOrder->product->unit->symbol ?? 'Unit' }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Pending Quantity')
                            <span class="fw-bold text--warning">{{ showAmount($pendingOrder->pending_quantity, 4, currencyFormat: false) }} {{ $pendingOrder->product->unit->symbol ?? 'Unit' }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Requested Price')
                            <span>{{ showAmount($pendingOrder->requested_price, 2) }} {{ $pendingOrder->product->currency->code ?? gs('cur_sym') }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Total Pending Amount')
                            <span class="fw-bold">{{ showAmount($pendingOrder->pending_quantity * $pendingOrder->requested_price, 2) }} {{ $pendingOrder->product->currency->code ?? gs('cur_sym') }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Available Quantity at Requested Price')
                            @if($availableQuantity >= $pendingOrder->pending_quantity)
                                <span class="fw-bold text--success">{{ showAmount($availableQuantity, 4, currencyFormat: false) }} {{ $pendingOrder->product->unit->symbol ?? 'Unit' }} ✓</span>
                            @else
                                <span class="fw-bold text--danger">{{ showAmount($availableQuantity, 4, currencyFormat: false) }} {{ $pendingOrder->product->unit->symbol ?? 'Unit' }} (Insufficient)</span>
                            @endif
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Status')
                            @if($pendingOrder->status == \App\Constants\Status::PENDING_BUY_ORDER)
                                <span class="badge badge--warning">@lang('Pending')</span>
                            @elseif($pendingOrder->status == \App\Constants\Status::PENDING_BUY_FULFILLED)
                                <span class="badge badge--success">@lang('Fulfilled')</span>
                            @elseif($pendingOrder->status == \App\Constants\Status::PENDING_BUY_CANCELLED)
                                <span class="badge badge--danger">@lang('Cancelled')</span>
                            @elseif($pendingOrder->status == \App\Constants\Status::PENDING_BUY_EXPIRED)
                                <span class="badge badge--dark">@lang('Expired')</span>
                            @endif
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Created At')
                            <span>{{ showDateTime($pendingOrder->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            @lang('Expires At')
                            @if($pendingOrder->expires_at)
                                <span>
                                    {{ showDateTime($pendingOrder->expires_at) }}
                                    @if($pendingOrder->expires_at->isPast())
                                        <span class="badge badge--danger badge--sm">@lang('Expired')</span>
                                    @else
                                        <small class="text--info d-block">{{ $pendingOrder->expires_at->diffForHumans() }}</small>
                                    @endif
                                </span>
                            @else
                                <span>@lang('No Expiry')</span>
                            @endif
                        </li>

                        @if($pendingOrder->notified_at)
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                @lang('Last Notified At')
                                <span>{{ showDateTime($pendingOrder->notified_at) }}</span>
                            </li>
                        @endif

                        @if($pendingOrder->notes)
                            <li class="list-group-item">
                                <strong>@lang('Notes'):</strong>
                                <p class="mt-2 mb-0">{{ $pendingOrder->notes }}</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 mb-30">
            <div class="card b-radius--10 overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('Actions')</h5>

                    @if($pendingOrder->status == \App\Constants\Status::PENDING_BUY_ORDER)
                        @if($availableQuantity >= $pendingOrder->pending_quantity)
                            <button type="button" class="btn btn--success btn-block confirmationBtn mb-3" data-action="{{ route('admin.pending-buy-orders.approve', $pendingOrder->id) }}" data-question="@lang('Sufficient quantity is available. Are you sure to approve this order?')">
                                <i class="las la-check"></i> @lang('Approve Order')
                            </button>
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="las la-exclamation-triangle"></i>
                                @lang('Insufficient quantity available at the requested price.')
                            </div>
                        @endif

                        <button type="button" class="btn btn--danger btn-block mb-3" onclick="showRejectModal()">
                            <i class="las la-times"></i> @lang('Reject Order')
                        </button>

                        <button type="button" class="btn btn--warning btn-block confirmationBtn mb-3" data-action="{{ route('admin.pending-buy-orders.mark-expired', $pendingOrder->id) }}" data-question="@lang('Are you sure to mark this order as expired?')">
                            <i class="las la-clock"></i> @lang('Mark as Expired')
                        </button>
                    @endif

                    <button type="button" class="btn btn--dark btn-block confirmationBtn" data-action="{{ route('admin.pending-buy-orders.destroy', $pendingOrder->id) }}" data-question="@lang('Are you sure to delete this pending order?')">
                        <i class="las la-trash"></i> @lang('Delete Order')
                    </button>
                </div>
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
                <form action="{{ route('admin.pending-buy-orders.reject', $pendingOrder->id) }}" method="POST">
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

@push('script')
    <script>
        function showRejectModal() {
            $('#rejectModal').modal('show');
        }
    </script>
@endpush

