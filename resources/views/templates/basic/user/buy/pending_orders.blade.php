@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card custom--card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">@lang('My Pending Buy Orders')</h5>
                    <a href="{{ route('user.buy.form') }}" class="btn btn--base btn--sm">
                        <i class="fas fa-plus"></i> @lang('New Buy Order')
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table--responsive--lg">
                            <thead>
                                <tr>
                                    <th>@lang('Order Code')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Requested Qty')</th>
                                    <th>@lang('Pending Qty')</th>
                                    <th>@lang('Requested Price')</th>
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
                                            <span class="fw-bold text--base">{{ $order->order_code }}</span>
                                        </td>
                                        <td>
                                            <span class="text-white">{{ $order->product->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-white">
                                                {{ showAmount($order->requested_quantity, 4, currencyFormat: false) }}
                                                {{ $order->product->unit->symbol ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-warning fw-bold">
                                                {{ showAmount($order->pending_quantity, 4, currencyFormat: false) }}
                                                {{ $order->product->unit->symbol ?? 'Unit' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-white">
                                                {{ showAmount($order->requested_price, 2) }}
                                                {{ $order->product->currency->code ?? gs('cur_sym') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-white fw-bold">
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
                                                <span class="text-muted">
                                                    {{ showDateTime($order->expires_at, 'd M Y') }}
                                                    @if($order->expires_at->isPast())
                                                        <span class="badge badge--danger badge--sm">@lang('Expired')</span>
                                                    @else
                                                        <small class="d-block text-info">
                                                            ({{ $order->expires_at->diffForHumans() }})
                                                        </small>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">@lang('No Expiry')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ showDateTime($order->created_at, 'd M Y') }}</span>
                                            <small class="d-block text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($order->status == \App\Constants\Status::PENDING_BUY_ORDER)
                                                <button type="button" class="btn btn--sm btn--danger" onclick="cancelOrder({{ $order->id }})">
                                                    <i class="fas fa-times"></i> @lang('Cancel')
                                                </button>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="py-5">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">@lang('No pending orders found')</p>
                                                <a href="{{ route('user.buy.form') }}" class="btn btn--base btn--sm mt-3">
                                                    <i class="fas fa-shopping-cart"></i> @lang('Start Shopping')
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($pendingOrders->hasPages())
                    <div class="card-footer">
                        {{ paginateLinks($pendingOrders) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    function cancelOrder(orderId) {
        if (!confirm('@lang("Are you sure you want to cancel this pending order?")')) {
            return;
        }

        $.ajax({
            url: '{{ url("user/buy/pending") }}/' + orderId + '/cancel',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || '@lang("An error occurred. Please try again.")';
                alert(errorMsg);
            }
        });
    }
</script>
@endpush

