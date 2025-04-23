@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>All Attendance Logs</h4>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Type</th>
                                <th>Time</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->user->name }}</td>
                                    <td>
                                        <span class="badge {{ $log->type === 'in' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($log->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->time->format('H:i:s') }}</td>
                                    <td>{{ $log->time->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('attendance.user.logs', $log->user_id) }}" class="btn btn-sm btn-info">View User Logs</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection