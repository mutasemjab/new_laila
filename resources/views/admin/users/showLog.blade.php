@extends('layouts.admin')
@section('title')
سجل حضور {{ $user->name }}
@endsection

@section('css')
<style>
    .user-profile {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .user-header {
        background: linear-gradient(135deg, #0063e5 0%, #0084ff 100%);
        color: white;
        padding: 25px;
        position: relative;
    }

    .user-header h2 {
        margin: 0;
        font-weight: 600;
    }

    .user-info {
        list-style: none;
        padding: 0;
        margin: 15px 0 0 0;
        display: flex;
        gap: 30px;
    }

    .user-info li {
        display: flex;
        align-items: center;
    }

    .user-info li i {
        margin-left: 8px;
        opacity: 0.8;
    }

    .back-button {
        padding: 8px 20px;
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
        border: none;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 15px;
        display: inline-flex;
        align-items: center;
    }

    .back-button:hover {
        background-color: rgba(255, 255, 255, 0.3);
    }

    .back-button i {
        margin-left: 5px;
    }

    .room-card {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .room-header {
        padding: 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .room-name {
        font-weight: 600;
        font-size: 1.2rem;
        color: #333;
        display: flex;
        align-items: center;
    }

    .room-name i {
        color: #0084ff;
        margin-left: 10px;
        font-size: 1.5rem;
    }

    .room-total {
        background-color: rgba(0, 132, 255, 0.1);
        color: #0084ff;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
    }

    .visit-list {
        padding: 0;
        list-style: none;
        margin: 0;
    }

    .visit-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f5f5f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .visit-item:last-child {
        border-bottom: none;
    }

    .visit-time {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .visit-time-item {
        display: flex;
        flex-direction: column;
    }

    .visit-time-label {
        font-size: 0.8rem;
        color: #666;
        margin-bottom: 3px;
    }

    .visit-time-value {
        font-weight: 600;
        color: #333;
    }

    .visit-duration {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 600;
    }

    .visit-duration.active {
        background-color: rgba(0, 132, 255, 0.1);
        color: #0084ff;
    }

    .no-records {
        padding: 30px;
        text-align: center;
        color: #666;
    }

    .no-records i {
        font-size: 3rem;
        color: #ddd;
        margin-bottom: 15px;
        display: block;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        padding: 20px;
        display: flex;
        align-items: center;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        background-color: rgba(0, 132, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 15px;
    }

    .stat-icon i {
        font-size: 1.8rem;
        color: #0084ff;
    }

    .stat-content h3 {
        margin: 0 0 5px 0;
        font-size: 0.9rem;
        color: #666;
    }

    .stat-content p {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
        color: #333;
    }

    .time-arrow {
        color: #aaa;
        font-size: 1.2rem;
        margin: 0 10px;
    }

    @media (max-width: 768px) {
        .visit-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }

        .visit-time {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
@endsection

@section('contentheaderlink')
<a href="{{ route('users.index') }}"> سجل حضور المستخدمين </a>
@endsection

@section('contentheaderactive')
{{ $user->name }}
@endsection

@section('content')
@php
function formatDuration($seconds) {
    if ($seconds < 60) {
        return $seconds . ' ثانية';
    }

    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;

    if ($minutes < 60) {
        return $minutes . ' دقيقة ' . ($remainingSeconds > 0 ? 'و ' . $remainingSeconds . ' ثانية' : '');
    }

    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;

    $formattedTime = $hours . ' ساعة';

    if ($remainingMinutes > 0) {
        $formattedTime .= ' و ' . $remainingMinutes . ' دقيقة';
    }

    if ($remainingSeconds > 0 && $remainingMinutes == 0) {
        $formattedTime .= ' و ' . $remainingSeconds . ' ثانية';
    }

    return $formattedTime;
}
@endphp
<div class="container-fluid">
    <!-- User Profile Card -->
    <div class="user-profile">
        <div class="user-header">
            <a href="{{ route('users.index') }}" class="back-button">
                <i class="fas fa-chevron-right"></i>
                العودة للقائمة
            </a>
            <h2>سجل حضور {{ $user->name }}</h2>
            <ul class="user-info">
                <li><i class="fas fa-phone"></i> {{ $user->phone }}</li>
                <li><i class="fas fa-barcode"></i> {{ $user->barcode }}</li>
                <li>
                    <i class="fas fa-circle {{ $user->activate == 1 ? 'text-success' : 'text-danger' }}"></i>
                    {{ $user->activate == 1 ? 'نشط' : 'غير نشط' }}
                </li>
            </ul>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="stat-content">
                <h3>عدد الغرف التي تم زيارتها</h3>
                <p>{{ count($roomTimeLogs) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <div class="stat-content">
                <h3>عدد مرات تسجيل الدخول</h3>
                <p>{{ array_sum(array_map(function($room) { return count($room['visits']); }, $roomTimeLogs)) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>إجمالي الوقت</h3>
                <p>
                    {{
                        formatDuration(
                            array_sum(array_map(function($room) {
                                return $room['total_seconds'];
                            }, $roomTimeLogs))
                        )
                    }}
                </p>
            </div>
        </div>
    </div>

    <!-- Room Time Logs -->
    @if(count($roomTimeLogs) > 0)
        @foreach($roomTimeLogs as $roomLog)
        <div class="room-card">
            <div class="room-header">
                <div class="room-name">
                    <i class="fas fa-door-open"></i>
                    {{ $roomLog['room']->name }}
                </div>
                <div class="room-total">
                    إجمالي الوقت: {{ $roomLog['total_time'] }}
                </div>
            </div>

            <ul class="visit-list">
                @foreach($roomLog['visits'] as $visit)
                <li class="visit-item">
                    <div class="visit-time">
                        <div class="visit-time-item">
                            <span class="visit-time-label">تسجيل الدخول</span>
                            <span class="visit-time-value">{{ $visit['check_in_time']->format('Y-m-d g:i A') }}</span>
                        </div>

                        <span class="time-arrow">
                            <i class="fas fa-arrow-left"></i>
                        </span>

                        <div class="visit-time-item">
                            <span class="visit-time-label">تسجيل الخروج</span>
                            <span class="visit-time-value">
                                {{ $visit['check_out_time'] ? $visit['check_out_time']->format('Y-m-d g:i A') : 'لم يتم الخروج بعد' }}
                            </span>
                        </div>
                    </div>

                    <div class="visit-duration {{ !$visit['check_out_time'] ? 'active' : '' }}">
                        {{ $visit['duration'] }}
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endforeach
    @else
        <div class="no-records">
            <i class="fas fa-clock"></i>
            <p>لا يوجد سجلات حضور لهذا المستخدم</p>
        </div>
    @endif



    <!-- history start -->

    <div class="py-5">
        <hr>
        <h4 class="pt-4">
            تاريخ  سجل الحضور
        </h4>
    </div>

    <!-- Stats -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-door-open"></i>
            </div>
            <div class="stat-content">
                <h3>عدد الغرف التي تم زيارتها</h3>
                <p>{{ count($roomTimeLogs) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <div class="stat-content">
                <h3>عدد مرات تسجيل الدخول</h3>
                <p>{{ array_sum(array_map(function($room) { return count($room['visits']); }, $roomTimeLogs)) }}</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>إجمالي الوقت</h3>
                <p>
                    {{
                        formatDuration(
                            array_sum(array_map(function($room) {
                                return $room['total_seconds'];
                            }, $roomTimeLogs))
                        )
                    }}
                </p>
            </div>
        </div>
    </div>
    <!-- Room Time Logs -->

    @if(count($roomTimeLogshistory) > 0)
        @foreach($roomTimeLogshistory as $roomLog)
        <div class="room-card">
            <div class="room-header">
                <div class="room-name">
                    <i class="fas fa-door-open"></i>
                    {{ $roomLog['room']->name }}
                </div>
                <div class="room-total">
                    إجمالي الوقت: {{ $roomLog['total_time'] }}
                </div>
            </div>

            <ul class="visit-list">
                @foreach($roomLog['visits'] as $visit)
                <li class="visit-item">
                    <div class="visit-time">
                        <div class="visit-time-item">
                            <span class="visit-time-label">تسجيل الدخول</span>
                            <span class="visit-time-value">{{ $visit['check_in_time']->format('Y-m-d g:i A') }}</span>
                        </div>

                        <span class="time-arrow">
                            <i class="fas fa-arrow-left"></i>
                        </span>

                        <div class="visit-time-item">
                            <span class="visit-time-label">تسجيل الخروج</span>
                            <span class="visit-time-value">
                                {{ $visit['check_out_time'] ? $visit['check_out_time']->format('Y-m-d g:i A') : 'لم يتم الخروج بعد' }}
                            </span>
                        </div>
                    </div>

                    <div class="visit-duration {{ !$visit['check_out_time'] ? 'active' : '' }}">
                        {{ $visit['duration'] }}
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endforeach
    @else
        <div class="no-records">
            <i class="fas fa-clock"></i>
            <p>لا يوجد سجلات حضور لهذا المستخدم</p>
        </div>
    @endif
    <!-- history end -->



</div>
@endsection

@section('script')
<script>
    // Format duration helper function for JS
    function formatDuration(seconds) {
        if (seconds < 60) {
            return seconds + ' ثانية';
        }

        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;

        if (minutes < 60) {
            return minutes + ' دقيقة ' + (remainingSeconds > 0 ? 'و ' + remainingSeconds + ' ثانية' : '');
        }

        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;

        let formattedTime = hours + ' ساعة';

        if (remainingMinutes > 0) {
            formattedTime += ' و ' + remainingMinutes + ' دقيقة';
        }

        if (remainingSeconds > 0 && remainingMinutes == 0) {
            formattedTime += ' و ' + remainingSeconds + ' ثانية';
        }

        return formattedTime;
    }
</script>
@endsection
