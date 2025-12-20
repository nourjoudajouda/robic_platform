@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('ID')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Email')</th>
                                    <th>@lang('Username')</th>
                                    <th>@lang('Roles')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admins as $admin)
                                    <tr>
                                        <td>{{ $admin->id }}</td>
                                        <td>{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>{{ $admin->username }}</td>
                                        <td>
                                            @forelse($admin->roles as $role)
                                                <span class="badge badge--primary">{{ $role->name }}</span>
                                            @empty
                                                <span class="badge badge--danger">@lang('No Roles')</span>
                                            @endforelse
                                        </td>
                                        <td>{{ showDateTime($admin->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.admin.edit', $admin->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>
                                                @if($admin->id != auth()->guard('admin')->id())
                                                    <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to delete this admin?')"
                                                        data-action="{{ route('admin.admin.delete', $admin->id) }}">
                                                        <i class="las la-trash"></i> @lang('Delete')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __('No admins found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($admins->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($admins) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.admin.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i> @lang('Add New Admin')
    </a>
    <x-search-form placeholder="Search by Name / Email / Username" />
@endpush

