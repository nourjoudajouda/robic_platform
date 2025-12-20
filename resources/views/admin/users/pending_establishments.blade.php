@extends('admin.layouts.app')

@php
    use App\Constants\Status;
@endphp

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--md  table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                            <tr>
                                <th>@lang('ID')</th>
                                <th>@lang('Establishment Name')</th>
                                <th>@lang('Email-Mobile')</th>
                                <th>@lang('Country')</th>
                                <th>@lang('Commercial Registration')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Joined At')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <span class="fw-bold">{{ $user->establishment_name ?? $user->fullname }}</span>
                                    <br>
                                    <span class="small">
                                    <a href="{{ route('admin.users.establishment.detail', $user->id) }}"><span>@</span>{{ $user->username }}</a>
                                    </span>
                                </td>

                                <td>
                                    {{ $user->email }}<br>{{ $user->mobileNumber }}
                                </td>
                                <td>
                                    <span class="fw-bold" title="{{ @$user->country_name }}">{{ $user->country_code }}</span>
                                </td>

                                <td>
                                    @if($user->commercial_registration)
                                        <span class="badge bg--success">@lang('Uploaded')</span>
                                    @else
                                        <span class="badge bg--danger">@lang('Not Uploaded')</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg--warning">@lang('Pending Approval')</span>
                                </td>

                                <td>
                                    {{ showDateTime($user->created_at) }} <br> {{ diffForHumans($user->created_at) }}
                                </td>

                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.users.establishment.detail', $user->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-desktop"></i> @lang('View Details')
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline--success ms-1 confirmationBtn" 
                                            data-action="{{ route('admin.users.establishment.approve', $user->id) }}" 
                                            data-question="@lang('Are you sure to approve this establishment registration?')">
                                            <i class="las la-check"></i> @lang('Approve')
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline--danger ms-1" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal{{ $user->id }}">
                                            <i class="las la-times"></i> @lang('Reject')
                                        </button>
                                    </div>
                                </td>

                            </tr>

                            <!-- Reject Modal -->
                            <div id="rejectModal{{ $user->id }}" class="modal fade" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">@lang('Reject Establishment Registration')</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                <i class="las la-times"></i>
                                            </button>
                                        </div>
                                        <form action="{{ route('admin.users.establishment.reject', $user->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>@lang('Rejection Reason') <span class="text--danger">*</span></label>
                                                    <textarea class="form-control" name="reason" rows="4" required placeholder="@lang('Enter rejection reason')"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                                                <button type="submit" class="btn btn--danger">@lang('Reject')</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No pending establishments found') }}</td>
                                </tr>
                            @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($users->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($users) }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.establishments') }}" class="btn btn-outline--primary">
        <i class="las la-list"></i> @lang('All Establishments')
    </a>
    <x-search-form placeholder="Username / Email / Establishment Name" />
@endpush

