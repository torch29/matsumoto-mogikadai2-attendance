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
        $user = Auth::user();
        Carbon::setLocale('ja');
        $today = Carbon::now()->format("Y-m-d");

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

        //指定が無ければ今日が属する月を、指定があればその月を設定したい
        $currentDay = Carbon::today();
        $firstOfMonth = $currentDay->copy()->firstOfMonth();
        $endOfMonth = $currentDay->copy()->endOfMonth();

        //1日～末日までの日付の配列を作成
        $dates = [];
        for ($date = $firstOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dates[] = $date->copy();
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$firstOfMonth, $endOfMonth])
            ->get()
            ->KeyBy('date');

        //日付＋勤怠データのセット
        $attendanceRecords = [];
        foreach ($dates as $date) {
            $key = $date->format('Y-m-d');
            $record = $attendances[$key] ?? null;

            $attendanceRecords[] = [
                'date' => $date->isoFormat('M月D日（ddd）'),
                'clock_in' => optional($record)->clock_in_formatted,
                'clock_out' => optional($record)->clock_out_formatted,
                'record' => $record,
            ];
        }

        return view('staff.attendance.list', compact('attendances', 'user', 'attendanceRecords'));
    }

    public function showDetail()
    {
        return view('staff.attendance.detail');
    }
}
