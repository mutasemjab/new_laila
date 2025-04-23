<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomAttendanceMetric extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'day_id',
        'room_id',
        'time_spent_seconds'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function day()
    {
        return $this->belongsTo(Day::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function getFormattedTimeSpentAttribute()
    {
        return $this->formatDuration($this->time_spent_seconds);
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
