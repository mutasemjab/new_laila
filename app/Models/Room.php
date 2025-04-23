<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'last_check_in' => 'datetime',
    ];

    public function slug()
    {
        return str_replace(' ','-',trim($this->name));
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function getCurrentOccupancyAttribute()
    {
        $roomId = (int) $this->id; // cast to int for safety
    
        $latestLogs = \DB::table('attendance_logs as a')
            ->select('a.user_id')
            ->join(
                \DB::raw("(SELECT user_id, MAX(time) as max_time FROM attendance_logs WHERE room_id = {$roomId} GROUP BY user_id) as latest"),
                function ($join) {
                    $join->on('a.user_id', '=', 'latest.user_id')
                         ->on('a.time', '=', 'latest.max_time');
                }
            )
            ->where('a.type', 'in')
            ->where('a.room_id', $roomId)
            ->pluck('a.user_id');
    
        return count($latestLogs);
    }
    
    
}
