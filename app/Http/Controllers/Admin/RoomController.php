<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RoomController extends Controller
{

    public function index()
    {
        $data = Room::paginate(PAGINATION_COUNT);
        return view('admin.rooms.index', ['data' => $data]);
    }

    public function create()
    {
        if (auth()->user()->can('room-add')) {
            return view('admin.rooms.create');
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
        }
    }

    public function store(Request $request)
    {
        if (auth()->user()->can('room-add')) {
            try {

                $room = new Room();
                $room->name = $request->get('name');

                if ($room->save()) {
                    return redirect()->route('rooms.index')->with(['success' => 'room created']);
                } else {
                    return redirect()->back()->with(['error' => 'Something wrong']);
                }
            } catch (\Exception $ex) {
                return redirect()->back()
                    ->with(['error' => 'عفوا حدث خطأ ما' . $ex->getMessage()])
                    ->withInput();
            }
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
        }
    }

    public function edit($id)
    {
        $userExists = User::where('barcode', '9415794049127')
        ->where('activate',1)
        ->exists();

        if (auth()->user()->can('room-edit')) {
            $data = Room::findorFail($id);
            return view('admin.rooms.edit', compact('data'));
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
        }
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->can('room-edit')) {
            $room = Room::findorFail($id);
            try {
                $room->name = $request->get('name');

                if ($room->save()) {
                    return redirect()->route('admin.room.index')->with(['success' => 'room update']);
                } else {
                    return redirect()->back()->with(['error' => 'Something wrong']);
                }
            } catch (\Exception $ex) {
                return redirect()->back()
                    ->with(['error' => 'عفوا حدث خطأ ما' . $ex->getMessage()])
                    ->withInput();
            }
        } else {
            return redirect()->back()
                ->with('error', "Access Denied");
        }
    }
}
