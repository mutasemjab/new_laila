<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AttendanceLog;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get all rooms - current_occupancy is now stored in the database
        $rooms = Room::all();

        // Get total number of users currently checked in across all rooms
        $totalActiveUsers = $rooms->sum('current_occupancy');

        // Get number of active rooms (rooms with at least one person)
        $activeRooms = $rooms->where('current_occupancy', '>', 0)->count();

        // Get total number of registered users
        $totalUsers = User::where('activate', 1)->count();

        // Calculate average time spent in rooms
        $avgTime = $this->calculateAverageTime();

        // Get total check-ins across all rooms
        $totalCheckIns = User::whereHas('attendanceLogs',function ($query) {
            $query->where('type', 'in');
        })->count();

        return view('admin.dashboard', compact(
            'rooms',
            'totalActiveUsers',
            'activeRooms',
            'totalUsers',
            'avgTime',
            'totalCheckIns'
        ));
    }

    private function calculateAverageTime()
    {
        // Get all completed room visits (pairs of check-in and check-out)
        $completedVisits = DB::select("
            SELECT
                a.user_id,
                a.room_id,
                a.time as check_in_time,
                MIN(b.time) as check_out_time
            FROM
                attendance_logs a
            JOIN
                attendance_logs b ON a.user_id = b.user_id AND a.room_id = b.room_id
                AND b.time > a.time AND b.type = 'out'
            JOIN
                rooms r ON a.room_id = r.id
            WHERE
                r.is_main = false AND
                a.type = 'in'
            GROUP BY
                a.id, a.user_id, a.room_id, a.time
        ");

        if (empty($completedVisits)) {
            return '0 دقيقة';
        }

        $totalSeconds = 0;
        $count = 0;

        foreach ($completedVisits as $visit) {
            $checkIn = Carbon::parse($visit->check_in_time);
            $checkOut = Carbon::parse($visit->check_out_time);
            $totalSeconds += $checkOut->diffInSeconds($checkIn);
            $count++;
        }

        if ($count === 0) {
            return '0 دقيقة';
        }

        $avgSeconds = $totalSeconds / $count;
        $minutes = round($avgSeconds / 60);

        return $minutes . ' دقيقة';
    }

    // Method to get real-time dashboard statistics via AJAX
    public function getStatistics()
    {
        // Get all rooms
        $rooms = Room::all();

        // Transform each room for JSON response
        $roomsData = $rooms->map(function ($room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'current_occupancy' => $room->current_occupancy,
                'last_check_in' => $room->last_check_in ? $room->last_check_in->toDateTimeString() : null,
                'last_check_in_human' => $room->last_check_in ? $room->last_check_in->diffForHumans() : null
            ];
        });

        // Count users currently checked in
        $totalActiveUsers = $rooms->sum('current_occupancy');

        // Count rooms with at least one person
        $activeRooms = $rooms->where('current_occupancy', '>', 0)->count();

        // Get total users
        $totalUsers = User::where('activate', 1)->count();

        // Get total check-ins
        $totalCheckIns = User::whereHas('attendanceLogs',function ($query) {
            $query->where('type', 'in');
        })->count();

        // Calculate average time
        $avgTime = $this->calculateAverageTime();

        return response()->json([
            'totalActiveUsers' => $totalActiveUsers,
            'activeRooms' => $activeRooms,
            'avgTime' => $avgTime,
            'totalUsers' => $totalUsers,
            'totalCheckIns' => $totalCheckIns,
            'rooms' => $roomsData
        ]);
    }
}
