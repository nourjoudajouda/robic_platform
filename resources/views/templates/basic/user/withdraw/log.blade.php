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
                    <table class="table table--responsive--sm">
                        <thead>
                            <tr>
                                <th>@lang('Gateway | Transaction')</th>
                                <th class="text-center">@lang('Initiated')</th>
                                <th class="text-center">@lang('Amount')</th>
                                <th class="text-center">@lang('Conversion')</th>
                                <th class="text-center">@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
    
                            @forelse($withdraws as $withdraw)
                                @php
                                    $details = [];
                                    if ($withdraw->withdraw_information && is_object($withdraw->withdraw_information)) {
                                    foreach ($withdraw->withdraw_information as $key => $info) {
                                        $details[] = $info;
                                            if (isset($info->type) && $info->type == 'file') {
                                            $details[$key]->value = route('user.download.attachment', encrypt(getFilePath('verify') . '/' . $info->value));
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div>
                                            <span class="fw-bold"><span class="text-primary">@lang('Bank Transfer')</span></span>
                                            <br>
                                            <small>{{ $withdraw->trx }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        {{ showDateTime($withdraw->created_at) }} <br> {{ diffForHumans($withdraw->created_at) }}
                                    </td>
                                    <td>
                                        <div>
                                            {{ showAmount($withdraw->amount) }}
                                        </div>
    
                                    </td>
                                    <td>
                                        <div>
                                            {{ showAmount($withdraw->amount, currencyFormat: false) }} {{ __($withdraw->currency) }}
                                        </div>
                                    </td>
                                    <td>
                                        @php echo $withdraw->statusBadge @endphp
                                    </td>
                                    <td>
                                        <button class="dashboard-table-btn detailBtn" 
                                            data-user_data="{{ json_encode($details) }}" 
                                            data-transfer_image="{{ $withdraw->transfer_image ?? '' }}"
                                            data-admin_feedback="{{ $withdraw->admin_feedback ?? '' }}"
                                            @if ($withdraw->status == Status::PAYMENT_REJECT) 
                                                data-rejected="true"
                                            @endif
                                            @if ($withdraw->status == Status::PAYMENT_SUCCESS && $withdraw->transfer_image) 
                                                data-approved="true"
                                            @endif>
                                            @lang('Details')
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">
                                        <x-empty-card empty-message="No withdraw found" />
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
                {{ paginateLinks($withdraws) }}
            </div>
        </div>
    </div>


    {{-- DETAIL MODAL --}}
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
    <img src="{{ asset($activeTemplateTrue . 'images/icons/44.png') }}" alt="image">
@endsection

@push('script')
    <script>
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
                    
                    var userData = $(this).data('user_data') || [];
                    var transferImage = $(this).data('transfer-image') || $(this).data('transfer_image') || '';
                    var adminFeedback = $(this).data('admin-feedback') || $(this).data('admin_feedback') || '';
                    var isRejected = $(this).data('rejected') || false;
                    var isApproved = $(this).data('approved') || false;
                    
                    console.log('Transfer Image:', transferImage);
                    console.log('Is Approved:', isApproved);
                    console.log('Admin Feedback:', adminFeedback);
                    
                    // Parse JSON if it's a string
                    if (typeof userData === 'string') {
                        try {
                            userData = JSON.parse(userData);
                        } catch (e) {
                            console.warn('Failed to parse userData:', e);
                            userData = [];
                        }
                    }
                    
                    var html = '';
                    
                    // Show transfer image if exists
                    if (transferImage && transferImage.trim() !== '') {
                        var imageUrl = '{{ asset("transfers/") }}/' + transferImage;
                        html += '<li class="list-group-item">' +
                            '<strong>@lang("Transfer Receipt")</strong>' +
                            '<br>' +
                            '<div class="mt-2 mb-2" style="text-align: center;">' +
                            '<img src="' + imageUrl + '" alt="Transfer Receipt" class="img-fluid" style="max-width: 100%; max-height: 300px; border: 1px solid #ddd; border-radius: 8px; cursor: pointer;" onclick="window.open(\'' + imageUrl + '\', \'_blank\')" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';">' +
                            '<p style="display: none; color: #999;">@lang("Image not available")</p>' +
                            '</div>' +
                            '<a href="' + imageUrl + '" target="_blank" class="btn btn-sm btn-outline--primary mt-2" style="cursor: pointer; pointer-events: auto;">' +
                            '<i class="las la-eye"></i> @lang("View Full Size")' +
                            '</a>' +
                            '</li>';
                    }
                    
                    // Show old withdraw information if exists
                    if (userData && Array.isArray(userData) && userData.length > 0) {
                        userData.forEach(function(element) {
                    if (element.type != 'file') {
                                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                                    '<span>' + (element.name || '') + '</span>' +
                                    '<span>' + (element.value || '') + '</span>' +
                                    '</li>';
                            } else {
                                html += '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                                    '<span>' + (element.name || '') + '</span>' +
                                    '<span><a href="' + (element.value || '#') + '"><i class="fa-regular fa-file"></i> @lang("Attachment")</a></span>' +
                                    '</li>';
                            }
                        });
                    }
                    
                    $modal.find('.userData').html(html);

                    // Show admin feedback
                    var feedbackHtml = '';
                    if (adminFeedback) {
                        feedbackHtml = '<div class="my-3">' +
                            '<strong>@lang("Admin Feedback")</strong>' +
                            '<p>' + adminFeedback + '</p>' +
                            '</div>';
                    } else if (isRejected) {
                        feedbackHtml = '<p class="text-muted">@lang("Your withdrawal request was rejected")</p>';
                    } else if (isApproved) {
                        feedbackHtml = '<p class="text-success">@lang("Your withdrawal request has been approved and processed")</p>';
                    } else {
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
    </script>
@endpush
