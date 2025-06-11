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
        Carbon::setLocale('ja');
        $today = Carbon::now()->format("Y-m-d");

        $user = Auth::user();

        //当日の勤怠情報があるか
        $today_attendance = Attendance::whereDate('date', $today)
            ->where('user_id', auth()->id())
            ->first();

        return view('staff.attendance.index', compact('today_attendance', 'today', 'user',));
    }

    public function clockIn()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $datetime = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $datetime->toDateString(),
            'clock_in' => $datetime->toTimeString(),
        ]);

        return redirect('attendance');
    }

    public function showAttendanceList()
    {
        $user = Auth::user();
        $attendances = $user->attendances;

        return view('staff.attendance.list', compact('attendances', 'user'));
    }

    public function showDetail()
    {
        return view('staff.attendance.detail');
    }
}
