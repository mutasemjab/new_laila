@extends('layouts.admin')
@section('title')
    {{ __('messages.users') }}
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Import Users from Excel</h4>
                </div>
                <div class="card-body">
                    @if(session('import_success'))
                        <div class="alert alert-success">
                            {{ session('import_success') }}
                        </div>
                    @endif

                    @if(session('import_error'))
                        <div class="alert alert-danger">
                            {{ session('import_error') }}
                        </div>
                    @endif

                    <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                        @csrf
                        <div class="col-md-8">
                            <label for="excel_file" class="form-label">Excel File</label>
                            <input type="file" class="form-control" id="excel_file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Upload .xlsx, .xls, or .csv files with user data</div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-file-import me-1"></i> Import Users
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>Users</h4>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">Create New User</a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Category Filter Form -->
                    <form action="{{ route('users.index') }}" method="GET" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="category" class="form-label">Filter by Category</label>
                                <select name="category" id="category" class="form-control">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $key => $value)
                                        <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </div>
                    </form>

                    @if(isset($users) && $users->count())
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Email</th>
                                <th>Barcode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{!! $user->categoryLabel() !!}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->barcode }}</td>
                                    <td>
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="{{ route('user-time.show', $user->id) }}" class="btn btn-sm btn-secondary">Attendance</a>
                                        <form method="POST" action="{{ route('certificate.download') }}">
                                            @csrf
                                            <input type="text" name="name" value="{{ $user->name }}" hidden>
                                            <input type="text" name="id" value="{{ $user->id }}" hidden>
                                            <button type="submit">Give the Certificate</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection