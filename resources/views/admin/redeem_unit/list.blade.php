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
                                    <th>@lang('Type')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($redeemUnits as $redeemUnit)
                                    <tr>
                                        <td>
                                            @if ($redeemUnit->type == Status::REDEEM_UNIT_BAR)
                                                @lang('Bar')
                                            @else
                                                @lang('Coin')
                                            @endif
                                        </td>
                                        <td>
                                            {{ showAmount($redeemUnit->quantity, currencyFormat:false) }} @lang('gram')
                                        </td>
                                        <td>
                                            @php echo $redeemUnit->statusBadge; @endphp
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary me-1 editUnitBtn" data-id="{{ $redeemUnit->id }}" data-type="{{ $redeemUnit->type }}" data-quantity="{{ getAmount($redeemUnit->quantity) }}">
                                                <i class="la la-pen"></i> @lang('Edit')
                                            </button>
                                            @if ($redeemUnit->status == Status::DISABLE)
                                                <button type="button" class="btn btn-sm btn-outline--success confirmationBtn" data-action="{{ route('admin.redeem.unit.status', $redeemUnit->id) }}" data-question="@lang('Are you sure to enable this redeem unit')?">
                                                    <i class="la la-eye"></i> @lang('Enable')
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.redeem.unit.status', $redeemUnit->id) }}" data-question="@lang('Are you sure to disable this redeem unit')?">
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


    <div id="redeemUnitModal" class="modal fade" tabindex="-1" role="dialog">
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
                            <label>@lang('Type')</label>
                            <select name="type" class="form-control" required>
                                <option value="" hidden>@lang('Select Type')</option>
                                <option value="{{ Status::REDEEM_UNIT_BAR }}">@lang('Bar')</option>
                                <option value="{{ Status::REDEEM_UNIT_COIN }}">@lang('Coin')</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>@lang('Quantity')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="quantity" class="form-control" required>
                                <span class="input-group-text">@lang('Gram')</span>
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

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <button type="button" class="btn btn-sm btn-outline--primary addUnitBtn">
        <i class="las la-plus"></i>@lang('Add New')
    </button>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";
            let action = "{{ route('admin.redeem.unit.save') }}";

            let modal = $('#redeemUnitModal');

            $('.addUnitBtn').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add New Redeem Unit')`);
                modal.find('form').attr('action', action);
                modal.modal('show');
            });

            $('.editUnitBtn').on('click', function() {
                let data = $(this).data();
                modal.find('.modal-title').text(`@lang('Edit Redeem Unit')`);
                modal.find('form').attr('action', action + '/' + data.id);
                modal.find('select[name=type]').val(data.type);
                modal.find('input[name=quantity]').val(data.quantity);
                modal.modal('show');
            });

            modal.on('hidden.bs.modal', function() {
                modal.find('form')[0].reset();
                modal.find('form').attr('action', action);
            });


        })(jQuery);
    </script>
@endpush
