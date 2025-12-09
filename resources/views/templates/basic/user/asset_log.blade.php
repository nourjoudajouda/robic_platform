@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card">
                <div class="dashboard-table">
                    <table class="table table--responsive--lg">
                        <thead>
                            <tr>
                                <th>@lang('Date & Time')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Quantity')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Charge')</th>
                                @if ($assetLogs->sum('vat') > 0)
                                    <th>@lang('Vat')</th>
                                @endif

                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assetLogs as $assetLog)
                                <tr>
                                    <td>
                                        <div>
                                            <span class="d-block">{{ showDateTime($assetLog->created_at, 'd M, Y') }}</span>
                                            <span class="d-block">{{ showDateTime($assetLog->created_at, 'h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td>@php echo $assetLog->statusBadge @endphp</td>
                                    <td>{{ $assetLog->category->name }}</td>
                                    <td>{{ showAmount($assetLog->quantity, 4, currencyFormat: false) }} @lang('gram')</td>
                                    <td>{{ showAmount($assetLog->amount) }}</td>
                                    <td>{{ showAmount($assetLog->charge) }}</td>
                                    @if ($assetLogs->sum('vat') > 0)
                                        <td>{{ $assetLog->type == Status::BUY_HISTORY ? showAmount($assetLog->vat) : '-' }}</td>
                                    @endif
                                    <td>
                                        @if ($assetLog->type == Status::REDEEM_HISTORY)
                                            <button class="dashboard-table-btn detailsBtn" data-order_details='@json($assetLog->redeemData->order_details->items)' data-pickup_point='@json($assetLog->redeemData->pickupPoint)' data-address="{{ $assetLog->redeemData->delivery_address }}">@lang('Details')</button>
                                        @else
                                            <button class="dashboard-table-btn disabled" disabled>@lang('Details')</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <x-empty-card empty-message="No asset log found" />
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
                {{ paginateLinks($assetLogs) }}
            </div>
        </div>
    </div>

    @include($activeTemplate . 'user.redeem.details_modal')
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/31.png') }}" alt="image">
@endsection


@push('pageHeaderButton')
    <a href="{{ route('user.portfolio') }}" class="btn btn--success btn--lg"><i class="fas fa-chart-pie"></i> @lang('My Portfolio')</a>
@endpush
