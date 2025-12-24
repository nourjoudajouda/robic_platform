@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card">
                <div class="dashboard-table">
                    <table class="table table--responsive--sm">
                        <thead>
                            <tr>
                                <th>@lang('Date Time')</th>
                                <th>@lang('Product')</th>
                                <th>@lang('Quantity')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Shipping Cost')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($redeemHistories as $redeemHistory)
                                <tr>
                                    <td>{{ showDateTime($redeemHistory->created_at) }}</td>
                                    <td>{{ $redeemHistory->product ? $redeemHistory->product->name : 'N/A' }}</td>
                                    <td>{{ showAmount($redeemHistory->quantity, currencyFormat: false) }} {{ $redeemHistory->product && $redeemHistory->product->unit ? $redeemHistory->product->unit->symbol : 'Unit' }}</td>
                                    <td>{{ showAmount($redeemHistory->amount) }}</td>
                                    <td>{{ showAmount($redeemHistory->charge) }}</td>
                                    <td>@php echo $redeemHistory->redeemData->statusBadge @endphp</td>
                                    <td>
                                        <button class="dashboard-table-btn detailsBtn" 
                                            data-product_name="{{ $redeemHistory->product ? $redeemHistory->product->name : 'N/A' }}"
                                            data-quantity="{{ showAmount($redeemHistory->quantity, 4, currencyFormat: false) }}"
                                            data-unit="{{ $redeemHistory->product && $redeemHistory->product->unit ? $redeemHistory->product->unit->symbol : 'Unit' }}"
                                            data-shipping_cost="{{ showAmount($redeemHistory->charge) }}"
                                            data-delivery_type="{{ $redeemHistory->redeemData->delivery_type }}"
                                            data-delivery_address="{{ $redeemHistory->redeemData->delivery_address }}"
                                            data-shipping_method="{{ $redeemHistory->redeemData->shippingMethod ? $redeemHistory->redeemData->shippingMethod->name : 'N/A' }}"
                                            data-distance="{{ $redeemHistory->redeemData->distance ? showAmount($redeemHistory->redeemData->distance, 2, currencyFormat: false) : 'N/A' }}">
                                            @lang('Details')
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <x-empty-card empty-message="No redeem history found" />
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
                {{ paginateLinks($redeemHistories) }}
            </div>
        </div>
    </div>

    @include($activeTemplate . 'user.redeem.details_modal')
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/40.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.redeem.form') }}" class="btn btn--orange btn--lg"> <i class="fas fa-truck"></i> @lang('Shipping and receiving')</a>
@endpush