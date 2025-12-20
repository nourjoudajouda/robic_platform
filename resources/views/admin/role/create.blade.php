@extends('admin.layouts.app')

@php
    use App\Constants\Status;
@endphp

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.role.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Name') <span class="text--danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Slug') <span class="text--danger">*</span></label>
                                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}" required>
                                    <small class="form-text text-muted">@lang('Unique identifier for the role (e.g., warehouses_team)')</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Status') <span class="text--danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>@lang('Active')</option>
                                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>@lang('Inactive')</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="mb-3">@lang('Permissions')</h5>
                                <div class="form-group">
                                    <div class="row">
                                        @foreach($permissions as $group => $groupPermissions)
                                            <div class="col-md-6 mb-4">
                                                <div class="card">
                                                    <div class="card-header bg--primary">
                                                        <h6 class="mb-0 text-white">{{ ucfirst($group) }}</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        @foreach($groupPermissions as $permission)
                                                            <div class="form-check mb-2">
                                                                <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                                    value="{{ $permission->id }}" id="permission_{{ $permission->id }}"
                                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                                    {{ $permission->name }}
                                                                    <small class="text-muted d-block">{{ $permission->slug }}</small>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

