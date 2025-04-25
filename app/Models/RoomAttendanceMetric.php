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

}
