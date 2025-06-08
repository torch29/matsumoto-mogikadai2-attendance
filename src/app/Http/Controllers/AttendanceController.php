<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('staff.attendance.index');
    }

    public function stamp()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $datetime = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $datetime->toDateString(),
            'clock_in' => $datetime->toTimeString(),
        ]);

        return redirect('attendance/list');
    }

    public function showAttendanceList()
    {
        $attendances = Attendance::with('user')->get();

        return view('staff.attendance.list', compact('attendances'));
    }

    public function showDetail()
    {
        return view('staff.attendance.detail');
    }
}
