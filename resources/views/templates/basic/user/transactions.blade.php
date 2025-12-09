@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="show-filter mb-3 text-end">
                <button type="button" class="btn btn--base showFilterBtn btn-sm"><i class="las la-filter"></i> @lang('Filter')</button>
            </div>
            <div class="card custom--card responsive-filter-card mb-4 no-overflow">
                <div class="card-body">
                    <form>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="flex-grow-1">
                                <label class="form-label">@lang('Transaction Number')</label>
                                <input type="search" name="search" value="{{ request()->search }}" class="form--control">
                            </div>
                            <div class="flex-grow-1 select2-parent">
                                <label class="form-label d-block">@lang('Type')</label>
                                <div class="customNiceSelect">
                                    <select name="trx_type">
                                        <option value="">@lang('All')</option>
                                        <option value="+" @selected(request()->trx_type == '+')>@lang('Plus')</option>
                                        <option value="-" @selected(request()->trx_type == '-')>@lang('Minus')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex-grow-1 select2-parent">
                                <label class="form-label d-block">@lang('Remark')</label>
                                <div class="customNiceSelect">
                                    <select name="remark">
                                        <option value="">@lang('All')</option>
                                        @foreach ($remarks as $remark)
                                            <option value="{{ $remark->remark }}" @selected(request()->remark == $remark->remark)>{{ __(keyToTitle($remark->remark)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <button class="btn btn--base btn--lg w-100"><i class="las la-filter"></i> @lang('Filter')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-xxl-10">
            <div class="dashboard-card no-filter">
                <div class="dashboard-table">
                    <table class="table table--responsive--sm">
                        <thead>
                            <tr>
                                <th>@lang('Trx')</th>
                                <th>@lang('Transacted')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Post Balance')</th>
                                <th>@lang('Detail')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                <tr>
                                    <td>
                                        <strong>{{ $trx->trx }}</strong>
                                    </td>

                                    <td>
                                        {{ showDateTime($trx->created_at) }}<br>{{ diffForHumans($trx->created_at) }}
                                    </td>

                                    <td>
                                        <span class="fw-bold @if ($trx->trx_type == '+') text--success @else text--danger @endif">
                                            {{ $trx->trx_type }} {{ showAmount($trx->amount) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ showAmount($trx->post_balance) }}
                                    </td>

                                    <td>{{ __($trx->details) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-center" colspan="100%">
                                        <x-empty-card empty-message="No transaction found" />
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
                {{ paginateLinks($transactions) }}
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/43.png') }}" alt="image">
@endsection

@push('style')
    <style>
        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush
