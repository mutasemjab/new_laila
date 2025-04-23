<?php

namespace App\Observer;

use App\Models\AttendanceLog;

class AttendanceLogObserver
{
    /**
     * Handle the AttendanceLog "created" event.
     *
     * @param  \App\Models\AttendanceLog  attendanceLog
     * @return void
     */
    // public function creating(AttendanceLog $attendanceLog)
    // {
    //     if ($attendanceLog) {
    //         $attendanceLog->day_no = session('day_no',1);
    //         // $attendanceLog->save();
    //     }
    // }
}
