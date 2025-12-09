@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light">
                            <thead>
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($apis as $api)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb">
                                                    <img src="{{ getImage(getFilePath('api_provider') . '/' . $api->image, getFileSize('api_provider')) }}">
                                                </div>
                                                <span class="name">{{ __($api->name) }}</span>
                                            </div>
                                        </td>

                                        <td>
                                            @php echo $api->statusBadge; @endphp
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary ms-1 mb-2 configureBtn" data-provider='@json($api)'>
                                                <i class="la la-cogs"></i> @lang('Configure')
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline--dark ms-1 mb-2 helpBtn" data-provider="{{ $api }}">
                                                <i class="la la-question"></i>@lang('Help')
                                            </button>
                                            @if ($api->status == Status::DISABLE)
                                                <button type="button" class="btn btn-sm btn-outline--success ms-1 mb-2 confirmationBtn" data-action="{{ route('admin.price.api.status', $api->id) }}" data-question="@lang('Are you sure to enable this currency data provider')?">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline--danger mb-2 confirmationBtn" data-action="{{ route('admin.price.api.status', $api->id) }}" data-question="@lang('Are you sure to disable this currency data provider')?">
                                                    <i class="la la-eye-slash"></i> @lang('Disable')
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>


    {{-- EDIT METHOD MODAL --}}
    <div id="modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Update Currency Data Provider'): <span class="provider-name"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST">
                    @csrf
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- HELP METHOD MODAL --}}
    <div id="helpModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Need Help')?</h5>
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


@push('script')
    <script>
        (function($) {
            "use strict";

            $(document).on('click', '.configureBtn', function() {
                var modal = $('#modal');
                var provider = $(this).data('provider');
                var action = "{{ route('admin.price.api.update', ':id') }}";
                var shortcode = provider.configuration;

                modal.find('.provider-name').text(provider.name);
                modal.find('form').attr('action', action.replace(':id', provider.id));

                var html = '';
                $.each(shortcode, function(key, item) {
                    html += `<div class="form-group">
                        <label class="col-md-12 control-label fw-bold">${item.title}</label>
                        <div class="col-md-12">
                            <input name="${key}" class="form-control" placeholder="--" value="${item.value}" required>
                        </div>
                    </div>`;
                })
                modal.find('.modal-body').html(html);
                modal.modal('show');
            });

            $(document).on('click', '.helpBtn', function() {
                var modal = $('#helpModal');
                var path = "{{ asset(getFilePath('extensions')) }}";
                const {
                    name,
                    instruction
                } = $(this).data('provider');

                modal.find('.modal-title').html(`@lang('${name} api key setting instruction')`);
                modal.find('.modal-body').html(instruction);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
