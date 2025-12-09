@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            @if (request()->routeIs('admin.gold.history.redeem'))
                <div class="show-filter mb-3 text-end">
                    <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
                </div>
                <div class="card responsive-filter-card mb-4">
                    <div class="card-body">
                        <form>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="flex-grow-1">
                                    <label>@lang('Username/Category')</label>
                                    <input type="search" name="search" value="{{ request()->search }}" class="form-control">
                                </div>
                                <div class="flex-grow-1">
                                    <label>@lang('Status')</label>
                                    <select name="status" class="form-control select2" data-minimum-results-for-search="-1">
                                        <option value="">@lang('All')</option>
                                        <option value="{{ Status::REDEEM_STATUS_PROCESSING }}" @selected(request()->status == Status::REDEEM_STATUS_PROCESSING)>@lang('Processing')</option>
                                        <option value="{{ Status::REDEEM_STATUS_SHIPPED }}" @selected(request()->status == Status::REDEEM_STATUS_SHIPPED)>@lang('Shipped')</option>
                                        <option value="{{ Status::REDEEM_STATUS_DELIVERED }}" @selected(request()->status == Status::REDEEM_STATUS_DELIVERED)>@lang('Delivered')</option>
                                        <option value="{{ Status::REDEEM_STATUS_CANCELLED }}" @selected(request()->status == Status::REDEEM_STATUS_CANCELLED)>@lang('Cancelled')</option>
                                    </select>
                                </div>
                                <div class="flex-grow-1">
                                    <label>@lang('Date')</label>
                                    <x-search-date-field :showIcon="false" />
                                </div>
                                <div class="flex-grow-1 align-self-end">
                                    <button class="btn btn--primary w-100 h-45"><i class="fas fa-filter"></i> @lang('Filter')</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    @if (request()->routeIs('admin.gold.history.gift'))
                                        <th>@lang('Recipient')</th>
                                    @endif
                                    <th>@lang('Date & Time')</th>
                                    <th>@lang('Trx')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Amount & Charge')</th>
                                    @if (request()->routeIs('admin.gold.history.buy'))
                                        <th>@lang('Vat')</th>
                                    @endif
                                    @if (request()->routeIs('admin.gold.history.redeem'))
                                        <th>@lang('Status')</th>
                                        <th>@lang('Action')</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goldHistories as $goldHistory)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $goldHistory->user->fullname }}</span>
                                            <br>
                                            <span class="small"> <a href="{{ appendQuery('search', $goldHistory->user->username) }}"><span>@</span>{{ $goldHistory->user->username }}</a> </span>
                                        </td>
                                        @if (request()->routeIs('admin.gold.history.gift'))
                                            <td>
                                                <span class="fw-bold">{{ $goldHistory->recipient->fullname }}</span>
                                                <br>
                                                <span class="small"> <a href="{{ appendQuery('search', $goldHistory->recipient->username) }}"><span>@</span>{{ $goldHistory->recipient->username }}</a> </span>
                                            </td>
                                        @endif
                                        <td>{{ showDateTime($goldHistory->created_at) }}<br>{{ diffForHumans($goldHistory->created_at) }}</td>
                                        <td>{{ $goldHistory->trx }}</td>
                                        <td>
                                            {{ __($goldHistory->category->name) }}
                                            <br>
                                            {{ $goldHistory->category->karat }} @lang('Karat')
                                        </td>
                                        <td>{{ showAmount($goldHistory->quantity, 4, currencyFormat: false) }} @lang('gram')</td>
                                        <td>
                                            {{ showAmount($goldHistory->amount) }}
                                            <br>
                                            {{ showAmount($goldHistory->charge) }}
                                        </td>

                                        @if (request()->routeIs('admin.gold.history.buy'))
                                            <td>{{ showAmount($goldHistory->vat) }}</td>
                                        @endif

                                        @if (request()->routeIs('admin.gold.history.redeem'))
                                            <td>
                                                @php echo $goldHistory->redeemData->statusBadge; @endphp
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline--primary detailsBtn" data-order_details='@json($goldHistory->redeemData->order_details->items)' data-pickup_point='@json($goldHistory->redeemData->pickupPoint)' data-address="{{ $goldHistory->redeemData->delivery_address }}">
                                                    <i class="la la-desktop"></i> @lang('Details')
                                                </button>
                                                @if ($goldHistory->redeemData->status == Status::REDEEM_STATUS_PROCESSING)
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn mx-1" data-action="{{ route('admin.gold.history.redeem.status', [$goldHistory->redeemData->id, Status::REDEEM_STATUS_SHIPPED]) }}" data-question="@lang('Are you sure to mark this redeem as shipped?')">
                                                        <i class="la la-truck"></i>@lang('Ship')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.gold.history.redeem.status', [$goldHistory->redeemData->id, Status::REDEEM_STATUS_CANCELLED]) }}" data-question="@lang('Are you sure to mark this redeem as cancelled?')">
                                                        <i class="la la-times"></i>@lang('Cancel')
                                                    </button>
                                                @elseif($goldHistory->redeemData->status == Status::REDEEM_STATUS_SHIPPED)
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn mx-1" data-action="{{ route('admin.gold.history.redeem.status', [$goldHistory->redeemData->id, Status::REDEEM_STATUS_DELIVERED]) }}" data-question="@lang('Are you sure to mark this redeem as delivered?')">
                                                        <i class="la la-check"></i>@lang('Deliver')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.gold.history.redeem.status', [$goldHistory->redeemData->id, Status::REDEEM_STATUS_CANCELLED]) }}" data-question="@lang('Are you sure to mark this redeem as cancelled?')">
                                                        <i class="la la-times"></i>@lang('Cancel')
                                                    </button>
                                                @endif
                                            </td>
                                        @endif
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
                @if ($goldHistories->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($goldHistories) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>


    <div id="redeemDetailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Redeem Details')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 class="mb-1">@lang('Order Details')</h6>
                    <div class="orderDetails"></div>
                    <hr>
                    <h6 class="mb-1">@lang('Delivery') - <span class="deliveryPoint"></span></h6>
                    <div class="deliveryDetails"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@if (!request()->routeIs('admin.gold.history.redeem'))
    @push('breadcrumb-plugins')
        <x-search-form placeholder="Username/Category" dateSearch='yes' />
    @endpush
@endif

@if (request()->routeIs('admin.gold.history.redeem'))
    @push('script')
        <script>
            (function($) {
                "use strict";

                let modal = $('#redeemDetailsModal');

                $('.detailsBtn').on('click', function() {
                    let orderDetails = $(this).data('order_details');
                    let html = '';
                    orderDetails.forEach(item => {
                        html += `<span>${item.text}</span></br>`;
                    });
                    modal.find('.orderDetails').html(html);

                    let pickupPoint = $(this).data('pickup_point');
                    if (pickupPoint) {
                        modal.find('.deliveryPoint').text(`@lang('Pickup Point')`);
                        modal.find('.deliveryDetails').text(pickupPoint.address);
                    } else {
                        let address = $(this).data('address');
                        modal.find('.deliveryPoint').text(`@lang('Home Delivery')`);
                        modal.find('.deliveryDetails').html(address);
                    }

                    modal.modal('show');
                });


            })(jQuery);
        </script>
    @endpush
@endif
