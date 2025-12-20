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
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#assignRoleModal{{ $admin->id }}">
                                                <i class="la la-user-shield"></i> @lang('Assign Roles')
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal for Assigning Roles -->
                                    <div class="modal fade" id="assignRoleModal{{ $admin->id }}" tabindex="-1" aria-labelledby="assignRoleModalLabel{{ $admin->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.admin-role.assign', $admin->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="assignRoleModalLabel{{ $admin->id }}">@lang('Assign Roles to') {{ $admin->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>@lang('Select Roles')</label>
                                                            @foreach($roles as $role)
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input" type="checkbox" name="roles[]" 
                                                                        value="{{ $role->id }}" id="role_{{ $admin->id }}_{{ $role->id }}"
                                                                        {{ $admin->roles->contains($role->id) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="role_{{ $admin->id }}_{{ $role->id }}">
                                                                        {{ $role->name }}
                                                                        <small class="text-muted d-block">{{ $role->description }}</small>
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
                                                        <button type="submit" class="btn btn--primary">@lang('Save Changes')</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.role.index') }}" class="btn btn-outline--info">
        <i class="las la-shield-alt"></i> @lang('Manage Roles')
    </a>
    <x-search-form placeholder="Search by Name / Email / Username" />
@endpush

