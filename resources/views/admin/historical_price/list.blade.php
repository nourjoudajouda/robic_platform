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
                                    <th>@lang('Date')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historicalPrices as $historicalPrice)
                                    <tr>
                                        <td>{{ $historicalPrice->date }}</td>
                                        <td> {{ showAmount($historicalPrice->price) }} </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary me-1 editPrice" data-id="{{ $historicalPrice->id }}" data-date="{{ $historicalPrice->date }}" data-price="{{ getAmount($historicalPrice->price) }}">
                                                <i class="la la-pen"></i>@lang('Edit')
                                            </button>
                                            <button  type="button" class="btn btn-sm btn-outline--danger confirmationBtn" data-question="@lang('Are you sure to delete this historical price?')" data-action="{{ route('admin.historical.price.delete', $historicalPrice->id) }}"><i class="las la-trash"></i>@lang('Delete')</button>
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
                @if ($historicalPrices->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($historicalPrices) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>


    <div id="priceModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form class="disableSubmission" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Date')</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Price')</label>
                            <div class="input-group">
                                <input type="number" step="any" name="price" class="form-control" required>
                                <span class="input-group-text">{{ __(gs('cur_text')) }}</span>
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


@push('script')
    <script>
        (function($) {
            "use strict";
            let modal = $('#priceModal');
            let action = `{{ route('admin.historical.price.save') }}`;
            
            $('.addPrice').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add New Price Data')`);
                modal.find('input[name=date]').val('');
                modal.find('input[name=price]').val('');
                modal.find('form').attr('action', action);
                modal.modal('show');
            });

            $('.editPrice').on('click', function() {
                let historicalPrice = $(this).data();
                modal.find('input[name=date]').val(historicalPrice.date);
                modal.find('input[name=price]').val(historicalPrice.price);
                modal.find('.modal-title').text(`@lang('Update Price Data')`);
                modal.find('form').attr('action', `${action+'/'+historicalPrice.id}`);
                modal.modal('show');
            });


        })(jQuery);
    </script>
@endpush

@push('breadcrumb-plugins')
    <button class="btn btn-outline--primary addPrice"><i class="las la-plus"></i>@lang('Add New')</button>
@endpush
