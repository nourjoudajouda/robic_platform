@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Client ID')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (gs('socialite_credentials') ?? [] as $key => $credential)
                                    @if ($key == 'facebook' || $key == 'linkedin')
                                        {{-- <tr>
                                            <td class="fw-bold">{{ ucfirst($key) }}</td>
                                            <td>{{ $credential->client_id }}</td>
                                            <td>
                                                @if (@$credential->status == Status::ENABLE)
                                                    <span class="badge badge--success">@lang('Enabled')</span>
                                                @else
                                                    <span class="badge badge--warning">@lang('Disabled')</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="button--group">
                                                    <button class="btn btn-outline--primary btn-sm editBtn"
                                                        data-client_id="{{ $credential->client_id }}"
                                                        data-client_secret="{{ $credential->client_secret }}"
                                                        data-key="{{ $key }}"><i class="la la-cogs"></i>
                                                        @lang('Configure')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--dark helpBtn" data-target-key="{{ $key }}">
                                                        <i class="la la-question"></i> @lang('Help')
                                                    </button>
                                                    @if (@$credential->status == Status::ENABLE)
                                                        <button class="btn btn-outline--danger btn-sm confirmationBtn"  data-question="@lang('Are you sure that you want to disable this login credential?')" data-action="{{ route('admin.setting.socialite.credentials.status.update', $key) }}">
                                                            <i class="las la-eye-slash"></i>@lang('Disable')
                                                        </button>
                                                    @else
                                                        <button class="btn btn-outline--success btn-sm confirmationBtn" data-question="@lang('Are you sure that you want to enable login credential?')" data-action="{{ route('admin.setting.socialite.credentials.status.update', $key) }}">
                                                            <i  class="las la-eye"></i>@lang('Enable')
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr> --}}
                                    @else
                                    <tr>
                                        <td class="fw-bold">{{ ucfirst($key) }}</td>
                                        <td>{{ $credential->client_id }}</td>
                                        <td>
                                            @if (@$credential->status == Status::ENABLE)
                                                <span class="badge badge--success">@lang('Enabled')</span>
                                            @else
                                                <span class="badge badge--warning">@lang('Disabled')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button class="btn btn-outline--primary btn-sm editBtn"
                                                    data-client_id="{{ $credential->client_id }}"
                                                    data-client_secret="{{ $credential->client_secret }}"
                                                    data-key="{{ $key }}"><i class="la la-cogs"></i>
                                                    @lang('Configure')
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline--dark helpBtn" data-target-key="{{ $key }}">
                                                    <i class="la la-question"></i> @lang('Help')
                                                </button>
                                                @if (@$credential->status == Status::ENABLE)
                                                    <button class="btn btn-outline--danger btn-sm confirmationBtn"  data-question="@lang('Are you sure that you want to disable this login credential?')" data-action="{{ route('admin.setting.socialite.credentials.status.update', $key) }}">
                                                        <i class="las la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline--success btn-sm confirmationBtn" data-question="@lang('Are you sure that you want to enable login credential?')" data-action="{{ route('admin.setting.socialite.credentials.status.update', $key) }}">
                                                        <i  class="las la-eye"></i>@lang('Enable')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit -->
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Update Credential'): <span class="credential-name"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Client ID')</label>
                            <input type="text" class="form-control" name="client_id">
                        </div>
                        <div class="form-group">
                            <label>@lang('Client Secret')</label>
                            <input type="text" class="form-control" name="client_secret">
                        </div>
                        <div class="form-group">
                            <label>@lang('Callback URL')</label>
                            <div class="input-group">
                                <input type="text" class="form-control callback" readonly>
                                <button type="button" class="input-group-text copyInput" title="@lang('Copy')">
                                    <i class="las la-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45"
                            id="editBtn">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help -->
    <div id="helpModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('How to get') <span class="title-key"></span> @lang('credentials')?</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@php
    $helpTexts = [
        'step1' => __('Step 1'),
        'step2' => __('Step 2'),
        'step3' => __('Step 3'),
        'step4' => __('Step 4'),
        'step5' => __('Step 5'),
        'step6' => __('Step 6'),
        'step7' => __('Step 7'),
        'step8' => __('Step 8'),
        'step9' => __('Step 9'),
        'goTo' => __('Go to'),
        'googleDevConsole' => __('google developer console'),
        'newProject' => __('New Project'),
        'credentials' => __('credentials'),
        'oauthClientId' => __('OAuth client ID'),
        'configureConsent' => __('Configure Consent Screen'),
        'chooseExternal' => __('Choose External option and press the create button'),
        'fillInfo' => __('Please fill up the required informations for app configuration'),
        'clickCredentials' => __('Again click on'),
        'step8Text' => __('and select type as web application and fill up the required informations. Also don\'t forget to add redirect url and press create button'),
        'step9Text' => __('Finally you\'ve got the credentials. Please copy the Client ID and Client Secret and paste it in admin panel google configuration'),
        'clickOn' => __('Click on'),
        'selectProject' => __('Click on Select a project than click on'),
        'createProject' => __('and create a project providing the project name'),
        'createCredentials' => __('Click on create credentials and select'),
    ];
@endphp

@push('script')
    <script>
        (function($) {
            "use strict";
            
            var helpTexts = {!! json_encode($helpTexts) !!};
            
            // Wait for DOM to be ready
            $(document).ready(function() {
                console.log('Social credentials page loaded');
            });
            
            $(document).on('click', '.editBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('Configure button clicked');
                
                let modal = $('#editModal');
                if (!modal.length) {
                    console.error('Modal #editModal not found');
                    alert('Modal not found. Please refresh the page.');
                    return;
                }
                
                let data = $(this).data();
                console.log('Button data:', data);
                
                if (!data.key) {
                    console.error('Key not found in data attributes');
                    alert('Error: Key not found. Please refresh the page.');
                    return;
                }
                
                let route = "{{ route('admin.setting.socialite.credentials.update', '') }}";
                let callbackUrl = "{{ route('user.social.login.callback', '') }}";
                
                modal.find('form').attr('action', `${route}/${data.key}`);
                modal.find('.credential-name').text(data.key);
                modal.find('[name=client_id]').val(data.client_id || '');
                modal.find('[name=client_secret]').val(data.client_secret || '');
                modal.find('.callback').val(`${callbackUrl}/${data.key}`);
                
                console.log('Opening modal...');
                
                // Use Bootstrap 5 modal if available, otherwise Bootstrap 4
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var bsModal = new bootstrap.Modal(modal[0], {
                        backdrop: true,
                        keyboard: true
                    });
                    bsModal.show();
                } else if ($.fn.modal) {
                    modal.modal('show');
                } else {
                    console.error('Bootstrap modal not available');
                    alert('Bootstrap modal not available. Please refresh the page.');
                }
            });
            $(document).on('click', '.copyInput', function(e) {
                e.preventDefault();
                var copybtn = $(this);
                var input = copybtn.closest('.input-group').find('input');
                if (input && input.length) {
                    input[0].select();
                    input[0].setSelectionRange(0, 99999); // For mobile devices
                    try {
                        document.execCommand('copy');
                        input.blur();
                        if (typeof notify !== 'undefined') {
                            notify('success', `Copied: ${input.val()}`);
                        } else {
                            alert('Copied: ' + input.val());
                        }
                    } catch (err) {
                        alert('Please press Ctrl/Cmd + C to copy');
                    }
                }
            });

            $(document).on('click', '.helpBtn', function() {
                var modal = $('#helpModal');

                let rules = '';
                let key = $(this).data('target-key');
                modal.find('.title-key').text(key);

                if (key == 'google') {
                    var step1 = helpTexts.step1 || 'Step 1';
                    var step2 = helpTexts.step2 || 'Step 2';
                    var step3 = helpTexts.step3 || 'Step 3';
                    var step4 = helpTexts.step4 || 'Step 4';
                    var step5 = helpTexts.step5 || 'Step 5';
                    var step6 = helpTexts.step6 || 'Step 6';
                    var step7 = helpTexts.step7 || 'Step 7';
                    var step8 = helpTexts.step8 || 'Step 8';
                    var step9 = helpTexts.step9 || 'Step 9';
                    var goTo = helpTexts.goTo || 'Go to';
                    var googleDevConsole = helpTexts.googleDevConsole || 'google developer console';
                    var newProject = helpTexts.newProject || 'New Project';
                    var credentials = helpTexts.credentials || 'credentials';
                    var oauthClientId = helpTexts.oauthClientId || 'OAuth client ID';
                    var configureConsent = helpTexts.configureConsent || 'Configure Consent Screen';
                    var chooseExternal = helpTexts.chooseExternal || 'Choose External option and press the create button';
                    var fillInfo = helpTexts.fillInfo || 'Please fill up the required informations for app configuration';
                    var clickCredentials = helpTexts.clickCredentials || 'Again click on';
                    var step8Text = helpTexts.step8Text || 'and select type as web application and fill up the required informations. Also don\'t forget to add redirect url and press create button';
                    var step9Text = helpTexts.step9Text || 'Finally you\'ve got the credentials. Please copy the Client ID and Client Secret and paste it in admin panel google configuration';
                    var clickOn = helpTexts.clickOn || 'Click on';
                    var selectProject = helpTexts.selectProject || 'Click on Select a project than click on';
                    var createProject = helpTexts.createProject || 'and create a project providing the project name';
                    var createCredentials = helpTexts.createCredentials || 'Click on create credentials and select';

                    rules = '<ul class="list-group list-group-flush">' +
                        '<li class="list-group-item"><b>' + step1 + '</b>: ' + goTo + ' <a href="https://console.developers.google.com" target="_blank">' + googleDevConsole + '.</a></li>' +
                        '<li class="list-group-item"><b>' + step2 + '</b>: ' + selectProject + ' <a href="https://console.cloud.google.com/projectcreate" target="_blank">' + newProject + '</a> ' + createProject + '.</li>' +
                        '<li class="list-group-item"><b>' + step3 + '</b>: ' + clickOn + ' <a href="https://console.cloud.google.com/apis/credentials" target="_blank">' + credentials + '.</a></li>' +
                        '<li class="list-group-item"><b>' + step4 + '</b>: ' + createCredentials + ' <a href="https://console.cloud.google.com/apis/credentials/oauthclient" target="_blank">' + oauthClientId + '.</a></li>' +
                        '<li class="list-group-item"><b>' + step5 + '</b>: ' + clickOn + ' <a href="https://console.cloud.google.com/apis/credentials/consent" target="_blank">' + configureConsent + '.</a></li>' +
                        '<li class="list-group-item"><b>' + step6 + '</b>: ' + chooseExternal + '.</li>' +
                        '<li class="list-group-item"><b>' + step7 + '</b>: ' + fillInfo + '.</li>' +
                        '<li class="list-group-item"><b>' + step8 + '</b>: ' + clickCredentials + ' <a href="https://console.cloud.google.com/apis/credentials" target="_blank">' + credentials + '</a> ' + step8Text + '.</li>' +
                        '<li class="list-group-item"><b>' + step9 + '</b>: ' + step9Text + '.</li>' +
                        '</ul>';
                }
                //  else if (key == 'facebook') {
                //     rules = ` <ul class="list-group list-group-flush">
                //         <li class="list-group-item"><b>@lang('Step 1')</b>: @lang('Go to') <a href="https://developers.facebook.com/" target="_blank">@lang('facebook developer')</a></li>
                //         <li class="list-group-item"><b>@lang('Step 2')</b>: @lang('Click on Get Started and create Meta Developer account').</li>
                //         <li class="list-group-item"><b>@lang('Step 3')</b>: @lang('Create an app by selecting Consumer option').</li>
                //         <li class="list-group-item"><b>@lang('Step 4')</b>: @lang('Click on Setup Facebook Login and select Web option').</li>
                //         <li class="list-group-item"><b>@lang('Step 5')</b>: @lang('Add site url').</li>
                //         <li class="list-group-item"><b>@lang('Step 6')</b>: @lang('Go to Facebook Login > Settings and add callback URL here').</li>
                //         <li class="list-group-item"><b>@lang('Step 7')</b>: @lang('Go to Setting > Basic and copy the credentials and paste to admin panel').</li>

                //     </ul>`;
                // } else if (key == 'linkedin') {
                //     rules = `<ul class="list-group list-group-flush">
                //         <li class="list-group-item"><b>@lang('Step 1')</b>: @lang('Go to') <a href="https://developer.linkedin.com/" target="_blank">@lang('linkedin developer')</a>.</li>
                //         <li class="list-group-item"><b>@lang('Step 2')</b>: @lang('Click on create app and provide required information').</li>
                //         <li class="list-group-item"><b>@lang('Step 3')</b>: @lang('Click on Sign In with Linkedin > Request access').</li>
                //         <li class="list-group-item"><b>@lang('Step 4')</b>: @lang('Click Auth option and copy the credentials and paste it to admin panel and don\'t forget to add redirect url here').</li>
                //     </ul>`;
                // }

                modal.find('.modal-body').html(rules);
                
                // Use Bootstrap 5 modal if available, otherwise Bootstrap 4
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var bsModal = new bootstrap.Modal(modal[0], {
                        backdrop: true,
                        keyboard: true
                    });
                    bsModal.show();
                } else if ($.fn.modal) {
                    modal.modal('show');
                } else {
                    console.error('Bootstrap modal not available');
                    alert('Bootstrap modal not available. Please refresh the page.');
                }
            });
        })(jQuery);
    </script>
@endpush
