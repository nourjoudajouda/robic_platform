@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.admin.store') }}" method="POST">
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
                                    <label>@lang('Email') <span class="text--danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Username') <span class="text--danger">*</span></label>
                                    <input type="text" name="username" class="form-control" value="{{ old('username') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Password') <span class="text--danger">*</span></label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Confirm Password') <span class="text--danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="mb-3">@lang('Assign Roles')</h5>
                                <div class="form-group">
                                    <div class="row">
                                        @foreach($roles as $role)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="roles[]" 
                                                        value="{{ $role->id }}" id="role_{{ $role->id }}"
                                                        {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                                        <strong>{{ $role->name }}</strong>
                                                        <small class="text-muted d-block">{{ $role->description }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="form-text text-muted mt-2">
                                        <i class="las la-info-circle"></i> @lang('Select the roles for this admin. For warehouses team, select "Warehouses Team" role.')
                                    </small>
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

