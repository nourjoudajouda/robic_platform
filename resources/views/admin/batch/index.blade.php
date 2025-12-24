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
                                    <th>@lang('Batch Code')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Warehouse')</th>
                                    <th>@lang('Quality Grade')</th>
                                    <th>@lang('Origin Country')</th>
                                    <th>@lang('Units Count')</th>
                                    <th>@lang('Buy Price')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Exp Date')</th>
                                    <th>@lang('Created At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $batch)
                                    <tr>
                                        <td>{{ $batch->id }}</td>
                                        <td><span class="badge bg--primary">{{ $batch->batch_code }}</span></td>
                                        <td>{{ $batch->product->name ?? '-' }}</td>
                                        <td>{{ $batch->warehouse->name ?? '-' }}</td>
                                        <td>{{ $batch->quality_grade ?? 'N/A' }}</td>
                                        <td>{{ $batch->origin_country ?? 'N/A' }}</td>
                                        <td>{{ showAmount($batch->units_count, currencyFormat: false) }} {{ $batch->unit->symbol ?? '' }}</td>
                                        <td>{{ $batch->buy_price ? showAmount($batch->buy_price * $batch->units_count, currencyFormat: false) . ' ' . ($batch->currency->symbol ?? '') : '-' }}</td>
                                        <td>
                                            @php echo $batch->statusBadge; @endphp
                                        </td>
                                        <td>{{ $batch->exp_date ? showDateTime($batch->exp_date) : '-' }}</td>
                                        <td>{{ showDateTime($batch->created_at) }}</td>
                                        <td>
                                            <div class="button--group">
                                                <a href="{{ route('admin.batch.edit', $batch->id) }}" class="btn btn-sm btn-outline--primary">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>
                                                @if ($batch->status == Status::DISABLE)
                                                    <button type="button" class="btn btn-sm btn-outline--success ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.batch.status', $batch->id) }}" 
                                                        data-question="@lang('Are you sure to enable this batch')?">
                                                        <i class="la la-eye"></i>@lang('Enable')
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn" 
                                                        data-action="{{ route('admin.batch.status', $batch->id) }}" 
                                                        data-question="@lang('Are you sure to disable this batch')?">
                                                        <i class="la la-eye-slash"></i>@lang('Disable')
                                                    </button>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline--danger ms-1 confirmationBtn"
                                                    data-question="@lang('Are you sure to delete this batch?')"
                                                    data-action="{{ route('admin.batch.delete', $batch->id) }}">
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
                @if ($batches->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($batches) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.batch.create') }}" class="btn btn-outline--primary">
        <i class="las la-plus"></i> @lang('Add New')
    </a>
    <x-search-form placeholder="Search by ID / Batch Code / Product / Warehouse" />
@endpush

