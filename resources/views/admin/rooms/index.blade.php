@extends('layouts.admin')
@section('title')
    {{ __('messages.rooms') }}
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between">
                        <h4>rooms</h4>
                        <a href="{{ route('rooms.create') }}" class="btn btn-primary">Create New room</a>
                    </div>
                </div>

                <div class="card-body">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $room)
                                <tr>
                                    <td>{{ $room->id }}</td>
                                    <td>{{ $room->name }}</td>

                                    <td>
                                        <a href="{{ route('room.attandance',[$room->id,$room->slug()]) }}" class="btn btn-sm btn-info">View</a>
                                        <a href="{{ route('rooms.edit', $room->id) }}" class="btn btn-sm btn-primary">Edit</a>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


