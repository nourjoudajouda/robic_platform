@extends('admin.layouts.app')
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
                                <th>@lang('Joined At')</th>
                                <th>@lang('Balance')</th>
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
                                    {{ showDateTime($user->created_at) }} <br> {{ diffForHumans($user->created_at) }}
                                </td>

                                <td>
                                    <span class="fw-bold">
                                    {{ showAmount($user->balance) }}
                                    </span>
                                </td>

                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.users.establishment.detail', $user->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="las la-desktop"></i> @lang('View Details')
                                        </a>
                                    </div>
                                </td>

                            </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No establishments found') }}</td>
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
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Username / Email / Establishment Name" />
@endpush

