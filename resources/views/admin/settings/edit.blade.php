@extends('layouts.admin')
@section('title')

edit Setting
@endsection



@section('contentheaderlink')
<a href="{{ route('admin.setting.index') }}"> Setting </a>
@endsection

@section('contentheaderactive')
تعديل
@endsection


@section('content')

<div class="card">
    <div class="card-header">
        <h3 class="card-title card_title_center"> edit Setting </h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">


        <form action="{{ route('admin.setting.update',$data['id']) }}" method="post" enctype='multipart/form-data'>
            <div class="row">
                @csrf



                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('messages.commission_under_ten_for_wallet') }}</label>
                        <input name="commission_under_ten_for_wallet" id="commission_under_ten_for_wallet" class="form-control"
                            value="{{ old('commission_under_ten_for_wallet',$data['commission_under_ten_for_wallet']) }}">
                        @error('commission_under_ten_for_wallet')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('messages.commission_up_ten_for_wallet') }}</label>
                        <input name="commission_up_ten_for_wallet" id="commission_up_ten_for_wallet" class="form-control"
                            value="{{ old('commission_up_ten_for_wallet',$data['commission_up_ten_for_wallet']) }}">
                        @error('commission_up_ten_for_wallet')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __('messages.commission_for_pay_invoice') }}</label>
                        <input name="commission_for_pay_invoice" id="commission_for_pay_invoice" class="form-control"
                            value="{{ old('commission_for_pay_invoice',$data['commission_for_pay_invoice']) }}">
                        @error('commission_for_pay_invoice')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>



                <div class="col-md-12">
                    <div class="form-group text-center">
                        <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm"> update</button>
                        <a href="{{ route('admin.setting.index') }}" class="btn btn-sm btn-danger">cancel</a>

                    </div>
                </div>

            </div>
        </form>



    </div>




</div>
</div>






@endsection
