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
                                <th>@lang('User')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Quantity')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Charge')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($giftHistories as $giftHistory)
                                <tr>
                                    <td>{{ showDateTime($giftHistory->created_at) }}</td>
                                    <td>{{ $giftHistory->recipient->username }}</td>
                                    <td>{{ $giftHistory->category->name }}</td>
                                    <td>{{ showAmount($giftHistory->quantity, 4, currencyFormat: false) }} {{ $giftHistory->batch && $giftHistory->batch->product && $giftHistory->batch->product->unit ? $giftHistory->batch->product->unit->symbol : 'Unit' }}</td>
                                    <td>{{ showAmount($giftHistory->amount) }}</td>
                                    <td>{{ showAmount($giftHistory->charge) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <x-empty-card empty-message="No gift history found" />
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
                {{ paginateLinks($giftHistories) }}
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/35.png') }}" alt="image">
@endsection


@push('pageHeaderButton')
    <a href="{{ route('user.gift.form') }}" class="btn btn--warning btn--lg"> <i class="fas fa-gift"></i> @lang('Gift Green Coffee')</a>
@endpush
