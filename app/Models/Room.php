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

    
    
}
