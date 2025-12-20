@extends('admin.layouts.app')

@section('panel')
    <div class="row mb-none-30">


        <div class="col-lg-4 col-md-4 mb-30">
            <div class="card overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="mb-20 text-muted">@lang('Withdraw Via') @lang('Bank Transfer')</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Date')
                            <span class="fw-bold">{{ showDateTime($withdrawal->created_at) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Trx Number')
                            <span class="fw-bold">{{ $withdrawal->trx }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Username')
                            <span class="fw-bold">
                                <a href="{{ route('admin.users.detail', $withdrawal->user_id) }}"><span>@</span>{{ @$withdrawal->user->username }}</a>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Method')
                            <span class="fw-bold">@lang('Bank Transfer')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Amount')
                            <span class="fw-bold">{{ showAmount($withdrawal->amount ) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Currency')
                            <span class="fw-bold">{{ __($withdrawal->currency) }}</span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Status')
                            @php echo $withdrawal->statusBadge @endphp
                        </li>

                        @if($withdrawal->admin_feedback)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            @lang('Admin Response')
                           <p>{{$withdrawal->admin_feedback}}</p>
                        </li>
                        @endif
                        @if($withdrawal->transfer_image)
                            <li class="list-group-item">
                                <strong>@lang('Transfer Receipt')</strong>
                                <br>
                                <a href="{{ asset('transfers/' . $withdrawal->transfer_image) }}" target="_blank" class="btn btn-sm btn-outline--primary mt-2">
                                    <i class="las la-eye"></i> @lang('View Image')
                                </a>
                                <a href="{{ asset('transfers/' . $withdrawal->transfer_image) }}" download class="btn btn-sm btn-outline--info mt-2">
                                    <i class="las la-download"></i> @lang('Download')
                                </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 mb-30">

            <div class="card overflow-hidden box--shadow1">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-2">@lang('Withdrawal Information')</h5>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>@lang('Amount')</h6>
                            <p>{{ showAmount($withdrawal->amount) }}</p>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6>@lang('Transaction Number')</h6>
                            <p>{{ $withdrawal->trx }}</p>
                        </div>
                    </div>

                    @if($withdrawal->admin_feedback)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h6>@lang('Admin Notes')</h6>
                                <p>{{ $withdrawal->admin_feedback }}</p>
                            </div>
                        </div>
                    @endif

                    @if($details != null && $details != 'null' && $details != '')
                        @php
                            $decodedDetails = json_decode($details);
                        @endphp
                        @if($decodedDetails && is_array($decodedDetails) && count($decodedDetails) > 0)
                            @foreach($decodedDetails as $val)
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <h6>{{__($val->name)}}</h6>
                                        @if(isset($val->type) && $val->type == 'checkbox')
                                        {{ implode(',',$val->value) }}
                                        @elseif(isset($val->type) && $val->type == 'file')
                                        @if($val->value)
                                            <a href="{{ route('admin.download.attachment',encrypt(getFilePath('verify').'/'.$val->value)) }}"><i class="fa-regular fa-file"></i>  @lang('Attachment') </a>
                                        @else
                                            @lang('No File')
                                        @endif
                                    @else
                                        <p>{{__($val->value ?? '')}}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        @endif
                    @endif


                    @if($withdrawal->status == Status::PAYMENT_PENDING)
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button class="btn btn-outline--success btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#approveModal">
                                    <i class="las la-check"></i> @lang('Approve')
                                </button>

                                <button class="btn btn-outline--danger btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="las la-ban"></i> @lang('Reject')
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>



    {{-- APPROVE MODAL --}}
    <div id="approveModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Approve Withdrawal Confirmation')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{ route('admin.withdraw.data.approve') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $withdrawal->id }}">
                    <div class="modal-body">
                        <p>@lang('Have you sent') <span class="fw-bold text--success">{{ showAmount($withdrawal->final_amount,currencyFormat:false) }} {{$withdrawal->currency}}</span>?</p>
                        
                        <div class="form-group mt-3">
                            <label>@lang('Transfer Receipt Image') <span class="text--danger">*</span></label>
                            <input type="file" name="transfer_image" class="form-control" accept="image/*" required>
                            <small class="form-text text-muted">@lang('Upload the bank transfer receipt image')</small>
                        </div>
                        
                        <div class="form-group mt-3">
                            <label>@lang('Details')</label>
                            <textarea name="details" class="form-control" rows="3" placeholder="@lang('Optional: Provide additional details. eg: transaction number')">{{ old('details') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Approve & Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- REJECT MODAL --}}
    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reject Withdrawal Confirmation')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="{{route('admin.withdraw.data.reject')}}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $withdrawal->id }}">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Reason of Rejection')</label>
                            <textarea name="details" class="form-control" rows="3" value="{{ old('details') }}" required></textarea>
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
