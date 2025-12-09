@extends($activeTemplate.'layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">

                <div class="card custom--card">
                    <div class="card-header">
                        <h5 class="card-title">@lang('Change Password')</h5>
                    </div>
                    <div class="card-body">

                        <form method="post">
                            @csrf
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Current Password')</label>
                                        <input type="password" class="form--control" name="current_password" required autocomplete="current-password">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Password')</label>
                                        <input type="password" class="form--control @if(gs('secure_password')) secure-password @endif" name="password" required autocomplete="current-password">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Confirm Password')</label>
                                        <input type="password" class="form--control" name="password_confirmation" required autocomplete="current-password">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/38.png') }}" alt="image">
@endsection


@if(gs('secure_password'))
    @push('script-lib')
        <script src="{{ asset('assets/global/js/secure_password.js') }}"></script>
    @endpush
@endif
