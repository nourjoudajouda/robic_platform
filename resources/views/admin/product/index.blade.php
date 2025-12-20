@extends('admin.layouts.app')

@php
    use App\Constants\Status;
@endphp

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('ID')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('SKU')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td>{{ __($product->name) }}</td>
                                        <td>{{ $product->sku }}</td>
                                        <td>
                                            @php echo $product->statusBadge; @endphp
                                        </td>
                                        <td>{{ showDateTime($product->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.product.edit', $product->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>
                                                @if ($product->status == Status::DISABLE)
                                                    <button type="button" class="btn btn-sm btn-outline--success ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.product.status', $product->id) }}" 
                                                        data-question="@lang('Are you sure to enable this product')?">
                                                        <i class="la la-eye"></i>@lang('Enable')
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.product.status', $product->id) }}" 
                                                        data-question="@lang('Are you sure to disable this product')?">
                                                        <i class="la la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to delete this product?')"
                                                    data-action="{{ route('admin.product.delete', $product->id) }}">
                                                    <i class="las la-trash"></i> @lang('Delete')
                                                </button>
                                            </div>
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
                @if ($products->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($products) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.product.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i> @lang('Add New')
    </a>
    <x-search-form placeholder="Search by ID / Name / SKU" />
@endpush

