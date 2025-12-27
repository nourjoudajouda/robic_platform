@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            @if (request()->routeIs('admin.bean.history.redeem'))
                <div class="show-filter mb-3 text-end">
                    <button type="button" class="btn btn-outline--primary showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
                </div>
                <div class="card responsive-filter-card mb-4">
                    <div class="card-body">
                        <form>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="flex-grow-1">
                                    <label>@lang('Username/Product')</label>
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
                                    @if (request()->routeIs('admin.bean.history.gift'))
                                        <th>@lang('Recipient')</th>
                                    @endif
                                    <th>@lang('Date & Time')</th>
                                    <th>@lang('Trx')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Amount & Charge')</th>
                                    @if (request()->routeIs('admin.bean.history.buy'))
                                        <th>@lang('Vat')</th>
                                    @endif
                                    @if (request()->routeIs('admin.bean.history.redeem'))
                                        <th>@lang('Status')</th>
                                        <th>@lang('Action')</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($beanHistories as $beanHistory)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $beanHistory->user->fullname }}</span>
                                            <br>
                                            <span class="small"> <a href="{{ appendQuery('search', $beanHistory->user->username) }}"><span>@</span>{{ $beanHistory->user->username }}</a> </span>
                                        </td>
                                        @if (request()->routeIs('admin.bean.history.gift'))
                                            <td>
                                                <span class="fw-bold">{{ $beanHistory->recipient->fullname }}</span>
                                                <br>
                                                <span class="small"> <a href="{{ appendQuery('search', $beanHistory->recipient->username) }}"><span>@</span>{{ $beanHistory->recipient->username }}</a> </span>
                                            </td>
                                        @endif
                                        <td>{{ showDateTime($beanHistory->created_at) }}<br>{{ diffForHumans($beanHistory->created_at) }}</td>
                                        <td>{{ $beanHistory->trx }}</td>
                                        <td>
                                            @if($beanHistory->product)
                                                {{ __($beanHistory->product->name) }}
                                            @elseif($beanHistory->batch && $beanHistory->batch->product)
                                                {{ __($beanHistory->batch->product->name) }}
                                                @if($beanHistory->batch->quality_grade)
                                                    <br>
                                                    <small class="text-muted">{{ $beanHistory->batch->quality_grade }}</small>
                                                @endif
                                            @else
                                                @lang('N/A')
                                            @endif
                                        </td>
                                        <td>{{ showAmount($beanHistory->quantity, 4, currencyFormat: false) }} 
                                            {{ $beanHistory->itemUnit->symbol ?? ($beanHistory->product && $beanHistory->product->unit ? $beanHistory->product->unit->symbol : ($beanHistory->batch && $beanHistory->batch->product && $beanHistory->batch->product->unit ? $beanHistory->batch->product->unit->symbol : 'Unit')) }}
                                        </td>
                                        <td>
                                            {{ showAmount($beanHistory->amount) }}
                                            <br>
                                            {{ showAmount($beanHistory->charge) }}
                                        </td>

                                        @if (request()->routeIs('admin.bean.history.buy'))
                                            <td>{{ showAmount($beanHistory->vat) }}</td>
                                        @endif

                                        @if (request()->routeIs('admin.bean.history.redeem'))
                                            <td>
                                                @php echo $beanHistory->redeemData->statusBadge; @endphp
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline--primary detailsBtn" 
                                                    data-product_name="{{ $beanHistory->product ? $beanHistory->product->name : 'N/A' }}"
                                                    data-quantity="{{ showAmount($beanHistory->quantity, 4, currencyFormat: false) }}"
                                                    data-unit="{{ $beanHistory->product && $beanHistory->product->unit ? $beanHistory->product->unit->symbol : 'Unit' }}"
                                                    data-shipping_cost="{{ showAmount($beanHistory->charge) }}"
                                                    data-delivery_type="{{ $beanHistory->redeemData->delivery_type }}"
                                                    data-delivery_address="{{ $beanHistory->redeemData->delivery_address }}"
                                                    data-shipping_method="{{ $beanHistory->redeemData->shippingMethod ? $beanHistory->redeemData->shippingMethod->name : 'N/A' }}"
                                                    data-distance="{{ $beanHistory->redeemData->distance ? showAmount($beanHistory->redeemData->distance, 2, currencyFormat: false) : 'N/A' }}">
                                                    <i class="la la-desktop"></i> @lang('Details')
                                                </button>
                                                @if ($beanHistory->redeemData->status == Status::REDEEM_STATUS_PROCESSING)
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn mx-1" data-action="{{ route('admin.bean.history.redeem.status', [$beanHistory->redeemData->id, Status::REDEEM_STATUS_SHIPPED]) }}" data-question="@lang('Are you sure to mark this order as shipped?')">
                                                        <i class="la la-truck"></i>@lang('Ship')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.bean.history.redeem.status', [$beanHistory->redeemData->id, Status::REDEEM_STATUS_CANCELLED]) }}" data-question="@lang('Are you sure to mark this order as cancelled?')">
                                                        <i class="la la-times"></i>@lang('Cancel')
                                                    </button>
                                                @elseif($beanHistory->redeemData->status == Status::REDEEM_STATUS_SHIPPED)
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn mx-1" data-action="{{ route('admin.bean.history.redeem.status', [$beanHistory->redeemData->id, Status::REDEEM_STATUS_DELIVERED]) }}" data-question="@lang('Are you sure to mark this order as delivered?')">
                                                        <i class="la la-check"></i>@lang('Deliver')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.bean.history.redeem.status', [$beanHistory->redeemData->id, Status::REDEEM_STATUS_CANCELLED]) }}" data-question="@lang('Are you sure to mark this order as cancelled?')">
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
                @if ($beanHistories->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($beanHistories) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>


    <div id="redeemDetailsModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Shipping & Receiving Details')</h5>
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

@if (!request()->routeIs('admin.bean.history.redeem'))
    @push('breadcrumb-plugins')
        <x-search-form placeholder="Username/Product" dateSearch='yes' />
    @endpush
@endif

@if (request()->routeIs('admin.bean.history.redeem'))
    @push('script')
        <script>
            (function($) {
                "use strict";

                let modal = $('#redeemDetailsModal');

                $('.detailsBtn').on('click', function() {
                    let productName = $(this).data('product_name');
                    let quantity = $(this).data('quantity');
                    let unit = $(this).data('unit');
                    let shippingCost = $(this).data('shipping_cost');
                    let deliveryType = $(this).data('delivery_type');
                    let deliveryAddress = $(this).data('delivery_address');
                    let shippingMethod = $(this).data('shipping_method');
                    let distance = $(this).data('distance');
                    
                    let html = `
                        <div class="mb-3">
                            <strong>@lang('Product'):</strong> ${productName}<br>
                            <strong>@lang('Quantity'):</strong> ${quantity} ${unit}<br>
                            <strong>@lang('Shipping Cost'):</strong> ${shippingCost}
                        </div>
                    `;
                    
                    if (deliveryType === 'shipping' && shippingMethod !== 'N/A') {
                        html += `
                            <div class="mb-3">
                                <strong>@lang('Shipping Method'):</strong> ${shippingMethod}<br>
                                <strong>@lang('Distance'):</strong> ${distance} @lang('km')
                            </div>
                        `;
                    }
                    
                    modal.find('.orderDetails').html(html);

                    if (deliveryType === 'pickup') {
                        modal.find('.deliveryPoint').text(`@lang('Pickup from Warehouse')`);
                    } else {
                        modal.find('.deliveryPoint').text(`@lang('Home Delivery')`);
                    }
                    modal.find('.deliveryDetails').html(deliveryAddress);

                    modal.modal('show');
                });


            })(jQuery);
        </script>
    @endpush
@endif
