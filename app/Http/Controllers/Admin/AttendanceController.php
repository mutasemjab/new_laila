<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AttendanceController extends Controller
{

    public function room($room_id)
    {
        if (!auth()->user()->can('room-edit') && !auth()->user()->can('room-table')) { abort(403); }
    
        // Get all rooms - current_occupancy is now stored in the database
        $rooms = Room::where('id', $room_id)->get();
        $room  = Room::where('id', $room_id)->first();
        $name  = $room->name;
    
        // Get total number of users currently checked in across all rooms
        $totalActiveUsers = $rooms->sum('current_occupancy');
    
        // Get total number of registered users
        $totalUsers = User::where('activate', 1)->count();
    
        // Calculate average time spent in rooms
        $avgTime = $this->calculateAverageTime($room_id);
    
        // Get total check-ins across all rooms (this counts all check-in records, not just current)
        $totalCheckIns = AttendanceLog::where('room_id', $room_id)
                         ->where('type', 'in')
                         ->count();
    
        // Get users who have attendance logs for this room
        $users = User::with(['attendanceLogs' => function($query) use ($room_id) {
            $query->where('room_id', $room_id)
                  ->orderBy('time', 'desc');
        }])
        ->whereHas('attendanceLogs', function ($query) use($room_id) {
            $query->where('room_id', $room_id);
        })
        ->paginate(PAGINATION_COUNT);
    
        $attendanceSummary = $this->attendanceSummary();
        $attendanceSummary = json_decode($attendanceSummary, true);
    
        $view = $room->is_main ? 'admin.rooms.main' : 'admin.rooms.room';
        return view($view, compact(
            'name',
            'room_id',
            'rooms',
            'totalActiveUsers',
            'totalUsers',
            'avgTime',
            'totalCheckIns',
            'users',
            'attendanceSummary',
        ));
    }

    public function getRoomUsers(Request $request, $room_id)
    {
        $room = Room::where('id', $room_id)->first();
        
        // Modified query to include all users with attendance logs for this room
        // Not just those with the latest attendance log
        $users = User::with(['attendanceLogs' => function($query) use ($room_id, $room) {
            $query->where('room_id', $room_id)
                  ->orderBy('time', 'desc');
        }])
        ->where(function ($query) use($request){
            if($request->barcode) $query->where('barcode', $request->barcode);
            if($request->category && $request->category != 'all') $query->where('category', $request->category);
            if($request->name) {
                $query->where('name', $request->name);
            }
        })
        ->whereHas('attendanceLogs', function ($query) use($room_id, $room, $request){
            $query->where('room_id', $room_id);
            if($request->status && $request->status != 'all') $query->where('type', $request->status);
        })
        ->get();
    
        return view('admin.rooms.attandance-users', compact('users', 'room'));
    }

    public function attendanceSummary() {
        $attendanceSummary = User::where('activate', 1)
        ->whereHas('attendanceLogs', function($q){$q->where('type', 'in');})
        ->selectRaw("
            JSON_OBJECT(
                '1', COUNT(CASE WHEN category = 1 THEN 1 END),
                '2', COUNT(CASE WHEN category = 2 THEN 1 END),
                '3', COUNT(CASE WHEN category = 3 THEN 1 END),
                '4', COUNT(CASE WHEN category = 4 THEN 1 END),
                '5', COUNT(CASE WHEN category = 5 THEN 1 END),
                '6', COUNT(CASE WHEN category = 6 THEN 1 END)
            ) as counts
        ")
        ->first()
        ->counts;
        return $attendanceSummary;
    }

    private function calculateAverageTime($room_id)
    {
        $room = Room::findOrFail($room_id);
        
        // Get all completed room visits for specific room (pairs of check-in and check-out)
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
                users u ON a.user_id = u.id
            WHERE
                a.type = 'in'
                AND a.room_id = ?
                AND u.category IN (1, 2)
            GROUP BY
                a.id, a.user_id, a.room_id, a.time
        ", [$room_id]);
    
        // For users currently checked in (with no check-out yet)
        $currentlyCheckedIn = DB::select("
            SELECT
                a.id,
                a.user_id,
                a.room_id,
                a.time as check_in_time,
                NOW() as check_out_time
            FROM
                attendance_logs a
            JOIN
                users u ON a.user_id = u.id
            WHERE
                a.type = 'in'
                AND a.room_id = ?
                AND u.category IN (1, 2)
                AND NOT EXISTS (
                    SELECT 1
                    FROM attendance_logs b
                    WHERE b.user_id = a.user_id
                    AND b.room_id = a.room_id
                    AND b.type = 'out'
                    AND b.time > a.time
                )
        ", [$room_id]);
    
        // Combine completed visits and currently checked-in users
        $allVisits = array_merge($completedVisits, $currentlyCheckedIn);
    
        if (empty($allVisits)) {
            return '0 دقيقة';
        }
    
        $totalSeconds = 0;
        $count = 0;
    
        foreach ($allVisits as $visit) {
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
    public function getStatistics($room_id,$user_category = null)
    {
        // Get all rooms
        $rooms = Room::where('id',$room_id)->get();
        $room  = Room::where('id',$room_id)->first();

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
        $totalCheckIns = User::whereHas('attendanceLogs',function ($query) use($room_id,$room){
            $query->where('type', 'in');
            if(!$room->is_main) $query->where('room_id', $room_id);
        })->count();

        // Calculate average time
        $avgTime = $this->calculateAverageTime($room_id);

        return response()->json([
            'totalActiveUsers' => $totalActiveUsers,
            'activeRooms' => $activeRooms,
            'avgTime' => $avgTime,
            'totalUsers' => $totalUsers,
            'totalCheckIns' => $totalCheckIns,
            'rooms' => $roomsData,
        ]);
    }

    public function scanBarcode(Request $request, $get_barcode = null, $get_room_id = null)
    {
        $barcode = ($request->user_barcode) ?? $get_barcode;
        $room_id = ($request->room_id) ?? $get_room_id;
    
        $request->validate([
            'user_barcode' => 'required|exists:users,barcode',
            'room_id' => 'required|exists:rooms,id',
        ]);
    
        $user = User::where('barcode', $barcode)->first();
        $room = Room::findOrFail($room_id);
    
        // Check if user is active
        if ($user->activate != 1) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير نشط'
            ], 403);
        }
    
        // Get the last log entry for this user in this specific room
        $lastRoomLog = AttendanceLog::where('user_id', $user->id)
            ->where('room_id', $room_id)
            ->orderBy('time', 'desc')
            ->first();
        
        // Find all non-main rooms
        $nonMainRoomIds = Room::where('is_main', false)->pluck('id')->toArray();
        
        // Find the last check-in log in any non-main room
        $lastNonMainRoomLog = AttendanceLog::where('user_id', $user->id)
            ->whereIn('room_id', $nonMainRoomIds)
            ->where('type', 'in')
            ->orderBy('time', 'desc')
            ->first();
    
        $newType = 'in'; // Default to check-in
    
        // If the user's last action in THIS room was checking in, then check out
        if ($lastRoomLog && $lastRoomLog->type == 'in') {
            $newType = 'out';
            
            // Only decrement counter for non-main rooms
            if (!$room->is_main) {
                // Use DB transaction to ensure accurate counter
                DB::transaction(function() use ($room) {
                    $room->decrement('current_occupancy');
                });
            }
        }
        // If user is checked into a different non-main room, check them out first
        else if ($lastNonMainRoomLog && $lastNonMainRoomLog->room_id != $room->id) {
            $previousRoom = Room::findOrFail($lastNonMainRoomLog->room_id);
            
            // Create automatic check-out from previous room
            AttendanceLog::create([
                'user_id' => $user->id,
                'room_id' => $lastNonMainRoomLog->room_id,
                'time'    => Carbon::now(),
                'type'    => 'out',
                'day_no'  => session('day_no', 1),
            ]);
    
            // Decrease previous room occupancy in a transaction
            DB::transaction(function() use ($previousRoom) {
                $previousRoom->decrement('current_occupancy');
            });
    
            // Now check into this room
            $newType = 'in';
            
            // Only increment for non-main rooms
            if (!$room->is_main) {
                DB::transaction(function() use ($room) {
                    $room->increment('current_occupancy');
                });
            }
        }
        // For all other cases (user's last action was checking out or first check-in)
        else {
            $newType = 'in';
            
            // Only increment for non-main rooms
            if (!$room->is_main) {
                DB::transaction(function() use ($room) {
                    $room->increment('current_occupancy');
                });
            }
        }
    
        // Create the new log entry
        $log = AttendanceLog::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'time' => Carbon::now(),
            'type' => $newType,
            'day_no' => session('day_no', 1),
        ]);
    
        // Update last check-in time if this is a check-in
        if ($newType == 'in') {
            $room->update(['last_check_in' => Carbon::now()]);
        }
    
        // Calculate time spent if user is checking out
        $timeSpent = null;
        if ($newType == 'out') {
            $checkInLog = AttendanceLog::where('user_id', $user->id)
                ->where('room_id', $room->id)
                ->where('type', 'in')
                ->orderBy('time', 'desc')
                ->first();
    
            if ($checkInLog) {
                $checkInTime = Carbon::parse($checkInLog->time);
                $checkOutTime = Carbon::parse($log->time);
                $timeSpent = $checkOutTime->diffInSeconds($checkInTime);
            }
        }
    
        // Get current room occupancy directly from the database
        $room->refresh(); // Force reload from database
        $currentOccupancy = $room->current_occupancy;
    
        // Prepare last check-in time for display
        $lastCheckInTime = $room->last_check_in ? Carbon::parse($room->last_check_in)->diffForHumans() : 'لا يوجد';
    
        return response()->json([
            'success' => true,
            'message' => $newType == 'in' ? 'تم تسجيل الدخول بنجاح' : 'تم تسجيل الخروج بنجاح',
            'type' => $newType,
            'user' => $user->name,
            'user_category' => $user->category,
            'room' => $room->name,
            'current_room_occupancy' => $currentOccupancy,
            'last_check_in' => $lastCheckInTime,
            'time_spent' => $timeSpent ? $this->formatTimeSpent($timeSpent) : null,
        ]);
    }

    public function validateBarcode(Request $request,$get_barcode = null)
    {
        $barcode = $request->input('barcode') ?? $get_barcode;

        $userExists = User::where('barcode', $barcode)
            ->where('activate', 1)
            ->exists();

        return response()->json([
            'valid'    => $userExists,
            'barcode'  => $barcode,
        ]);
    }

    private function formatTimeSpent($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    // Method to initialize all room occupancy counts
    public function initializeRoomOccupancy()
    {
        // Get all rooms
        $rooms = Room::all();

        foreach ($rooms as $room) {
            // Calculate current occupancy
            $currentOccupancy = AttendanceLog::whereIn('id', function ($query) use ($room) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('attendance_logs')
                    ->where('room_id', $room->id)
                    ->groupBy('user_id');
            })
            ->where('type', 'in')
            ->count();

            // Get last check-in time
            $lastCheckIn = AttendanceLog::where('room_id', $room->id)
                ->where('type', 'in')
                ->orderBy('time', 'desc')
                ->first();

            // Update room
            $room->update([
                'current_occupancy' => $currentOccupancy,
                'last_check_in' => $lastCheckIn ? $lastCheckIn->time : null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات الغرف بنجاح'
        ]);
    }
}
