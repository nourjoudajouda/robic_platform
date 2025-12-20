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
                                <th>@lang('Category')</th>
                                <th>@lang('Quantity')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Charge')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($redeemHistories as $redeemHistory)
                                <tr>
                                    <td>{{ showDateTime($redeemHistory->created_at) }}</td>
                                    <td>{{ $redeemHistory->category->name }}</td>
                                    <td>{{ showAmount($redeemHistory->quantity, currencyFormat: false) }} {{ $redeemHistory->batch && $redeemHistory->batch->product && $redeemHistory->batch->product->unit ? $redeemHistory->batch->product->unit->symbol : 'Unit' }}</td>
                                    <td>{{ showAmount($redeemHistory->amount) }}</td>
                                    <td>{{ showAmount($redeemHistory->charge) }}</td>
                                    <td>@php echo $redeemHistory->redeemData->statusBadge @endphp</td>
                                    <td>
                                        <button class="dashboard-table-btn detailsBtn" data-order_details='@json($redeemHistory->redeemData->order_details->items)' data-pickup_point='@json($redeemHistory->redeemData->pickupPoint)' data-address="{{ $redeemHistory->redeemData->delivery_address }}">@lang('Details')</button>
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
    <a href="{{ route('user.redeem.form') }}" class="btn btn--orange btn--lg"> <i class="fas fa-truck"></i> @lang('Redeem Green Coffee')</a>
@endpush