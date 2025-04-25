<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Day;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AttendanceLogHistory;
use App\Models\DailyAttendanceMetric;
use App\Models\RoomAttendanceMetric;

use function GuzzleHttp\Promise\queue;

class DayController extends Controller
{

     /**
     * Get cumulative time spent by users in all rooms except main room
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getCumulativeRoomTime(Request $request)
    {
        if (!auth()->user()->can('day-table')) { abort(403); }
        
        // Get all users
        $users = User::whereHas('roomAttendanceMetrics')->get();
        
        // For each user, calculate cumulative time in non-main rooms
        $userData = [];
        foreach ($users as $user) {
            // Get all room metrics for this user from all days (excluding main room)
            $roomMetrics = RoomAttendanceMetric::where('user_id', $user->id)
                ->whereHas('room', function($query) {
                    $query->where('is_main', false);
                })
                ->with(['room', 'day'])
                ->get();
            
            // Initialize user data
            $userData[$user->id] = [
                'user' => $user,
                'total_seconds' => 0,
                'daily_breakdown' => [],
                'room_breakdown' => []
            ];
            
            // Calculate totals
            foreach ($roomMetrics as $metric) {
                // Add to total seconds
                $userData[$user->id]['total_seconds'] += $metric->time_spent_seconds;
                
                // Add to daily breakdown
                if (!isset($userData[$user->id]['daily_breakdown'][$metric->day_id])) {
                    $userData[$user->id]['daily_breakdown'][$metric->day_id] = 0;
                }
                $userData[$user->id]['daily_breakdown'][$metric->day_id] += $metric->time_spent_seconds;
                
                // Add to room breakdown
                if (!isset($userData[$user->id]['room_breakdown'][$metric->room_id])) {
                    $userData[$user->id]['room_breakdown'][$metric->room_id] = [
                        'name' => $metric->room->name,
                        'seconds' => 0
                    ];
                }
                $userData[$user->id]['room_breakdown'][$metric->room_id]['seconds'] += $metric->time_spent_seconds;
            }
            
            // Convert daily breakdown to human-readable format
            foreach ($userData[$user->id]['daily_breakdown'] as $dayId => $seconds) {
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $userData[$user->id]['daily_breakdown'][$dayId] = sprintf('%d hours, %d minutes', $hours, $minutes);
            }
            
            // Convert room breakdown to human-readable format
            foreach ($userData[$user->id]['room_breakdown'] as $roomId => &$roomData) {
                $hours = floor($roomData['seconds'] / 3600);
                $minutes = floor(($roomData['seconds'] % 3600) / 60);
                $roomData['time'] = sprintf('%d hours, %d minutes', $hours, $minutes);
            }
            
            // Calculate total hours and minutes
            $hours = floor($userData[$user->id]['total_seconds'] / 3600);
            $minutes = floor(($userData[$user->id]['total_seconds'] % 3600) / 60);
            $userData[$user->id]['total_time'] = sprintf('%d hours, %d minutes', $hours, $minutes);
        }
        
        // Get days for display
        $days = Day::orderBy('id')->get();
        
        return view('admin.days.days', compact('userData', 'days'));
    }


    public function Close()
    {
        if (!auth()->user()->can('day-close')) { abort(403); }
        DB::beginTransaction();

        try {
            // Find the currently open day
            $currentDay = Day::where('is_open', true)->first();
            if (!$currentDay) {
                return redirect()->back()->with(['error' => 'No open day found to close']);
            }

            $currentTime = now();

            // Find all users who are still checked in to ANY room (have an 'in' record without matching 'out')
            $activeLogins = AttendanceLog::where('type', 'in')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('attendance_logs as al2')
                        ->whereColumn('attendance_logs.user_id', 'al2.user_id')
                        ->whereColumn('attendance_logs.room_id', 'al2.room_id')
                        ->where('al2.type', 'out')
                        ->whereColumn('attendance_logs.time', '<', 'al2.time');
                })
                ->get();

            // Create automatic checkout records for all active users in all rooms
            foreach ($activeLogins as $login) {
                AttendanceLog::create([
                    'user_id' => $login->user_id,
                    'room_id' => $login->room_id,
                    'day_no' => $login->day_no,
                    'time' => $currentTime,
                    'type' => 'out'
                ]);
            }

            // Now proceed with existing day closing logic
            Day::where('is_open', true)->update([
                'is_open' => false,
            ]);

            // Copy all attendance records to history
            $attendanceLogs = AttendanceLog::get(['user_id', 'room_id', 'day_no', 'time', 'type'])->toArray();
            session()->forget('day_no');

            if (!empty($attendanceLogs)) {
                AttendanceLogHistory::insert($attendanceLogs);
            }

            // Calculate and store metrics for reporting before clearing logs
            $this->calculateAndStoreAttendanceMetrics($currentDay->id);

            // Clear current logs
            AttendanceLog::where('id', '!=', 0)->delete();

            DB::commit();
            return redirect()->back()->with(['success' => 'Day closed successfully. All attendance metrics have been calculated and stored.']);
        } catch(\Exception $ex) {
            DB::rollback();
            return redirect()->back()
                ->with(['error' => 'Error closing day: ' . $ex->getMessage()])
                ->withInput();
        }
    }

    /**
     * Calculate and store attendance metrics when closing a day
     *
     * @param int $dayId
     * @return void
     */
    private function calculateAndStoreAttendanceMetrics($dayId)
    {
        // Get all users who attended this day
        $users = User::whereHas('attendanceLogs', function($query) use ($dayId) {
            $query->where('day_no', $dayId);
        })->get();

        foreach ($users as $user) {
            // Calculate total time in conference (from main room check-in to last check-out of any room)
            $mainRoomCheckIn = AttendanceLog::where('user_id', $user->id)
                ->where('day_no', $dayId)
                ->whereHas('room', function($query) {
                    $query->where('is_main', true);
                })
                ->where('type', 'in')
                ->orderBy('time', 'asc')
                ->first();

            // Find the last checkout of any room
            $lastCheckOut = AttendanceLog::where('user_id', $user->id)
                ->where('day_no', $dayId)
                ->where('type', 'out')
                ->orderBy('time', 'desc')
                ->first();

            if ($mainRoomCheckIn && $lastCheckOut) {
                $totalTimeInConference = Carbon::parse($lastCheckOut->time)
                    ->diffInSeconds(Carbon::parse($mainRoomCheckIn->time));

                // Store this metric in a new table or update user's metrics
                DailyAttendanceMetric::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'day_id' => $dayId
                    ],
                    [
                        'total_time_seconds' => $totalTimeInConference
                    ]
                );
            }

            // Calculate time spent in each individual room
            $rooms = Room::all();
            foreach ($rooms as $room) {
                $roomLogs = AttendanceLog::where('user_id', $user->id)
                    ->where('day_no', $dayId)
                    ->where('room_id', $room->id)
                    ->orderBy('time')
                    ->get();

                // Calculate total time in this specific room
                $totalTimeInRoom = 0;
                $checkIn = null;

                foreach ($roomLogs as $log) {
                    if ($log->type == 'in') {
                        $checkIn = $log;
                    } else if ($log->type == 'out' && $checkIn) {
                        $timeSpent = Carbon::parse($log->time)
                            ->diffInSeconds(Carbon::parse($checkIn->time));
                        $totalTimeInRoom += $timeSpent;
                        $checkIn = null;
                    }
                }

                // Store room-specific metrics
                if ($totalTimeInRoom > 0) {
                    RoomAttendanceMetric::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'day_id' => $dayId,
                            'room_id' => $room->id
                        ],
                        [
                            'time_spent_seconds' => $totalTimeInRoom
                        ]
                    );
                }
            }
        }
    }


    public function Open(Request $request)
    {
        if (!auth()->user()->can('day-add')) { abort(403); }
        if (!Day::where('is_open',true)->count()){
            Day::where('is_open',true)->Update(['is_open'=>false]);
            $day = Day::Create(['is_open'=>true]);
            session()->put('day_no',$day->id);
        }
        return redirect()->back();
    }

    public function qualified(Request $request)
    {
        if (!auth()->user()->can('day-table')) { abort(403); }

        $avgTime = AttendanceLogHistory::calculateAveragePresenceHistory()['avg'];
        $sumTime = AttendanceLogHistory::calculateAveragePresenceHistory()['sum'];

        // Get total check-ins across all rooms
        $totalCheckIns = User::whereHas('attendanceLogHistories')->count();

        $users         = $this->getUsersColectionWithMinHours($request);
        return view('admin.days.qualified', compact(
            'users',
            'totalCheckIns',
        ));
    }

    public function getUsersColectionWithMinHours(Request $request)
    {
        // جلب السجلات للمكان واليوم المحدد
        $logs = AttendanceLogHistory::with('user')->whereHas('user',function ($q)use($request) {
            if($request->name)     $q->where('name',$request->name);
            if($request->barcode)  $q->where('barcode',$request->barcode);
            if($request->category && $request->category != 'all'){
                $q->where('category',$request->category);
            }elseif ($request->category) {
                $q->whereIn('category',[1,2]);
            }

        })
        ->orderBy('user_id')->orderBy('time')->get();

        $minHours = $request->minHours ?? 20;
        $userDurations = [];

        foreach ($logs as $log) {
            $userId = $log->user_id;

            if (!isset($userDurations[$userId])) {
                $userDurations[$userId] = [
                    'totalSeconds' => 0,
                    'inTime'       => null,
                    'id'           => $userId,
                ];
            }

            if ($log->type === 'in') {
                $userDurations[$userId]['inTime'] = $log->time;
            } elseif ($log->type === 'out' && $userDurations[$userId]['inTime']) {
                $in = Carbon::parse($userDurations[$userId]['inTime']);
                $out = Carbon::parse($log->time);
                $duration = $out->diffInSeconds($in);
                $userDurations[$userId]['totalSeconds'] += $duration;
                $userDurations[$userId]['inTime'] = null;
            }
        }

        // جلب المستخدمين الذين تجاوزوا عدد الساعات
        $qualifiedUserIds = collect($userDurations)->where('totalSeconds','>=',($minHours * 3600))->keys();

        // جلب المستخدمين كـ Collection عادي
        $users = User::whereIn('id', $qualifiedUserIds)->get();

        return $users;
    }

    // get for ajax request
    public function getUsersWithMinHours(Request $request)
    {
        $users = $this->getUsersColectionWithMinHours($request);
        return view('admin.days.attandance-users', compact(
            'users',
        ));
    }

}
