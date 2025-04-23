<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceLogHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'room_id',
        'time',
        'type',
        'day_no',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
    */
    protected $casts = [
        'time' => 'datetime',
    ];

    /**
     * Get the user that owns the attendance log.
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public static function getUserAttendanceByDay($userId)
    {
        $results = [];

        // Get all days with attendance for this user
        $days = self::where('user_id', $userId)
                    ->select('day_no')
                    ->distinct()
                    ->get()
                    ->pluck('day_no');

        foreach ($days as $dayNo) {
            // Calculate main conference attendance for this day
            $mainRoom = Room::where('is_main', true)->first();

            if ($mainRoom) {
                $mainRoomStats = self::calculateAveragePresenceHistory($userId, $mainRoom->id, $dayNo);

                // Calculate attendance in specific rooms for this day
                $roomStats = [];
                $nonMainRooms = Room::where('is_main', false)->get();

                foreach ($nonMainRooms as $room) {
                    $stats = self::calculateAveragePresenceHistory($userId, $room->id, $dayNo);
                    if ($stats['count'] > 0) {
                        $roomStats[$room->id] = [
                            'name' => $room->name,
                            'duration' => $stats['sum'],
                            'seconds' => $stats['sum_seconds']
                        ];
                    }
                }

                $results[$dayNo] = [
                    'day_number' => $dayNo,
                    'total_time' => $mainRoomStats['sum'],
                    'total_seconds' => $mainRoomStats['sum_seconds'],
                    'rooms' => $roomStats
                ];
            }
        }

        // Add overall total across all days
        $overallTotal = self::calculateAveragePresenceHistory($userId, null, null);
        $results['overall'] = [
            'total_time' => $overallTotal['sum'],
            'total_seconds' => $overallTotal['sum_seconds']
        ];

        return $results;
    }
    static function calculateAveragePresenceHistory($userId = null, $roomId = null, $dayNo = null)
    {
        // جلب السجلات الخاصة بالمستخدم، الغرفة، واليوم المطلوب مرتبة زمنياً
        $logs = AttendanceLogHistory::where(function($q) use($userId,$roomId,$dayNo) {
            if($userId) $q->where('user_id', $userId);
            if($roomId) $q->where('room_id', $roomId);
            if($dayNo) $q->where('day_no', $dayNo);
        })
        ->orderBy('time')
        ->get();

        $totalDuration = 0;
        $count = 0;
        $inTime = null;

        foreach ($logs as $log) {
            if ($log->type === 'in') {
                $inTime = $log->time;
            } elseif ($log->type === 'out' && $inTime !== null) {
                // حساب الفرق بين الخروج والدخول بالثواني
                $duration = Carbon::parse($log->time)->diffInSeconds(Carbon::parse($inTime));
                $totalDuration += $duration;
                $count++;
                $inTime = null; // إعادة التهيئة لتسجيل جولة جديدة
            }
        }

        if ($count === 0) {
            return[
                // إرجاع المتوسط بالساعات
                'avg' => 0,
                // إرجاع الإجمالي بالساعات
               'sum' => round(($totalDuration / 60)/60, 2),
            ];
        }

        return[
            // إرجاع المتوسط بالساعات
            'avg' => round($totalDuration /( $count / 60)/60, 2),
            // إرجاع الإجمالي بالساعات
            'sum' => round(($totalDuration / 60)/60, 2),
        ];
    }

}
