<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use App\Http\Controllers\Controller;
use App\Imports\UsersImport;
use App\Models\AttendanceLogHistory;
use App\Models\Day;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\Fpdi;

class UserController extends Controller
{

    public function generateCertificate(Request $request)
    {
        $name = $request->input('name');
        $id = $request->input('id');

        $user = User::find($id);

        // Path to template based on user category
        $templatePath = $user->category == 1
            ? base_path('certificates/speaker.pdf')
            : base_path('certificates/all.pdf');

        // Create new FPDI instance
        $pdf = new Fpdi();

        // Get original page size
        $pageCount = $pdf->setSourceFile($templatePath);
        $template = $pdf->importPage(1);

        // Get the page sizes from the imported page
        $size = $pdf->getTemplateSize($template);

        // Add a page with the same size as the template
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

        // Use the imported page as template
        $pdf->useTemplate($template, 0, 0, null, null, true);

        // Set font and position
        $pdf->SetFont('Helvetica', '', 24);
        $pdf->SetTextColor(0, 0, 0);

        // Adjust X and Y based on where you want the name
        $pdf->SetXY(120, 74);
        $pdf->Write(10, $name);

        $pdf->SetXY(140, 150);
        $pdf->Write(10, 9);

        // Output as a downloadable PDF
        return response()->streamDownload(function () use ($pdf) {
            $pdf->Output('F', 'php://output');
        }, 'certificate.pdf');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file'));
            return redirect()->back()->with('success', 'Users imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing users: ' . $e->getMessage());
        }
    }
        public function index()
    {
        $users = User::paginate(PAGINATION_COUNT);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'gender' => 'required|in:1,2',
            'category' => 'required|in:1,2,3,4,5,6',
            'phone' => 'required|string|max:255|unique:users',
            'activate' => 'required|in:1,2',
        ]);

        $user = new User();

        $user->name = $validatedData['name'];

        $user->company = $validatedData['company'];
        $user->country = $validatedData['country'];
        $user->email = $validatedData['email'];
        $user->gender = $validatedData['gender'];
        $user->category = $validatedData['category'];
        $user->phone = $validatedData['phone'];
        $user->activate = $validatedData['activate'];
        $user->barcode = User::generateUniqueBarcode();
        $user->save();

        return redirect()->route('users.index')->with('success', 'User created successfully');
    }

    /**
     * Display the specified user and their barcode.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
    */
    public function edit($id)
    {
        $data = User::findOrFail($id);
        return view('admin.users.edit', compact('data'));
    }

    /**
     * Update the specified user in storage.
    */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([

            'name' => 'required|string|max:255',

            'company' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'gender' => 'required|in:1,2',
            'category' => 'required|in:1,2,3,4,5,6',
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
            'activate' => 'required|in:1,2',
        ]);


        $user->name = $validatedData['name'];


        $user->company = $validatedData['company'];
        $user->country = $validatedData['country'];
        $user->email = $validatedData['email'];
        $user->gender = $validatedData['gender'];
        $user->category = $validatedData['category'];
        $user->phone = $validatedData['phone'];
        $user->activate = $validatedData['activate'];

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }
    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully');
    }

    // public function showLogs($id)
    // {
    //     // Get the user
    //     $user = User::findOrFail($id);

    //     // Get all rooms
    //     $rooms = Room::all();

    //     // Get all user attendance logs grouped by room
    //     $roomTimeLogs = [];

    //     foreach ($rooms as $room) {
    //         // Get all logs for this user in this room, ordered by time
    //         $logs = AttendanceLog::where('user_id', $user->id)
    //             ->where('room_id', $room->id)
    //             ->orderBy('time')
    //             ->get();

    //         // Calculate time spent and format attendance records
    //         $visits = [];
    //         $totalTimeInRoom = 0;

    //         // Process logs to pair check-ins with check-outs
    //         $checkIn = null;

    //         foreach ($logs as $log) {
    //             if ($log->type == 'in') {
    //                 $checkIn = $log;
    //             } else if ($log->type == 'out' && $checkIn) {
    //                 // Calculate time spent
    //                 $checkInTime = Carbon::parse($checkIn->time);
    //                 $checkOutTime = Carbon::parse($log->time);
    //                 $duration = $checkOutTime->diffInSeconds($checkInTime);
    //                 $totalTimeInRoom += $duration;

    //                 // Add to visits
    //                 $visits[] = [
    //                     'check_in_time' => $checkInTime,
    //                     'check_out_time' => $checkOutTime,
    //                     'duration' => $this->formatDuration($duration),
    //                     'duration_seconds' => $duration
    //                 ];

    //                 $checkIn = null;
    //             }
    //         }

    //         // Handle case where user is still in room (no check-out)
    //         if ($checkIn) {
    //             $visits[] = [
    //                 'check_in_time' => Carbon::parse($checkIn->time),
    //                 'check_out_time' => null,
    //                 'duration' => 'مازال في الغرفة',
    //                 'duration_seconds' => Carbon::now()->diffInSeconds(Carbon::parse($checkIn->time))
    //             ];
    //         }

    //         // Add room data with visits
    //         if (!empty($visits)) {
    //             $roomTimeLogs[] = [
    //                 'room' => $room,
    //                 'visits' => $visits,
    //                 'total_time' => $this->formatDuration($totalTimeInRoom),
    //                 'total_seconds' => $totalTimeInRoom
    //             ];
    //         }
    //     }

    //     // Sort rooms by total time spent (descending)
    //     usort($roomTimeLogs, function($a, $b) {
    //         return $b['total_seconds'] - $a['total_seconds'];
    //     });


    //     // Get all user attendance logs grouped by room
    //     $roomTimeLogshistory = [];

    //     foreach ($rooms as $room) {
    //         // Get all logs for this user in this room, ordered by time
    //         $logsHistory = AttendanceLogHistory::where('user_id', $user->id)
    //             ->where('room_id', $room->id)
    //             ->orderBy('time')
    //             ->get();

    //         // Calculate time spent and format attendance records
    //         $visits = [];
    //         $totalTimeInRoom = 0;

    //         // Process logs to pair check-ins with check-outs
    //         $checkIn = null;

    //         foreach ($logsHistory as $logHistory) {
    //             if ($logHistory->type == 'in') {
    //                 $checkIn = $logHistory;
    //             } else if ($logHistory->type == 'out' && $checkIn) {
    //                 // Calculate time spent
    //                 $checkInTime = Carbon::parse($checkIn->time);
    //                 $checkOutTime = Carbon::parse($logHistory->time);
    //                 $duration = $checkOutTime->diffInSeconds($checkInTime);
    //                 $totalTimeInRoom += $duration;

    //                 // Add to visits
    //                 $visits[] = [
    //                     'check_in_time' => $checkInTime,
    //                     'check_out_time' => $checkOutTime,
    //                     'duration' => $this->formatDuration($duration),
    //                     'duration_seconds' => $duration
    //                 ];

    //                 $checkIn = null;
    //             }
    //         }

    //         // Handle case where user is still in room (no check-out)
    //         if ($checkIn) {
    //             $visits[] = [
    //                 'check_in_time' => Carbon::parse($checkIn->time),
    //                 'check_out_time' => null,
    //                 'duration' => 'مازال في الغرفة',
    //                 'duration_seconds' => Carbon::now()->diffInSeconds(Carbon::parse($checkIn->time))
    //             ];
    //         }

    //         // Add room data with visits
    //         if (!empty($visits)) {
    //             $roomTimeLogshistory[] = [
    //                 'room' => $room,
    //                 'visits' => $visits,
    //                 'total_time' => $this->formatDuration($totalTimeInRoom),
    //                 'total_seconds' => $totalTimeInRoom
    //             ];
    //         }
    //     }

    //     // Sort rooms by total time spent (descending)
    //     usort($roomTimeLogshistory, function($a, $b) {
    //         return $b['total_seconds'] - $a['total_seconds'];
    //     });


    //     return view('admin.users.showLog', compact('user', 'roomTimeLogs','roomTimeLogshistory'));
    // }

    // private function formatDuration($seconds)
    // {
    //     if ($seconds < 60) {
    //         return $seconds . ' ثانية';
    //     }

    //     $minutes = floor($seconds / 60);
    //     $remainingSeconds = $seconds % 60;

    //     if ($minutes < 60) {
    //         return $minutes . ' دقيقة ' . ($remainingSeconds > 0 ? 'و ' . $remainingSeconds . ' ثانية' : '');
    //     }

    //     $hours = floor($minutes / 60);
    //     $remainingMinutes = $minutes % 60;

    //     $formattedTime = $hours . ' ساعة';

    //     if ($remainingMinutes > 0) {
    //         $formattedTime .= ' و ' . $remainingMinutes . ' دقيقة';
    //     }

    //     if ($remainingSeconds > 0 && $remainingMinutes == 0) {
    //         $formattedTime .= ' و ' . $remainingSeconds . ' ثانية';
    //     }

    //     return $formattedTime;
    // }


    public function showLogs($userId)
{
    $user = User::with(['dailyAttendanceMetrics', 'roomAttendanceMetrics.room'])->findOrFail($userId);

    // Get all days the user attended
    $days = Day::whereHas('dailyAttendanceMetrics', function($query) use ($userId) {
        $query->where('user_id', $userId);
    })->get();

    // Get all rooms
    $rooms = Room::all();

    // Prepare room summary data
    $roomSummary = [];
    foreach ($rooms as $room) {
        $totalTime = $user->getTotalTimeInRoom($room->id);

        // Only include rooms the user actually visited
        if ($user->roomAttendanceMetrics()->where('room_id', $room->id)->exists()) {
            $roomSummary[] = [
                'room' => $room,
                'total_time' => $totalTime,
                'total_seconds' => $totalTime,
                'daily_breakdown' => $user->roomAttendanceMetrics()
                    ->where('room_id', $room->id)
                    ->with('day')
                    ->get()
                    ->map(function($metric) {
                        return [
                            'day_id' => $metric->day_id,
                            'day_number' => $metric->day->id, // Assuming this corresponds to day number
                            'time_spent' => $metric->formatted_time_spent,
                        ];
                    })
            ];
        }
    }

    // Sort rooms by total time (most time first)
    usort($roomSummary, function($a, $b) {
        return $b['total_seconds'] <=> $a['total_seconds'];
    });

    return view('admin.users.showLog', compact('user', 'days', 'roomSummary'));
}
}
