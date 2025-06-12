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
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        return view('staff.attendance.index', compact('todayAttendance', 'today', 'user',));
    }

    public function clockIn()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $now = Carbon::now();
        $today = $now->toDateString();

        //当日の勤怠情報があるか
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        //当日に出勤情報がなければ出勤打刻する
        if (!$todayAttendance) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in' => $now->toTimeString(),
            ]);
        }

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
