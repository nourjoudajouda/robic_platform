@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-8">
            <div class="dashboard-card">
                <div class="account-setting">
                    <form method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="account-setting__top">
                            <span class="account-setting__thumb">
                                <img id="preview-image" src="{{ getImage(getFilePath('userProfile') . '/' . $user->image, getFileSize('userProfile')) }}" alt="image">
                            </span>
                            <div class="account-setting__content">
                                <label for="change-profile-picture" class="btn btn--base">
                                    <span>@lang('Change Picture')</span>
                                    <input type="file" name="image" id="change-profile-picture" accept=".png, .jpg, .jpeg">
                                </label>
                                <button type="button" class="btn btn--light confirmationBtn" data-action="{{ route('user.profile.picture.delete') }}" data-question="@lang('Are you sure you want to delete your profile picture?')">@lang('Delete Picture')</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="form--label">@lang('First Name')</label>
                                    <input type="text" class="form--control" name="firstname" value="{{ $user->firstname }}" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="form--label">@lang('Last Name')</label>
                                    <input type="text" class="form--control" name="lastname" value="{{ $user->lastname }}" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon">
                                    <label for="" class="form--label">@lang('Email Address')</label>
                                    <input type="email"class="form--control" value="{{ $user->email }}" readonly>
                                    <span class="icon"><i class="fa-regular fa-envelope"></i></span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group has-icon">
                                    <label for="" class="form--label">@lang('Mobile Number')</label>
                                    <input type="number" n class="form--control" value="{{ $user->mobile }}" readonly>
                                    <span class="icon"><i class="fa-solid fa-mobile-screen"></i></span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group">
                                    <label for="" class="form--label">@lang('Address')</label>
                                    <input type="text" name="address" class="form--control" value="{{ $user->address }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="form--label">@lang('City')</label>
                                    <input type="text" name="city" class="form--control" value="{{ $user->city }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="form--label">@lang('State')</label>
                                    <input type="text" name="state" class="form--control" value="{{ $user->state }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="form--label">@lang('Zip')</label>
                                    <input type="text" name="zip" class="form--control" value="{{ $user->zip }}">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="" class="form--label">@lang('Country')</label>
                                    <input type="text" class="form--control" value="{{ $user->country_name }}" readonly>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <button type="submit" class="btn btn--base w-100">@lang('Submit')</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/38.png') }}" alt="image">
@endsection


@push('script')
    <script>
        $(document).ready(function() {
            $('#change-profile-picture').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview-image').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush
