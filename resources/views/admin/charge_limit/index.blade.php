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
                                    <th>@lang('Slug')</th>
                                    <th>@lang('Min Amount')</th>
                                    <th>@lang('Max Amount')</th>
                                    <th>@lang('Fixed Charge')</th>
                                    <th>@lang('Percent Charge')</th>
                                    <th>@lang('Vat')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($chargeLimits as $chargeLimit)
                                    <tr>
                                        <td>{{ __(ucFirst($chargeLimit->slug)) }}</td>
                                        <td>{{ showAmount($chargeLimit->min_amount) }}</td>
                                        <td>{{ showAmount($chargeLimit->max_amount) }}</td>
                                        <td>{{ showAmount($chargeLimit->fixed_charge) }}</td>
                                        <td>{{ $chargeLimit->percent_charge }}%</td>
                                        <td>{{ $chargeLimit->id == 1 ? $chargeLimit->vat . '%' : '--' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary me-1 updateChargeLimit" data-id="{{ $chargeLimit->id }}" data-min_amount="{{ getAmount($chargeLimit->min_amount) }}" data-max_amount="{{ getAmount($chargeLimit->max_amount) }}" data-fixed_charge="{{ getAmount($chargeLimit->fixed_charge) }}" data-percent_charge="{{ getAmount($chargeLimit->percent_charge) }}" data-vat="{{ getAmount($chargeLimit->vat) }}"
                                                data-title="{{ __('Update ' . $chargeLimit->slug . ' charge limit') }}">
                                                <i class="las la-pen"></i>@lang('Edit')
                                            </button>
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


    <div id="chargeLimitModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Min Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="min_amount" class="form-control" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Max Amount')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="max_amount" class="form-control" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Fixed Charge')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="fixed_charge" class="form-control" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>@lang('Percent Charge')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="percent_charge" class="form-control" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="form-group vatGroup">
                            <label>@lang('Vat')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="vat" class="form-control" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";
            let modal = $('#chargeLimitModal');
            let action = `{{ route('admin.charge.limit.save') }}`;

            $('.updateChargeLimit').on('click', function() {
                let data = $(this).data();
                $('[name=min_amount]').val(data.min_amount);
                $('[name=max_amount]').val(data.max_amount);
                $('[name=fixed_charge]').val(data.fixed_charge);
                $('[name=percent_charge]').val(data.percent_charge);
                $('[name=vat]').val(data.vat);
                modal.find('.modal-title').text(data.title);
                modal.find('form').attr('action', `${action+'/'+data.id}`);
                modal.modal('show');
                handleVat(data.id, modal);
            });

            function handleVat(id, modal) {
                if (id == 1) {
                    modal.find('.vatGroup').removeClass('d-none');
                    modal.find('.vatGroup').find('label').addClass('required');
                    modal.find('.vatGroup').find('[name=vat]').attr('required', true);
                } else {
                    modal.find('.vatGroup').addClass('d-none');
                    modal.find('.vatGroup').find('label').removeClass('required');
                    modal.find('.vatGroup').find('[name=vat]').attr('required', false);
                }
            }

        })(jQuery);
    </script>
@endpush
