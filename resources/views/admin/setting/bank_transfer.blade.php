@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.setting.bank.transfer.update') }}">
                        @csrf
                        
                        <h5 class="card-title mb-4">@lang('Bank Transfer Details')</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Bank Name') <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="bank_name" value="{{ $bankTransfer['bank_name'] ?? '' }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Account Name') <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="account_name" value="{{ $bankTransfer['account_name'] ?? '' }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Account Number') <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="account_number" value="{{ $bankTransfer['account_number'] ?? '' }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('IBAN')</label>
                                    <input type="text" class="form-control" name="iban" value="{{ $bankTransfer['iban'] ?? '' }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('SWIFT')</label>
                                    <input type="text" class="form-control" name="swift" value="{{ $bankTransfer['swift'] ?? '' }}">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Currency') <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="currency" value="{{ $bankTransfer['currency'] ?? 'SAR' }}" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">@lang('Reference Hint')</label>
                                    <textarea class="form-control" name="reference_hint" rows="2">{{ $bankTransfer['reference_hint'] ?? '' }}</textarea>
                                    <small class="form-text text-muted">@lang('This message will be shown as a warning/hint to users when they make a deposit')</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="card-title mb-4">@lang('Deposit Instructions')</h5>
                        
                        <div class="instructions-wrapper">
                            @if(!empty($depositInstructions) && is_array($depositInstructions))
                                @foreach($depositInstructions as $index => $instruction)
                                    <div class="form-group instruction-row" data-index="{{ $index }}">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="instructions[]" value="{{ $instruction }}" placeholder="@lang('Instruction')">
                                            @if($index > 0)
                                                <button type="button" class="btn btn-danger remove-instruction">
                                                    <i class="las la-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="form-group instruction-row" data-index="0">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="instructions[]" value="" placeholder="@lang('Instruction')">
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <button type="button" class="btn btn--info btn-sm mt-2" id="add-instruction">
                            <i class="las la-plus"></i> @lang('Add Instruction')
                        </button>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    (function($) {
        "use strict";
        
        let instructionIndex = {{ !empty($depositInstructions) && is_array($depositInstructions) ? count($depositInstructions) : 1 }};
        
        // Add new instruction
        $(document).on('click', '#add-instruction', function() {
            const html = `
                <div class="form-group instruction-row" data-index="${instructionIndex}">
                    <div class="input-group">
                        <input type="text" class="form-control" name="instructions[]" value="" placeholder="@lang('Instruction')">
                        <button type="button" class="btn btn-danger remove-instruction">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('.instructions-wrapper').append(html);
            instructionIndex++;
        });
        
        // Remove instruction
        $(document).on('click', '.remove-instruction', function() {
            $(this).closest('.instruction-row').remove();
        });
    })(jQuery);
</script>
@endpush


