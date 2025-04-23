<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time' => 'datetime',
    ];


    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class,'day_no');
    }

    public function attendanceHistory()
    {
        return $this->hasMany(attendanceLogHistory::class,'day_no');
    }

    public function dailyAttendanceMetrics()
    {
        return $this->hasMany(DailyAttendanceMetric::class);
    }
}
