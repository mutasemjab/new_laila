@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>Attendance Logs for {{ $user->name }}</h4>
                        <div>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
                            <button id="calculate-time" class="btn btn-primary">Calculate Total Time</button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div id="time-result" class="alert alert-info">
                        Total Time: {{ $totalTime }}
                    </div>
                    
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Time</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <span class="badge {{ $log->type === 'in' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($log->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->time->format('H:i:s') }}</td>
                                    <td>{{ $log->time->format('Y-m-d') }}</td>
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

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calculateBtn = document.getElementById('calculate-time');
        const timeResult = document.getElementById('time-result');
        
        calculateBtn.addEventListener('click', function() {
            // Show loading indicator
            timeResult.innerHTML = 'Calculating...';
            
            // Send the request to calculate time
            fetch('{{ route('attendance.calculate', $user->id) }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    timeResult.innerHTML = `
                        <strong>Total Time:</strong> ${data.total_time_formatted}
                    `;
                } else {
                    timeResult.innerHTML = 'Error calculating time';
                }
            })
            .catch(error => {
                timeResult.innerHTML = `Error: ${error.message}`;
            });
        });
    });
</script>
@endsection