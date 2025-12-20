@extends('admin.layouts.app')

@php
    use App\Constants\Status;
@endphp

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
                                    <th>@lang('Slug')</th>
                                    <th>@lang('Permissions')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $role->id }}</td>
                                        <td>{{ __($role->name) }}</td>
                                        <td><code>{{ $role->slug }}</code></td>
                                        <td>
                                            <span class="badge badge--primary">{{ $role->permissions->count() }} @lang('Permissions')</span>
                                        </td>
                                        <td>
                                            @if($role->status == Status::ENABLE)
                                                <span class="badge badge--success">@lang('Active')</span>
                                            @else
                                                <span class="badge badge--danger">@lang('Inactive')</span>
                                            @endif
                                        </td>
                                        <td>{{ showDateTime($role->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.role.edit', $role->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>
                                                @if($role->slug !== 'super_admin')
                                                    <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                        data-question="@lang('Are you sure to delete this role?')"
                                                        data-action="{{ route('admin.role.delete', $role->id) }}">
                                                        <i class="las la-trash"></i> @lang('Delete')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __('No roles found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
                @if ($roles->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($roles) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.role.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i> @lang('Add New Role')
    </a>
    <a href="{{ route('admin.admin-role.index') }}" class="btn btn-outline--info">
        <i class="las la-user-shield"></i> @lang('Assign Roles to Admins')
    </a>
    <x-search-form placeholder="Search by Name / Slug" />
@endpush

