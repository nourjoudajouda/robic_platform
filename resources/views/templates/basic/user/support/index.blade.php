@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card">
                <div class="dashboard-table">
                    <table class="table table--responsive--sm">
                        <thead>
                            <tr>
                                <th>@lang('Subject')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Priority')</th>
                                <th>@lang('Last Reply')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supports as $support)
                                <tr>
                                    <td> <a href="{{ route('ticket.view', $support->ticket) }}" class="fw-bold"> [@lang('Ticket')#{{ $support->ticket }}] {{ __($support->subject) }} </a></td>
                                    <td>
                                        @php echo $support->statusBadge; @endphp
                                    </td>
                                    <td>
                                        @if ($support->priority == Status::PRIORITY_LOW)
                                            <span class="badge badge--dark">@lang('Low')</span>
                                        @elseif($support->priority == Status::PRIORITY_MEDIUM)
                                            <span class="badge  badge--warning">@lang('Medium')</span>
                                        @elseif($support->priority == Status::PRIORITY_HIGH)
                                            <span class="badge badge--danger">@lang('High')</span>
                                        @endif
                                    </td>
                                    <td>{{ diffForHumans($support->last_reply) }} </td>

                                    <td>
                                        <a href="{{ route('ticket.view', $support->ticket) }}" class="dashboard-table-btn">
                                            @lang('View')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%">
                                        <x-empty-card empty-message="No support ticket found" />
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
                {{ paginateLinks($supports) }}
            </div>
        </div>

    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/42.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('ticket.open') }}" class="btn btn--base"> <i class="fas fa-plus"></i> @lang('New Ticket')</a>
@endpush
