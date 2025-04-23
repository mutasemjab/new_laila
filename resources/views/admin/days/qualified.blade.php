@extends('layouts.admin')
@section('title')
{{ __('messages.Qualified users') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .rooms-container {
        padding: 25px;
        background-color: #f5f9fc;
        border-radius: 15px;
        min-height: 80vh;
    }

    .rooms-grid {
        display: grid;
        /* grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); */
        gap: 25px;
        margin-top: 20px;
    }

    .room-card {
        background: linear-gradient(135deg, #ffffff 0%, #f9fdff 100%);
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 123, 255, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        height: 220px;
        border: 1px solid rgba(0, 123, 255, 0.1);
    }

    .room-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 123, 255, 0.15);
        border-color: rgba(0, 123, 255, 0.3);
    }

    .room-header {
        padding: 20px;
        color: #fff;
        background: linear-gradient(135deg, #0063e5 0%, #0084ff 100%);
        position: relative;
        height: 50%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .room-header h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .room-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2.5rem;
        opacity: 0.5;
    }

    .room-details {
        padding: 20px;
        height: 50%;
    }

    .room-stat {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .room-stat-label {
        color: #555;
        font-size: 0.9rem;
    }

    .room-stat-value {
        font-weight: 600;
        color: #0084ff;
        font-size: 1.1rem;
    }

    /* Scanner Modal */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-container {
        background-color: #fff;
        width: 90%;
        max-width: 500px;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        transform: scale(0.8);
        transition: all 0.3s ease;
    }

    .modal-overlay.active .modal-container {
        transform: scale(1);
    }

    .modal-header {
        background: linear-gradient(135deg, #0063e5 0%, #0084ff 100%);
        color: #fff;
        padding: 20px;
        position: relative;
    }

    .modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
    }

    .modal-close {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 1.5rem;
    }

    .modal-body {
        padding: 25px;
        text-align: center;
    }

    .barcode-input-container {
        margin: 20px 0;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .barcode-input {
        padding: 15px;
        border: 2px solid #ddd;
        border-radius: 10px;
        font-size: 1.2rem;
        width: 100%;
        text-align: center;
        transition: all 0.3s ease;
        background-color: #f9fdff;
    }

    .barcode-input:focus {
        border-color: #0084ff;
        box-shadow: 0 0 0 3px rgba(0, 132, 255, 0.2);
        outline: none;
    }

    .btn-scan {
        padding: 12px 25px;
        background: linear-gradient(135deg, #0063e5 0%, #0084ff 100%);
        color: #fff;
        border: none;
        border-radius: 30px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        width: 100%;
        margin-top: 15px;
    }

    .btn-scan:hover {
        box-shadow: 0 8px 20px rgba(0, 123, 255, 0.4);
        transform: translateY(-2px);
    }

    .scan-status {
        margin-top: 15px;
        padding: 15px;
        border-radius: 8px;
        display: none;
    }

    .scan-status.success {
        background-color: rgba(40, 167, 69, 0.1);
        border: 1px solid rgba(40, 167, 69, 0.2);
        color: #28a745;
        display: block;
    }

    .scan-status.error {
        background-color: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.2);
        color: #dc3545;
        display: block;
    }

    .scan-status.loading {
        background-color: rgba(0, 123, 255, 0.1);
        border: 1px solid rgba(0, 123, 255, 0.2);
        color: #0084ff;
        display: block;
    }

    /* Dashboard Header */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .dashboard-title {
        display: flex;
        align-items: center;
    }

    .dashboard-title i {
        font-size: 1.5rem;
        color: #0084ff;
        margin-left: 10px;
    }

    .dashboard-title h1 {
        margin: 0;
        font-size: 1.8rem;
        color: #333;
    }

    .dashboard-filters {
        display: flex;
        gap: 15px;
    }

    .filter-btn {
        padding: 8px 15px;
        border-radius: 20px;
        background-color: #fff;
        color: #555;
        border: 1px solid #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-btn.active {
        background-color: #0084ff;
        color: #fff;
        border-color: #0084ff;
    }

    /* User Feedback Toast */
    .toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background-color: #fff;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 15px;
        z-index: 99999;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
    }

    .toast.active {
        transform: translateY(0);
        opacity: 1;
    }

    .toast i {
        font-size: 1.5rem;
    }

    .toast.success i {
        color: #28a745;
    }

    .toast.error i {
        color: #dc3545;
    }

    .toast-message {
        font-weight: 500;
    }

    /* Stats Cards at Top */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        padding: 20px;
        display: flex;
        align-items: center;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.5rem;
    }

    .stat-icon.blue {
        background-color: rgba(0, 123, 255, 0.1);
        color: #0084ff;
    }

    .stat-icon.green {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }

    .stat-icon.orange {
        background-color: rgba(255, 153, 0, 0.1);
        color: #ff9900;
    }

    .stat-icon.purple {
        background-color: rgba(111, 66, 193, 0.1);
        color: #6f42c1;
    }

    .stat-icon.red {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .stat-content h3 {
        margin: 0;
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 5px;
    }

    .stat-content p {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
    }

    /* Refresh button */
    .refresh-stats {
        background: none;
        border: none;
        color: #0084ff;
        cursor: pointer;
        font-size: 1rem;
        display: flex;
        align-items: center;
        padding: 8px 15px;
        border-radius: 20px;
        transition: all 0.3s ease;
        margin-right: 15px;
    }

    .refresh-stats:hover {
        background-color: rgba(0, 123, 255, 0.1);
    }

    .refresh-stats i {
        margin-left: 5px;
    }

    /* Animation for refresh icon */
    .fa-sync-alt {
        transition: transform 0.5s ease;
    }

    .refresh-stats:hover .fa-sync-alt {
        transform: rotate(180deg);
    }

    .refreshing .fa-sync-alt {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('contentheaderlink')
<a href="{{ route('admin.dashboard') }}">{{ __('messages.Home') }} </a>
@endsection

@section('contentheaderactive')
<p> {{ __('messages.Qualified users') }} </p>
@endsection

@section('content')
<div class="rooms-container">
    <div class="dashboard-header">
        <div class="dashboard-title">
            <i class="fa fa-certificate"></i>
            <h1>{{ __('messages.Qualified users') }}</h1>
        </div>
    </div>

    <div id='day-users'>
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="stat-content">
                    <h3>إجمالي عمليات التسجيل</h3>
                    <p id="total-check-ins">{{ $totalCheckIns }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fa fa-certificate"></i>
                </div>
                <div class="stat-content">
                    <h3>إجمالي المستحقين</h3>
                    <p id="total-users">{{ $users->count() }}</p>
                </div>
            </div>

        </div>


        <div class="row justify-content-center pt-5">
            <div class="col-md-12">
                <div class="row py-3"><div class="col-lg-6">
                    <div class="dashboard-filters">
                        <span class="pt-2">{{ __('messages.Hours') }}</span>
                        <select name="minHours" id='minHours' class='attandance-users-search form-control rounded mt-1 '>
                            <option value="all">{{ __('messages.Select') }}</option>
                            <option value="10"> 10 ساعات </option>
                            <option value="15"> 15 ساعة </option>
                            <option selected value="20"> 20 ساعة </option>
                            <option value="25"> 25 ساعة </option>
                            <option value="30"> 30 ساعة </option>
                            <option value="35"> 35 ساعة </option>
                            <option value="40"> 40 ساعة </option>
                            <option value="45"> 45 ساعة </option>
                            <option value="50"> 50 ساعة </option>
                        </select>
                    </div>
                </div></div>

                <div class="card">

                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h4>Users</h4>
                            <a href="javascript:void(0)" class="btn btn-primary" id='update-users'>تحديث البيانات</a>
                        </div>
                    </div>

                    <div class="card-body">

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        ID
                                        <div class="col-12 p-0 pt-3">#</div>
                                    </th>
                                    <th>Name <input id='attandance-users-name' class='attandance-users-search form-control rounded mt-1' type="text"></th>
                                    <th>
                                        Category
                                        <select name="category" id='attandance-users-category' class='attandance-users-search form-control rounded mt-1'>
                                            <option value="all">{{ __('messages.Select') }}</option>
                                            <option value="1">{{ __('messages.Speaker') }}</option>
                                            <option value="2">{{ __('messages.Participant') }}</option>
                                        </select>
                                    </th>
                                    <!-- <th>Email <input id='attandance-users-email' class='attandance-users-search form-control rounded mt-1' type="text"></th> -->
                                    <th>Barcode <input id='attandance-users-barcode' class='attandance-users-search form-control rounded mt-1' type="text"></th>
                                    <th>متوسط الحضور  <i class="fa fa-clock text-success"></i> </th>
                                    <th>إجمالي الحضور  <i class="fa fa-clock text-success"></i> </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id='attandance-users'>
                            @if(isset($users) && $users->count())
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->title .' '. $user->first_name }}</td>
                                        <td>{!! $user->categoryLabel(0) !!}</td>
                                        <!-- <td>{{ $user->email }}</td> -->
                                        <td>{{ $user->barcode }}</td>
                                        <!-- AttendanceLogHistory -->
                                        <td>{!! $user->calculateAveragePresenceHistory()['avg'] !!}</td>
                                        <td>{!! $user->calculateAveragePresenceHistory()['sum'] !!}</td>
                                        <td>
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                            <a href="{{ route('user-time.show', $user->id) }}" class="btn btn-sm btn-secondary">Attendance</a>
                                            <!-- <a href="{{ route('user-time.show', $user->id) }}" class="btn btn-sm btn-secondary">Attendance</a> -->
                                           
                                            <form method="POST" action="{{ route('certificate.download') }}">
                                                @csrf
                                                <input type="text" name="name" value="{{ $user->name }}" hidden>
                                                <input type="text" name="id" value="{{ $user->id }}" hidden>
                                                <button type="submit">Give the Certificate</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        @if(isset($users) && $users->count()) {{ $users->links() }} @endif
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Toast Notification -->
<div class="toast" id="toast">
    <i class="fas fa-check-circle"></i>
    <div class="toast-message">تم تسجيل الدخول بنجاح</div>
</div>

@endsection

@section('script')
<script>

document.addEventListener('DOMContentLoaded', function() {
    const toast = document.getElementById('toast');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const updateUsers = document.getElementById('update-users');

    const attendance = document.getElementById('total-attendance');

    // Helper function to get the CSRF token
    function getCSRFToken() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        let token = '';

        if (csrfToken) {
            token = csrfToken.getAttribute('content');
        } else {
            console.error('CSRF token meta tag not found');
            // Try to get it from a form if available
            const tokenInput = document.querySelector('input[name="_token"]');
            if (tokenInput) {
                token = tokenInput.value;
            }
        }

        return token;
    }


    function fetchAttandanceUsersToRoom() {

        var category = document.getElementById('attandance-users-category').value;
        var barcode  = document.getElementById('attandance-users-barcode').value;
        var name     = document.getElementById('attandance-users-name').value;
        var minHours = document.getElementById('minHours').value;

        fetch('{{ route("day.get.qualified-users") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCSRFToken()
            },
            body: JSON.stringify({
                barcode: barcode.trim(),
                category: category,
                name: name,
                minHours: minHours,
            })
        })
        .then(response => response.text()) // Get the response as text
        .then(response => {
            if (response) {
                document.getElementById('attandance-users').innerHTML = response;
            }
        })
        .catch(error => {
            console.error('Error fetching statistics:', error);
        });
    }

    // up date users on click
    updateUsers.addEventListener('click', function() {
        fetchAttandanceUsersToRoom();
    });

});
</script>
@endsection
