@if(isset($users) && $users->count())
@foreach ($users as $user)
    <tr>
        <td>{{ $user->id }}</td>
        <td>{{ $user->title .' '. $user->first_name }}</td>
        <td>
            @if($user->latestAttendance->type == 'in')
                In <i class="fas fa-sign-in-alt text-success"></i>
            @else
                <i class="fas fa-sign-out text-danger"></i> Out
            @endif
        </td>
        <td>{!! $user->categoryLabel(0) !!}</td>
        <!-- <td>{{ $user->email }}</td> -->
        <td>{{ $user->barcode }}</td>
        @if(!$room->is_main)
        <td>{!! $user->calculateAveragePresence($user->room_id)['avg'] !!}</td>
        <td>{!! $user->calculateAveragePresence($user->room_id)['sum'] !!}</td>
        @endif
        <td>
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
            <a href="{{ route('user-time.show', $user->id) }}" class="btn btn-sm btn-secondary">Attendance</a>

        </td>
    </tr>
    @endforeach
@else
  <tr> No user found for this search ddss</tr>
@endif
