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
use App\Models\DailyAttendanceMetric;
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
        
        // Calculate total attendance time in hours
        $totalTimeSeconds = DailyAttendanceMetric::where('user_id', $id)
            ->sum('total_time_seconds');
        $totalHours = $totalTimeSeconds / 3600; // Convert seconds to hours
        
        // Determine credited hours based on attendance ranges
        $creditedHours = $this->calculateCreditedHours($totalHours);
        
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

       if($user->category == 1) {
        $pdf->SetXY(107, 81);
         $pdf->Write(10, $name);
       }else{
        // Adjust X and Y based on where you want the name
        $pdf->SetXY(108, 76);
        $pdf->Write(10, $name);
        
        // Use calculated hours instead of fixed 9
        $pdf->SetXY(140, 150);
        $pdf->Write(10, $creditedHours);
       }
        
        // Output as a downloadable PDF
        return response()->streamDownload(function () use ($pdf) {
            $pdf->Output('F', 'php://output');
        }, 'certificate.pdf');
    }
    
    /**
     * Calculate credited hours based on total hours attended
     *
     * @param float $totalHours
     * @return int
     */
    private function calculateCreditedHours($totalHours)
    {
        if ($totalHours >= 34 && $totalHours <= 37) {
            return 6;
        } elseif ($totalHours >= 25 && $totalHours < 34) {
            return 5;
        } elseif ($totalHours >= 19 && $totalHours < 25) {
            return 4;
        } elseif ($totalHours >= 15 && $totalHours < 19) { // Note: There seems to be a potential overlap in your ranges
            return 3;
        } elseif ($totalHours >= 10 && $totalHours < 15) {
            return 2;
        } else { // $totalHours < 10
            return 1;
        }
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
    public function index(Request $request)
    {
        $query = User::query();
        
        // Filter by category if specified
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }
        
        $users = $query->get(); // Removed pagination
        
        // Get all categories for the filter dropdown
        $categories = [
            1 => 'Speaker',
            2 => 'Participant',
            3 => 'Exhibitor',
            4 => 'Committee',
            5 => 'Press',
            6 => 'Other'
        ];
        
        return view('admin.users.index', compact('users', 'categories'));
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
            'position' => 'required|string|max:255',
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
        $user->position = $validatedData['position'];
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
            'position' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'gender' => 'required|in:1,2',
            'category' => 'required|in:1,2,3,4,5,6',
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
            'activate' => 'required|in:1,2',
        ]);


        $user->name = $validatedData['name'];


        $user->company = $validatedData['company'];
        $user->country = $validatedData['country'];
        $user->position = $validatedData['position'];
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

   
    public function showLogs(Request $request, $userId = null)
    {
        // Get all rooms for reference
        $rooms = Room::all();
        
        // If no user ID is provided, get logs for all users
        if (!$userId) {
            // Get all users with their attendance logs
            $users = User::all();
            $userData = [];
            
            foreach ($users as $user) {
                $userData[] = $this->processUserLogs($user);
            }
            
            return view('attendance.logs', [
                'multipleUsers' => true,
                'userData' => $userData,
                'rooms' => $rooms,
            ]);
        }
        
        // Get a specific user with their attendance logs
        $user = User::findOrFail($userId);
        $userData = $this->processUserLogs($user);
        
        return view('admin.users.showLog', [
            'multipleUsers' => false,
            'userData' => [$userData], // Wrap in array for consistent blade handling
            'rooms' => $rooms,
            'user' => $user,
        ]);
    }
    
    /**
     * Process user logs to calculate time spent in each room
     * 
     * @param User $user
     * @return array
     */
    protected function processUserLogs(User $user)
    {
        // Get all attendance logs for this user (current and historical)
        $currentLogs = AttendanceLog::where('user_id', $user->id)->get();
        $historyLogs = AttendanceLogHistory::where('user_id', $user->id)->get();
        
        // Combine and sort all logs by time
        $allLogs = $currentLogs->concat($historyLogs)->sortBy('time');
        
        // Group logs by day number
        $logsByDay = $allLogs->groupBy('day_no');
        
        // Initialize room time tracking
        $roomTimes = [];
        $currentRooms = [];
        $totalTimeByRoom = [];
        $currentlyInRooms = []; // Track rooms user is currently in
        
        // Process each day's logs
        foreach ($logsByDay as $dayNo => $dayLogs) {
            $dayRoomTimes = [];
            
            foreach ($dayLogs as $log) {
                $roomId = $log->room_id;
                $time = Carbon::parse($log->time);
                
                if ($log->type === 'in') {
                    // User entered a room
                    $currentRooms[$roomId] = $time;
                    
                    // Check if there's a matching 'out' log after this time
                    $hasExited = false;
                    foreach ($dayLogs as $exitLog) {
                        if ($exitLog->room_id == $roomId && 
                            $exitLog->type == 'out' && 
                            Carbon::parse($exitLog->time)->gt($time)) {
                            $hasExited = true;
                            break;
                        }
                    }
                    
                    // If no matching 'out' found, user is still in this room
                    if (!$hasExited) {
                        $currentlyInRooms[$roomId] = $time;
                    }
                    
                } else {
                    // User exited a room
                    if (isset($currentRooms[$roomId])) {
                        $entryTime = $currentRooms[$roomId];
                        $exitTime = $time;
                        $duration = $exitTime->diffInSeconds($entryTime);
                        
                        // Save room time for this day
                        if (!isset($dayRoomTimes[$roomId])) {
                            $dayRoomTimes[$roomId] = 0;
                        }
                        $dayRoomTimes[$roomId] += $duration;
                        
                        // Add to total time
                        if (!isset($totalTimeByRoom[$roomId])) {
                            $totalTimeByRoom[$roomId] = 0;
                        }
                        $totalTimeByRoom[$roomId] += $duration;
                        
                        unset($currentRooms[$roomId]);
                        // Also remove from currently in rooms if present
                        if (isset($currentlyInRooms[$roomId])) {
                            unset($currentlyInRooms[$roomId]);
                        }
                    }
                }
            }
            
            // Add the day's room times to the tracking array
            $roomTimes[$dayNo] = $dayRoomTimes;
        }
        
        // Calculate ongoing durations for rooms the user is still in
        $ongoingDurations = [];
        $now = Carbon::now();
        foreach ($currentlyInRooms as $roomId => $entryTime) {
            $ongoingDurations[$roomId] = $now->diffInSeconds($entryTime);
            
            // Add ongoing time to the total room time
            if (!isset($totalTimeByRoom[$roomId])) {
                $totalTimeByRoom[$roomId] = 0;
            }
            $totalTimeByRoom[$roomId] += $ongoingDurations[$roomId];
        }
        
        // Convert seconds to human-readable format
        $formattedTotalTime = [];
        foreach ($totalTimeByRoom as $roomId => $seconds) {
            $formattedTotalTime[$roomId] = $this->formatDuration($seconds);
        }
        
        $formattedOngoingDurations = [];
        foreach ($ongoingDurations as $roomId => $seconds) {
            $formattedOngoingDurations[$roomId] = $this->formatDuration($seconds);
        }
        
        return [
            'user' => $user,
            'logs' => $allLogs,
            'logsByDay' => $logsByDay,
            'totalTimeByRoom' => $totalTimeByRoom,
            'formattedTotalTime' => $formattedTotalTime,
            'roomTimes' => $roomTimes,
            'currentlyInRooms' => $currentlyInRooms,
            'ongoingDurations' => $ongoingDurations,
            'formattedOngoingDurations' => $formattedOngoingDurations
        ];
    }
    
    /**
     * Format seconds into a human-readable duration
     * 
     * @param int $seconds
     * @return string
     */
    protected function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
