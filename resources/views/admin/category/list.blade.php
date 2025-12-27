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
                                    <th>@lang('Karat')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Total Asset')</th>
                                    <th>@lang('Asset Value')</th>
                                    <th>@lang('1h/24h/7d/30d/90d Change')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ __($category->name) }}</td>
                                        <td>{{ $category->karat }} @lang('karat')</td>
                                        <td>{{ showAmount($category->price) }}</td>
                                        <td>{{ showAmount($category->total_quantity, currencyFormat:false) }} @lang('g')</td>
                                        <td>{{ showAmount($category->total_quantity * $category->price) }}</td>
                                        <td>
                                            <span class="{{ $category->change_1h >= 0 ? 'text--success' : 'text--danger' }}">{{ abs($category->change_1h) }}%</span> / 
                                            <span class="{{ $category->change_24h >= 0 ? 'text--success' : 'text--danger' }}">{{ abs($category->change_24h) }}%</span> / 
                                            <span class="{{ $category->change_7d >= 0 ? 'text--success' : 'text--danger' }}">{{ abs($category->change_7d) }}%</span> / 
                                            <span class="{{ $category->change_30d >= 0 ? 'text--success' : 'text--danger' }}">{{ abs($category->change_30d) }}%</span> / 
                                            <span class="{{ $category->change_90d >= 0 ? 'text--success' : 'text--danger' }}">{{ abs($category->change_90d) }}%</span>
                                        </td>
                                        <td>
                                            @php echo $category->statusBadge; @endphp
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline--primary me-1 editCategory" data-category="{{ $category }}" data-price="{{ getAmount($category->price) }}">
                                                <i class="la la-pen"></i>@lang('Edit')
                                            </button>
                                            @if ($category->status == Status::DISABLE)
                                                <button type="button" class="btn btn-sm btn-outline--success ms-1 confirmationBtn" data-action="{{ route('admin.category.status', $category->id) }}" data-question="@lang('Are you sure to enable this category')?">
                                                    <i class="la la-eye"></i>@lang('Enable')
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline--danger confirmationBtn" data-action="{{ route('admin.category.status', $category->id) }}" data-question="@lang('Are you sure to disable this category')?">
                                                    <i class="la la-eye-slash"></i>@lang('Disable')
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


    <div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
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
                            <label>@lang('Name') (@lang('English'))</label>
                            <input type="text" name="name_en" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Name') (@lang('Arabic'))</label>
                            <input type="text" name="name_ar" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Karat')</label>
                            <input type="number" name="karat" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('Price per gram')</label>
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
            let modal = $('#categoryModal');
            let action = `{{ route('admin.category.save') }}`;
            $('.addCategory').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add New Category')`);
                modal.find('input[name=name_en]').val('');              
                modal.find('input[name=name_ar]').val('');
                modal.find('input[name=karat]').val('');
                modal.find('input[name=price]').val('');
                modal.find('form').attr('action', action);
                modal.modal('show');
            });

            $('.editCategory').on('click', function() {
                let category = $(this).data('category');
                modal.find('input[name=name_en]').val(category.name_en || category.name);              
                modal.find('input[name=name_ar]').val(category.name_ar || category.name);
                modal.find('input[name=karat]').val(category.karat);
                modal.find('input[name=price]').val($(this).data('price'));
                modal.find('.modal-title').text(`@lang('Update Category')`);
                modal.find('form').attr('action', `${action+'/'+category.id}`);
                modal.modal('show');
            });


        })(jQuery);
    </script>
@endpush

@push('breadcrumb-plugins')
    <button class="btn btn-outline--primary addCategory"><i class="las la-plus"></i>@lang('Add New')</button>
@endpush
