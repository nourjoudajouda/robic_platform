@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <div class="dashboard-card__top">
                    <h4 class="dashboard-card__title">@lang('Withdraw Money')</h4>
                </div>
                <div class="dashboard-card__body">
                    <form action="{{ route('user.withdraw.money') }}" method="post">
                @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Amount') <span class="text--danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ gs('cur_sym') }}</span>
                                        <input type="number" step="0.01" name="amount" class="form-control form--control" 
                                            placeholder="0.00" value="{{ old('amount') }}" required>
                                    </div>
                                    <small class="form-text text-muted">@lang('Enter the amount you want to withdraw')</small>
                                </div>
                                    </div>
                            <div class="col-md-12">
                                <div class="alert alert--info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    @lang('Your withdrawal request will be reviewed by the admin. You will be notified once your withdrawal is approved and processed.')
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn--base w-100">
                                <i class="fas fa-paper-plane me-2"></i> @lang('Submit Withdrawal Request')
                            </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/44.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.withdraw.history') }}" class="btn btn--base btn--lg"> 
        <i class="las la-history"></i> @lang('Withdraw History')
    </a>
@endpush
