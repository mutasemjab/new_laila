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
        $label_color = '#835C3B';
        $text        = $label_only ? '' : __('messages.Other');
        }
        
        // If it's a dot (label_only), keep existing style
        if ($label_only) {
            return "<div class='text-center text-light badge-category' style='background-color:".$label_color.";width: 26px;height: 26px;border-radius: 13px !important;'>".$text."</div>";
        } else {
            // For full labels, make it more prominent with increased height
            return "<div class='text-center text-light badge-category' style='background-color:".$label_color.";padding: 15px 6px;font-size: 18px;'>".$text."</div>";
        }
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





}
