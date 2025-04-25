@if(isset($users) && $users->count())
    @foreach ($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>
                @php
                    // Get the latest attendance log for this user in this room
                    $latestLog = $user->attendanceLogs->first();
                @endphp
                
                @if($latestLog && $latestLog->type == 'in')
                    In <i class="fas fa-sign-in-alt text-success"></i>
                @else
                    <i class="fas fa-sign-out text-danger"></i> Out
                @endif
            </td>
            <td>{!! $user->categoryLabel(0) !!}</td>
            <td>{{ $user->barcode }}</td>
            <td>{!! $user->calculateAveragePresence($room->id)['avg'] !!}</td>
            <td>{!! $user->calculateAveragePresence($room->id)['sum'] !!}</td>
            <td>
                <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a>
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                <a href="{{ route('user-time.show', $user->id) }}" class="btn btn-sm btn-secondary">Attendance</a>
            </td>
        </tr>
    @endforeach
@endif