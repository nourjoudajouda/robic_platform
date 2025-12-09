@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card h-auto">
                <div class="dashboard-table">
                    <table class="table table--responsive--sm">
                        <thead>
                            <tr>
                                <th>@lang('Date Time')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Quantity')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Charge')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellHistories as $sellHistory)
                                <tr>
                                    <td>{{ showDateTime($sellHistory->created_at) }}</td>
                                    <td>{{ $sellHistory->category->name }}</td>
                                    <td>{{ showAmount($sellHistory->quantity, 4, currencyFormat: false) }} @lang('gram')</td>
                                    <td>{{ showAmount($sellHistory->amount) }}</td>
                                    <td>{{ showAmount($sellHistory->charge) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <x-empty-card empty-message="No sell history found" />
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
                {{ paginateLinks($sellHistories) }}
            </div>
        </div>

    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/41.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.sell.form') }}" class="btn btn--danger btn--lg"> <i class="fas fa-money-bill-trend-up"></i> @lang('Sell Gold')</a>
@endpush
