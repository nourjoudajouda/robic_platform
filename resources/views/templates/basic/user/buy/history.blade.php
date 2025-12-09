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
                                @if (count($buyHistories) > 0 && $vat > 0)
                                    <th>@lang('Vat')</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buyHistories as $buyHistory)
                                <tr>
                                    <td>{{ showDateTime($buyHistory->created_at) }}</td>
                                    <td>{{ $buyHistory->category->name }}</td>
                                    <td>{{ showAmount($buyHistory->quantity, 4, currencyFormat: false) }} @lang('gram')</td>
                                    <td>{{ showAmount($buyHistory->amount) }}</td>
                                    <td>{{ showAmount($buyHistory->charge) }}</td>
                                    @if (count($buyHistories) > 0 && $vat > 0)
                                        <td>{{ showAmount($buyHistory->vat) }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <x-empty-card empty-message="No buy history found" />
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
                {{ paginateLinks($buyHistories) }}
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/22.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.buy.form') }}" class="btn btn--success btn--lg"> <i class="fas fa-circle-dollar-to-slot"></i> @lang('Buy Gold')</a>
@endpush
