@extends('admin.layouts.app')

@php
    use App\Constants\Status;
@endphp

@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="row gy-4">
                <div class="col-xxl-3 col-sm-6">
                    <x-widget style="7" link="{{ route('admin.report.transaction', $user->id) }}" title="Balance" icon="las la-money-bill-wave-alt" value="{{ showAmount($user->balance) }}" bg="indigo" type="2" />
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <x-widget style="7" link="{{ route('admin.bean.history.buy', $user->id) }}" title="Asset Value" icon="las la-coins" value="{{ showAmount($totalAssetAmount ?? 0) }}" bg="info" type="2" />
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <x-widget style="7" link="{{ route('admin.deposit.list', $user->id) }}" title="Deposits" icon="las la-wallet" value="{{ showAmount($totalDeposit ?? 0) }}" bg="8" type="2" />
                </div>
                <div class="col-xxl-3 col-sm-6">
                    <x-widget style="7" link="{{ route('admin.withdraw.data.all', $user->id) }}" title="Withdrawals" icon="la la-bank" value="{{ showAmount($totalWithdrawals ?? 0) }}" bg="6" type="2" />
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 mt-4">
                @if($user->status == Status::USER_BAN)
                    <div class="flex-fill">
                        <button type="button" class="btn btn--success btn--shadow w-100 btn-lg confirmationBtn" 
                            data-action="{{ route('admin.users.establishment.approve', $user->id) }}" 
                            data-question="@lang('Are you sure to approve this establishment registration?')">
                            <i class="las la-check"></i> @lang('Approve Registration')
                        </button>
                    </div>
                    <div class="flex-fill">
                        <button type="button" class="btn btn--danger btn--shadow w-100 btn-lg" 
                            data-bs-toggle="modal" 
                            data-bs-target="#rejectModal">
                            <i class="las la-times"></i> @lang('Reject Registration')
                        </button>
                    </div>
                @endif
                <div class="flex-fill">
                    <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn--primary btn--shadow w-100 btn-lg">
                        <i class="las la-user"></i> @lang('User Profile')
                    </a>
                </div>
                <div class="flex-fill">
                    <a href="{{ route('admin.report.login.history') }}?search={{ $user->username }}" class="btn btn--primary btn--shadow w-100 btn-lg">
                        <i class="las la-list-alt"></i>@lang('Logins')
                    </a>
                </div>
                <div class="flex-fill">
                    <a href="{{ route('admin.users.notification.log', $user->id) }}" class="btn btn--secondary btn--shadow w-100 btn-lg">
                        <i class="las la-bell"></i>@lang('Notifications')
                    </a>
                </div>
            </div>

            @if($user->status == Status::USER_BAN)
                <!-- Reject Modal -->
                <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
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
            @endif

            <!-- Establishment Information Card -->
            <div class="card mt-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Establishment Information')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Establishment Name')</label>
                                <input class="form-control" type="text" value="{{ $user->establishment_name ?? '-' }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('User Type')</label>
                                <input class="form-control" type="text" value="{{ ucfirst($user->user_type ?? 'individual') }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Establishment Information')</label>
                                <textarea class="form-control" rows="4" readonly>{{ $user->establishment_info ?? '-' }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Commercial Registration')</label>
                                <div class="mt-2">
                                    @if($user->commercial_registration)
                                        <div class="d-flex align-items-center gap-3">
                                            <a href="{{ asset('users/' . $user->commercial_registration) }}" target="_blank" class="btn btn--primary">
                                                <i class="las la-file-pdf"></i> @lang('View Commercial Registration')
                                            </a>
                                            <a href="{{ asset('users/' . $user->commercial_registration) }}" download class="btn btn--success">
                                                <i class="las la-download"></i> @lang('Download')
                                            </a>
                                        </div>
                                        <small class="text-muted mt-2 d-block">@lang('File uploaded on'): {{ showDateTime($user->updated_at) }}</small>
                                    @else
                                        <span class="badge bg--danger">@lang('Not Uploaded')</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Basic Information Card -->
            <div class="card mt-30">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('User Basic Information')</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('First Name')</label>
                                <input class="form-control" type="text" value="{{ $user->firstname }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Last Name')</label>
                                <input class="form-control" type="text" value="{{ $user->lastname }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Username')</label>
                                <input class="form-control" type="text" value="{{ $user->username }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Email')</label>
                                <input class="form-control" type="email" value="{{ $user->email }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Mobile')</label>
                                <input class="form-control" type="text" value="{{ $user->mobileNumber }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Country')</label>
                                <input class="form-control" type="text" value="{{ $user->country_name ?? '-' }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('City')</label>
                                <input class="form-control" type="text" value="{{ $user->city ?? '-' }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Address')</label>
                                <input class="form-control" type="text" value="{{ $user->address ?? '-' }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Joined At')</label>
                                <input class="form-control" type="text" value="{{ showDateTime($user->created_at) }}" readonly>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Status')</label>
                                <input class="form-control" type="text" value="{{ $user->status == Status::USER_ACTIVE ? 'Active' : 'Banned' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.users.establishments') }}" class="btn btn-outline--primary">
        <i class="las la-arrow-left"></i> @lang('Back to Establishments')
    </a>
@endpush

@if($user->status == Status::USER_BAN)
    <x-confirmation-modal />
@endif

