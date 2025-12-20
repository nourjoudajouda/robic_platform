@extends($activeTemplate . 'layouts.master')
@section('content')
    <div class="row justify-content-center">
        <div class="col-xxl-10">
            <div class="row justify-content-end">
                <div class="col-lg-4">
                    <form>
                        <div class="mb-3 d-flex justify-content-end">
                            <div class="input-group">
                                <input type="search" name="search" class="form-control form--control" value="{{ request()->search }}" placeholder="@lang('Search by transactions')">
                                <button class="input-group-text bg--base text-white">
                                    <i class="las la-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xxl-10">
            <div class="dashboard-card">
                <div class="dashboard-table">
                    <table class="table table--responsive--lg">
                        <thead>
                            <tr>
                                <th>@lang('Gateway | Transaction')</th>
                                <th class="text-center">@lang('Initiated')</th>
                                <th class="text-center">@lang('Amount')</th>
                                <th class="text-center">@lang('Conversion')</th>
                                <th class="text-center">@lang('Status')</th>
                                <th>@lang('Details')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits as $deposit)
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            <div>
                                                <span class="text-primary">
                                                    @if ($deposit->method_code < 5000)
                                                        {{ __(@$deposit->gateway->name) }}
                                                    @else
                                                        @lang('Google Pay')
                                                    @endif
                                                </span>
                                            </div>
                                            </span>
                                            <br>
                                            <small> {{ $deposit->trx }} </small>
                                    </td>

                                    <td>
                                        {{ showDateTime($deposit->created_at) }}<br>{{ diffForHumans($deposit->created_at) }}
                                    </td>
                                    <td>
                                        <div>
                                            {{ showAmount($deposit->amount) }} + <span class="text--danger" data-bs-toggle="tooltip" title="@lang('Processing Charge')">{{ showAmount($deposit->charge) }} </span>
                                            <br>
                                            <strong data-bs-toggle="tooltip" title="@lang('Amount with charge')">
                                                {{ showAmount($deposit->amount + $deposit->charge) }}
                                            </strong>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            {{ showAmount(1) }} = {{ showAmount($deposit->rate, currencyFormat: false) }} {{ __($deposit->method_currency) }}
                                            <br>
                                            <strong>{{ showAmount($deposit->final_amount, currencyFormat: false) }} {{ __($deposit->method_currency) }}</strong>
                                        </div>
                                    </td>
                                    <td>
                                        @php echo $deposit->statusBadge @endphp
                                    </td>
                                    @php
                                        $details = [];
                                        if ($deposit->method_code >= 1000 && $deposit->method_code <= 5000) {
                                            foreach (@$deposit->detail ?? [] as $key => $info) {
                                                $details[] = $info;
                                                if ($info->type == 'file') {
                                                    $details[$key]->value = route('user.download.attachment', encrypt(getFilePath('verify') . '/' . $info->value));
                                                }
                                            }
                                        }
                                    @endphp

                                    <td>
                                        @if ($deposit->method_code >= 1000 && $deposit->method_code <= 5000)
                                            <a href="javascript:void(0)" class="dashboard-table-btn detailBtn" 
                                                data-info="{{ !empty($details) ? json_encode($details) : '[]' }}" 
                                                @if ($deposit->status == Status::PAYMENT_REJECT && $deposit->admin_feedback) data-admin-feedback="{{ htmlspecialchars($deposit->admin_feedback, ENT_QUOTES, 'UTF-8') }}" @endif
                                                @if ($deposit->transfer_image) data-transfer-image="{{ asset('transfers/' . $deposit->transfer_image) }}" @endif
                                                @if ($deposit->description) data-description="{{ htmlspecialchars($deposit->description, ENT_QUOTES, 'UTF-8') }}" @endif>
                                                @lang('Details')
                                            </a>
                                        @else
                                            <button type="button" class="dashboard-table-btn" data-bs-toggle="tooltip" title="@lang('Automatically processed')">
                                                @lang('Automated')
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%" class="text-center">
                                        <x-empty-card empty-message="No deposit found" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xxl-10">
            <div class="pagination-wrapper">
                {{ paginateLinks($deposits) }}
            </div>
        </div>
    </div>


    {{-- APPROVE MODAL --}}
    <div id="detailModal" class="modal custom--modal fade" tabindex="-1" role="dialog" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Details')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group userData">
                    </ul>
                    <div class="feedback"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--danger btn--sm" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageTitleIcon')
    <img src="{{ asset($activeTemplateTrue . 'images/icons/39.png') }}" alt="image">
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                // Remove any existing backdrop
                function removeBackdrops() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css('overflow', '');
                    $('body').css('padding-right', '');
                }
                
                $('.detailBtn').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Remove any existing backdrops first
                    removeBackdrops();
                    
                    try {
                        var modalElement = document.getElementById('detailModal');
                        if (!modalElement) {
                            console.error('Modal element not found');
                            return;
                        }
                        
                        var $modal = $(modalElement);

                var userData = $(this).data('info');
                var html = '';
                        
                        // Parse JSON if it's a string
                        if (typeof userData === 'string') {
                            try {
                                userData = JSON.parse(userData);
                            } catch (e) {
                                console.warn('Failed to parse userData:', e);
                                userData = [];
                            }
                        }
                        
                        if (userData && Array.isArray(userData) && userData.length > 0) {
                            userData.forEach(function(element) {
                                if (element && element.type != 'file') {
                                    html += '<li class="list-group-item d-flex justify-content-between align-items-center px-2">' +
                                        '<span>' + (element.name || '') + '</span>' +
                                        '<span>' + (element.value || '') + '</span>' +
                                        '</li>';
                                } else if (element && element.type == 'file') {
                                    html += '<li class="list-group-item d-flex justify-content-between align-items-center px-2">' +
                                        '<span>' + (element.name || '') + '</span>' +
                                        '<span><a href="' + (element.value || '#') + '" target="_blank"><i class="fa-regular fa-file"></i> @lang("Attachment")</a></span>' +
                                        '</li>';
                        }
                    });
                }

                        $modal.find('.userData').html(html || '<li class="list-group-item">@lang("No additional details available")</li>');

                        var feedbackHtml = '';
                        var transferImage = $(this).data('transfer-image') || $(this).data('transfer_image');
                        var adminFeedback = $(this).data('admin-feedback') || $(this).data('admin_feedback');
                        
                        if (transferImage) {
                            feedbackHtml += '<div class="my-3">' +
                                '<strong>@lang("Transfer Receipt")</strong>' +
                                '<br>' +
                                '<img src="' + transferImage + '" alt="Transfer Receipt" class="img-fluid mt-2" style="max-width: 100%; border: 1px solid #ddd; border-radius: 8px;" onerror="this.style.display=\'none\'">' +
                                '</div>';
                        }

                        if (adminFeedback) {
                            feedbackHtml += '<div class="my-3">' +
                                '<strong>@lang("Admin Feedback")</strong>' +
                                '<p>' + adminFeedback + '</p>' +
                                '</div>';
                        } else if (!transferImage) {
                            feedbackHtml = '<p class="text-muted">@lang("Waiting for admin approval")</p>';
                }

                        $modal.find('.feedback').html(feedbackHtml);

                        // Use Bootstrap 5 modal
                        var modal = new bootstrap.Modal(modalElement, {
                            backdrop: true,
                            keyboard: true,
                            focus: true
                        });
                        
                        // Clean up on hide
                        modalElement.addEventListener('hidden.bs.modal', function() {
                            removeBackdrops();
                        }, { once: true });
                        
                        // Show modal
                        modal.show();
                    } catch (error) {
                        console.error('Error opening modal:', error);
                        removeBackdrops();
                        alert('@lang("An error occurred while loading details. Please try again.")');
                    }
                });
                
                // Close modal on backdrop click
                $(document).on('click', '.modal-backdrop', function() {
                    $('#detailModal').modal('hide');
                    removeBackdrops();
                });

                // Initialize tooltips
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title], [data-title], [data-bs-title]'));
                tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                    try {
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    } catch (e) {
                        console.warn('Tooltip initialization failed:', e);
                    }
                });
            });
        })(jQuery);
    </script>
@endpush
