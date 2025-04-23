@extends('layouts.admin')
@section('title')
    {{ __('messages.Show') }} {{ __('messages.Customers') }}
@endsection




@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>User Details</h4>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>User Information</h5>
                            <p><strong>ID:</strong> {{ $user->id }}</p>
                            <p><strong>Name:</strong> {{ $user->name }}</p>
                            <p><strong>Phone:</strong> {{ $user->phone }}</p>
                            <p><strong>Barcode:</strong> {{ $user->barcode }}</p>
                            <p><strong>Created At:</strong> {{ $user->created_at->format('Y-m-d H:i:s') }}</p>
                            
                            <div class="mt-3">
                                <a href="{{ route('attendance.user.logs', $user->id) }}" class="btn btn-info">View Attendance Logs</a>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Edit User</a>
                            </div>
                        </div>
                        
                        <div class="col-md-6 text-center">
                            <h5>User Barcode</h5>
                            <div class="mt-3">
                                {!! DNS1D::getBarcodeHTML($user->barcode, 'C128') !!}
                                <p class="mt-2">{{ $user->barcode }}</p>
                            </div>
                            <button class="btn btn-success mt-3" onclick="window.print()">Print Barcode</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('script')
    <script src="{{ asset('assets/admin/js/customers.js') }}"></script>
@endsection
