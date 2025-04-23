<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;



class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

   /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
   protected $guarded = [];

   /**
    * The attributes that should be hidden for serialization.
    *
    * @var array<int, string>
    */
    protected $hidden = [
      'password',
      'remember_token',
    ];

    public function categoryLabel($label_only=true)
    {
       $text        = '';
       $label_color = '';
       if($this->category == 1){
           $label_color = '#971010';
           $text        = $label_only ? '' : __('messages.Speaker');
        }elseif ($this->category == 2) {
           $label_color = '#0d173e';
           $text        = $label_only ? '' : __('messages.Participant');
        }elseif ($this->category == 3) {
           $label_color = '#2E50D6';
           $text        = $label_only ? '' : __('messages.Exhibitor');
        }elseif ($this->category == 4) {
           $label_color = '#145c1d';
           $text        = $label_only ? '' : __('messages.Committee');
        }elseif ($this->category == 5) {
           $label_color = '#6F437F';
           $text        = $label_only ? '' : __('messages.Press');
        }else {
           $label_color = '#835C3B';//835C3B
           $text        = $label_only ? '' : __('messages.Other');
        }
        return "<div class='text-center text-light badge-category' style='background-color:".$label_color.';'. ($label_only ? 'width: 26px;height: 26px;border-radius: 13px !important' : '').";'>".$text."</div>";
    }

    /**
        * Generate a unique barcode for the user.
    */
    public static function generateUniqueBarcode()
    {
        $barcode =  rand(10000000, 99999999);

        // Make sure the barcode is unique
        while (self::where('barcode', $barcode)->exists()) {
            $barcode =  rand(10000000, 99999999);
        }

        return $barcode;
    }

    public function attendanceLogHistories()
    {
        return $this->hasMany(AttendanceLogHistory::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function latestAttendance()
    {
        return $this->hasOne(AttendanceLog::class)->latestOfMany('time');
    }

    public function latestAttendanceHistories()
    {
        return $this->hasOne(AttendanceLogHistory::class)->latestOfMany('time');
    }

    public function getCurrentRoomAttribute()
    {
        $lastLog = $this->attendanceLogs()->orderBy('time', 'desc')->first();

        if ($lastLog && $lastLog->type == 'in') {
            return $lastLog->room;
        }

        return null;
    }

    public function calculateAveragePresence($room_id)
    {
        $room = Room::find($room_id);
        $query = $this->attendanceLogs()
            ->where(function($q) use ($room_id, $room) {
                if (!$room->is_main) {
                    $q->where('room_id', $room_id);
                }
            })
            ->orderBy('time', 'asc')
            ->get();

        $totalSeconds = 0;
        $count = 0;
        $lastInRecord = null;

        foreach ($query as $log) {
            if ($log->type == 'in') {
                $lastInRecord = $log;
            } else if ($log->type == 'out' && $lastInRecord) {
                $checkIn = Carbon::parse($lastInRecord->time);
                $checkOut = Carbon::parse($log->time);
                $seconds = $checkOut->diffInSeconds($checkIn);
                $totalSeconds += $seconds;
                $count++;
                $lastInRecord = null;
            }
        }

        // If the user is still checked in
        if ($lastInRecord) {
            $checkIn = Carbon::parse($lastInRecord->time);
            $checkOut = Carbon::now();
            $seconds = $checkOut->diffInSeconds($checkIn);
            $totalSeconds += $seconds;
            $count++;
        }

        // Format the average time
        $avgSeconds = $count > 0 ? ($totalSeconds / $count) : 0;
        $avgMinutes = round($avgSeconds / 60);

        // Format the total time
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        return [
            'avg' => $avgMinutes . ' دقيقة',
            'sum' => $hours . ' ساعة ' . $minutes . ' دقيقة'
        ];
    }

    // function calculateAveragePresenceHistory($roomId = null, $dayNo = null)
    // {
    //     // جلب السجلات الخاصة بالمستخدم، الغرفة، واليوم المطلوب مرتبة زمنياً
    //     $logs = AttendanceLogHistory::where(function($q) use($roomId,$dayNo) {
    //         $q->where('user_id', $this->id);
    //         if($roomId) $q->where('room_id', $roomId);
    //         if($dayNo) $q->where('day_no', $dayNo);
    //     })
    //     ->orderBy('time')
    //     ->get();

    //     $totalDuration = 0;
    //     $count = 0;
    //     $inTime = null;

    //     foreach ($logs as $log) {
    //         if ($log->type === 'in') {
    //             $inTime = $log->time;
    //         } elseif ($log->type === 'out' && $inTime !== null) {
    //             // حساب الفرق بين الخروج والدخول بالثواني
    //             $duration = Carbon::parse($log->time)->diffInSeconds(Carbon::parse($inTime));
    //             $totalDuration += $duration;
    //             $count++;
    //             $inTime = null; // إعادة التهيئة لتسجيل جولة جديدة
    //         }
    //     }

    //     if ($count === 0) {
    //         return[
    //            'avg' => 0,
    //            'sum' => 0,
    //         ];
    //     }

    //     return[
    //         // إرجاع المتوسط بالساعات
    //         'avg' => round($totalDuration / $count / 60 / 60, 2),
    //         // إرجاع الإجمالي بالساعات
    //         'sum' => round($totalDuration / 60 / 60, 2),
    //     ];
    // }


    public function calculateDetailedAttendanceHistory($roomId = null, $dayNo = null)
    {
        // Get all logs for this user (optionally filtered by room and day)
        $query = AttendanceLogHistory::where('user_id', $this->id);

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        if ($dayNo) {
            $query->where('day_no', $dayNo);
        }

        $logs = $query->orderBy('day_no')
                    ->orderBy('room_id')
                    ->orderBy('time')
                    ->get();

        // Group logs by day and room
        $attendanceByDayRoom = [];
        $totalDuration = 0;
        $sessionCount = 0;

        $currentDay = null;
        $currentRoom = null;
        $inTime = null;

        foreach ($logs as $log) {
            // If we've moved to a new day or room, reset the in-time
            if ($currentDay !== $log->day_no || $currentRoom !== $log->room_id) {
                $inTime = null;
                $currentDay = $log->day_no;
                $currentRoom = $log->room_id;

                // Initialize the day/room in our results array if needed
                if (!isset($attendanceByDayRoom[$currentDay])) {
                    $attendanceByDayRoom[$currentDay] = [
                        'total_seconds' => 0,
                        'rooms' => []
                    ];
                }

                if (!isset($attendanceByDayRoom[$currentDay]['rooms'][$currentRoom])) {
                    $attendanceByDayRoom[$currentDay]['rooms'][$currentRoom] = [
                        'room_name' => Room::find($currentRoom)->name ?? "Room $currentRoom",
                        'total_seconds' => 0,
                        'sessions' => 0
                    ];
                }
            }

            // Handle check-ins and check-outs
            if ($log->type === 'in') {
                $inTime = Carbon::parse($log->time);
            } elseif ($log->type === 'out' && $inTime !== null) {
                $outTime = Carbon::parse($log->time);
                $duration = $outTime->diffInSeconds($inTime);

                // Update counters
                $totalDuration += $duration;
                $sessionCount++;

                // Update day/room specific counters
                $attendanceByDayRoom[$currentDay]['total_seconds'] += $duration;
                $attendanceByDayRoom[$currentDay]['rooms'][$currentRoom]['total_seconds'] += $duration;
                $attendanceByDayRoom[$currentDay]['rooms'][$currentRoom]['sessions']++;

                $inTime = null;
            }
        }

        // Format the results
        $overall = [
            'total_hours' => floor($totalDuration / 3600),
            'total_minutes' => floor(($totalDuration % 3600) / 60),
            'total_seconds' => $totalDuration,
            'session_count' => $sessionCount,
            'average_session_minutes' => $sessionCount > 0 ? round($totalDuration / $sessionCount / 60) : 0
        ];

        // Format day and room durations
        foreach ($attendanceByDayRoom as $day => &$dayData) {
            $dayData['total_hours'] = floor($dayData['total_seconds'] / 3600);
            $dayData['total_minutes'] = floor(($dayData['total_seconds'] % 3600) / 60);

            foreach ($dayData['rooms'] as &$roomData) {
                $roomData['total_hours'] = floor($roomData['total_seconds'] / 3600);
                $roomData['total_minutes'] = floor(($roomData['total_seconds'] % 3600) / 60);
                $roomData['formatted_time'] = $roomData['total_hours'] . ' ساعة ' . $roomData['total_minutes'] . ' دقيقة';
            }

            $dayData['formatted_time'] = $dayData['total_hours'] . ' ساعة ' . $dayData['total_minutes'] . ' دقيقة';
        }

        return [
            'overall' => $overall,
            'formatted_total_time' => $overall['total_hours'] . ' ساعة ' . $overall['total_minutes'] . ' دقيقة',
            'by_day' => $attendanceByDayRoom
        ];
    }

    public function calculateAveragePresenceHistory($roomId = null, $dayNo = null)
    {
        // Get all logs for this user (optionally filtered by room and day)
        $query = AttendanceLogHistory::where('user_id', $this->id);

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        if ($dayNo) {
            $query->where('day_no', $dayNo);
        }

        $logs = $query->orderBy('time')->get();

        $totalDuration = 0;
        $count = 0;
        $inTime = null;
        $currentRoom = null;
        $currentDay = null;

        foreach ($logs as $log) {
            // Reset in-time if we've changed room or day
            if ($currentRoom !== $log->room_id || $currentDay !== $log->day_no) {
                $inTime = null;
                $currentRoom = $log->room_id;
                $currentDay = $log->day_no;
            }

            if ($log->type === 'in') {
                $inTime = Carbon::parse($log->time);
            } elseif ($log->type === 'out' && $inTime !== null) {
                $outTime = Carbon::parse($log->time);
                $duration = $outTime->diffInSeconds($inTime);
                $totalDuration += $duration;
                $count++;
                $inTime = null;
            }
        }

        // Calculate hours and minutes
        $totalHours = floor($totalDuration / 3600);
        $totalMinutes = floor(($totalDuration % 3600) / 60);

        // Calculate average if we have any sessions
        $avgHours = 0;
        $avgMinutes = 0;
        if ($count > 0) {
            $avgSeconds = $totalDuration / $count;
            $avgHours = floor($avgSeconds / 3600);
            $avgMinutes = floor(($avgSeconds % 3600) / 60);
        }

        return [
            'avg' => $avgHours . ' ساعة ' . $avgMinutes . ' دقيقة',
            'sum' => $totalHours . ' ساعة ' . $totalMinutes . ' دقيقة'
        ];
    }


    public function dailyAttendanceMetrics()
{
    return $this->hasMany(DailyAttendanceMetric::class);
}

/**
 * Get the user's room-specific attendance metrics
 */
public function roomAttendanceMetrics()
{
    return $this->hasMany(RoomAttendanceMetric::class);
}

/**
 * Calculate user's total time across all days
 */
public function getTotalAttendanceTimeAttribute()
{
    $totalSeconds = $this->dailyAttendanceMetrics->sum('total_time_seconds');
    return $this->formatDuration($totalSeconds);
}

/**
 * Calculate user's average daily attendance time
 */
public function getAverageDailyAttendanceTimeAttribute()
{
    $metrics = $this->dailyAttendanceMetrics;
    if ($metrics->count() === 0) return '0 ثانية';

    $totalSeconds = $metrics->sum('total_time_seconds');
    $averageSeconds = round($totalSeconds / $metrics->count());
    return $this->formatDuration($averageSeconds);
}

/**
 * Calculate user's total time in a specific room across all days
 */
public function getTotalTimeInRoom($roomId)
{
    $totalSeconds = $this->roomAttendanceMetrics()
        ->where('room_id', $roomId)
        ->sum('time_spent_seconds');

    return $this->formatDuration($totalSeconds);
}

private function formatDuration($seconds)
{
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

}
