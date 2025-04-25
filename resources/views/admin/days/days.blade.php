@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Cumulative Time in Conference Rooms (Excluding Main Room)</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Attendee</th>
                                    <th>Barcode</th>
                                    @foreach($days as $day)
                                        <th>Day {{ $day->id }}</th>
                                    @endforeach
                                    <th>Cumulative Total</th>
                                    <th>Room Breakdown</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($userData as $userId => $data)
                                    <tr>
                                        <td>{{ $data['user']->name }}</td>
                                        <td>{{ $data['user']->barcode }}</td>
                                        
                                        @foreach($days as $day)
                                            <td>
                                                {{ $data['daily_breakdown'][$day->id] ?? '0 hours, 0 minutes' }}
                                            </td>
                                        @endforeach
                                        
                                        <td><strong>{{ $data['total_time'] }}</strong></td>
                                        
                                        <td>
                                            <button class="btn btn-sm btn-info" type="button" data-toggle="collapse" 
                                                    data-target="#roomBreakdown{{ $userId }}" aria-expanded="false">
                                                View Details
                                            </button>
                                            
                                            <div class="collapse mt-2" id="roomBreakdown{{ $userId }}">
                                                <ul class="list-group">
                                                    @foreach($data['room_breakdown'] as $roomId => $roomData)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            {{ $roomData['name'] }}
                                                            <span class="badge badge-primary badge-pill">{{ $roomData['time'] }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($days) + 4 }}" class="text-center">No attendance data found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // You can add any additional JavaScript functionality here
        // For example, you might want to add sorting or filtering capabilities
    });
</script>
@endpush