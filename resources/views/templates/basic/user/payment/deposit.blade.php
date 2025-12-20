@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-12 col-lg-11">
            <div class="row g-4">
                {{-- Left: Bank transfer details --}}
                <div class="col-lg-6">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card__top">
                            <h4 class="dashboard-card__title">
                                <i class="las la-university me-1"></i> {{ __('Bank Transfer Details') }}
                            </h4>
                        </div>
                        <div class="dashboard-card__body">
                            @if(!empty($bankTransfer))
                                <div class="row g-3">
                                    @if(!empty($bankTransfer['bank_name']))
                                        <div class="col-md-6">
                                            <label class="form--label mb-1">{{ __('Bank Name') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form--control" value="{{ $bankTransfer['bank_name'] }}" readonly>
                                                <button type="button" class="input-group-text copyInput" data-copy-value="{{ $bankTransfer['bank_name'] }}" title="{{ __('Copy') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($bankTransfer['account_name']))
                                        <div class="col-md-6">
                                            <label class="form--label mb-1">{{ __('Account Name') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form--control" value="{{ $bankTransfer['account_name'] }}" readonly>
                                                <button type="button" class="input-group-text copyInput" data-copy-value="{{ $bankTransfer['account_name'] }}" title="{{ __('Copy') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($bankTransfer['account_number']))
                                        <div class="col-md-6">
                                            <label class="form--label mb-1">{{ __('Account Number') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form--control" value="{{ $bankTransfer['account_number'] }}" readonly>
                                                <button type="button" class="input-group-text copyInput" data-copy-value="{{ $bankTransfer['account_number'] }}" title="{{ __('Copy') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($bankTransfer['iban']))
                                        <div class="col-md-6">
                                            <label class="form--label mb-1">{{ __('IBAN') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form--control" value="{{ $bankTransfer['iban'] }}" readonly>
                                                <button type="button" class="input-group-text copyInput" data-copy-value="{{ $bankTransfer['iban'] }}" title="{{ __('Copy') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($bankTransfer['swift']))
                                        <div class="col-md-6">
                                            <label class="form--label mb-1">{{ __('SWIFT') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form--control" value="{{ $bankTransfer['swift'] }}" readonly>
                                                <button type="button" class="input-group-text copyInput" data-copy-value="{{ $bankTransfer['swift'] }}" title="{{ __('Copy') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($bankTransfer['currency']))
                                        <div class="col-md-6">
                                            <label class="form--label mb-1">{{ __('Currency') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control form--control" value="{{ $bankTransfer['currency'] }}" readonly>
                                                <button type="button" class="input-group-text copyInput" data-copy-value="{{ $bankTransfer['currency'] }}" title="{{ __('Copy') }}">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if(!empty($bankTransfer['reference_hint']))
                                <div class="mt-3">
                                    <div class="alert alert--warning mb-0">
                                        <i class="las la-info-circle me-1"></i>
                                        {{ __($bankTransfer['reference_hint']) }}
                                    </div>
                                </div>
                            @endif

                            @if(!empty($depositInstructions) && is_array($depositInstructions))
                                <div class="mt-3">
                                    <h6 class="mb-2" style="color: hsl(var(--heading-color));">
                                        <i class="las la-clipboard-list me-1"></i> {{ __('Instructions') }}
                                    </h6>
                                    <ul class="mb-0" style="padding-left: 18px;">
                                        @foreach($depositInstructions as $line)
                                            <li style="color: hsl(var(--body-color)); margin-bottom: 6px;">{{ __($line) }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Right: Deposit form --}}
                <div class="col-lg-6">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card__top">
                            <h4 class="dashboard-card__title">@lang('Deposit Balance')</h4>
                        </div>
                        <div class="dashboard-card__body">
                            <form action="{{ route('user.deposit.insert') }}" method="post" enctype="multipart/form-data">
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
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>@lang('Transfer Receipt Image') <span class="text--danger">*</span></label>
                                            <input type="file" name="transfer_image" class="form-control form--control" accept="image/*" required>
                                            <small class="form-text text-muted">@lang('Upload a clear image of your bank transfer receipt')</small>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>@lang('Description')</label>
                                            <textarea name="description" class="form-control form--control" rows="4" placeholder="@lang('Optional: Add any additional notes or information about this deposit')">{{ old('description') }}</textarea>
                                            <small class="form-text text-muted">@lang('You can add any additional information or notes about this deposit')</small>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="alert alert--info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            @lang('After uploading the transfer receipt, your request will be reviewed by the admin. You will be notified once your deposit is approved.')
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn--base w-100">
                                        <i class="fas fa-upload me-2"></i> @lang('Submit Deposit Request')
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/39.png') }}" alt="image">
@endsection

@push('pageHeaderButton')
    <a href="{{ route('user.deposit.history') }}" class="btn btn--base btn--lg"> 
        <i class="las la-history"></i> @lang('Deposit History')
    </a>
@endpush

@push('script')
    <script>
        (function($){
            "use strict";
            $('.copyInput').on('click', async function () {
                const value = $(this).data('copy-value') ?? '';
                if (!value) return;

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(String(value));
                    } else {
                        const $temp = $('<textarea>');
                        $('body').append($temp);
                        $temp.val(String(value)).select();
                        document.execCommand('copy');
                        $temp.remove();
                    }

                    const $btn = $(this);
                    const $icon = $btn.find('i');
                    const oldClass = $icon.attr('class');
                    $icon.attr('class', 'fas fa-check');
                    setTimeout(() => $icon.attr('class', oldClass), 1200);
                } catch (e) {
                    // ignore
                }
            });
        })(jQuery);
    </script>
@endpush
