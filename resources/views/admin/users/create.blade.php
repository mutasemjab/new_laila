@extends('layouts.admin')
@section('title')
{{ __('messages.users') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">{{ __('messages.Add_New') }} {{ __('messages.users') }}</h3>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="post" enctype='multipart/form-data'>
                <div class="row">
                    @csrf

                
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Name') }}</label>
                            <input name="name" id="name" class="form-control" value="{{ old('name') }}">
                            @error('name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                   
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Company') }}</label>
                            <input name="company" id="company" class="form-control" value="{{ old('company') }}">
                            @error('company')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Country') }}</label>
                            <input name="country" id="country" class="form-control" value="{{ old('country') }}">
                            @error('country')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Email') }}</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Phone') }}</label>
                            <input name="phone" id="phone" class="form-control" value="{{ old('phone') }}">
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Gender') }}</label>
                            <select name="gender" id="gender" class="form-control">
                                <option value="">{{ __('messages.Select') }}</option>
                                <option @if (old('gender') == 1 || old('gender') == '') selected="selected" @endif value="1">{{ __('messages.Male') }}</option>
                                <option @if (old('gender') == 2 && old('gender') != '') selected="selected" @endif value="2">{{ __('messages.Female') }}</option>
                            </select>
                            @error('gender')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Category') }}</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">{{ __('messages.Select') }}</option>
                                <option @if (old('category') == 1 || old('category') == '') selected="selected" @endif value="1">{{ __('messages.Speaker') }}</option>
                                <option @if (old('category') == 2 && old('category') != '') selected="selected" @endif value="2">{{ __('messages.Participant') }}</option>
                                <option @if (old('category') == 3 && old('category') != '') selected="selected" @endif value="3">{{ __('messages.Exhibitor') }}</option>
                                <option @if (old('category') == 4 && old('category') != '') selected="selected" @endif value="4">{{ __('messages.Committee') }}</option>
                                <option @if (old('category') == 5 && old('category') != '') selected="selected" @endif value="5">{{ __('messages.Press') }}</option>
                                <option @if (old('category') == 6 && old('category') != '') selected="selected" @endif value="6">{{ __('messages.Other') }}</option>
                            </select>
                            @error('category')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{ __('messages.Activate') }}</label>
                            <select name="activate" id="activate" class="form-control">
                                <option value="">{{ __('messages.Select') }}</option>
                                <option @if (old('activate') == 1 || old('activate') == '') selected="selected" @endif value="1">{{ __('messages.Activate') }}</option>
                                <option @if (old('activate') == 2 && old('activate') != '') selected="selected" @endif value="2">{{ __('messages.Deactivate') }}</option>
                            </select>
                            @error('activate')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group text-center">
                            <button id="do_add_item_cardd" type="submit" class="btn btn-primary btn-sm">{{ __('messages.Submit') }}</button>
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-danger">{{ __('messages.Cancel') }}</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ asset('assets/admin/js/users.js') }}"></script>


<script>
    function previewImage() {
      var preview = document.getElementById('image-preview');
      var input = document.getElementById('Item_img');
      var file = input.files[0];
      if (file) {
      preview.style.display = "block";
      var reader = new FileReader();
      reader.onload = function() {
        preview.src = reader.result;
      }
      reader.readAsDataURL(file);
    }else{
        preview.style.display = "none";
    }
    }
</script>

@endsection
