{{-- this code when i filter the time in qualified --}}
@if(isset($users) && $users->count())
@foreach ($users as $user)
    <tr>
        <td>{{ $user->id }}</td>
        <td>{{  $user->name }}</td>
        <td>{!! $user->categoryLabel(0) !!}</td>
        <!-- <td>{{ $user->email }}</td> -->
        <td>{{  $user->barcode }}</td>
        <td>{!! $user->calculateAveragePresenceHistory()['avg'] !!}</td>
        <td>{!! $user->calculateAveragePresenceHistory()['sum'] !!}</td>
        <td>
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a>
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
            <a href="{{ route('user-time.show', $user->id) }}" class="btn btn-sm btn-secondary">Attendance</a>

        </td>
    </tr>
    @endforeach
@else
  <tr> No user found for this search</tr>
@endif
