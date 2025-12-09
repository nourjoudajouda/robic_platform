@extends($activeTemplate . 'layouts.master')

@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="dashboard-card no-overflow">
                <form action="{{ route('user.redeem.address.store') }}" method="POST" class="withdraw-form homeDeliveryForm mt-3">
                    @csrf
                    <input type="hidden" name="option" value="2">
                    <div class="row">
                        <div class="col-12">
                            <label class="label-title mb-3">@lang('Enter Your Address')</label>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form--control" name="address" placeholder="@lang('Address')">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form--control" name="city" placeholder="@lang('City')">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form--control" name="state" placeholder="@lang('State')">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" class="form--control" name="zip_code" placeholder="@lang('Zip Code')">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <button type="submit" class="btn btn--base w-100">Confirm</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/40.png') }}" alt="image">
@endsection