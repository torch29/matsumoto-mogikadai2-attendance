<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;

class AttendanceStampController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $today = Carbon::now()->format("Y-m-d");

        //当日の勤怠情報がある
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        //viewに渡すstatusの設定
        $status = '勤務外'; //デフォルト
        if ($todayAttendance) {
            if ($todayAttendance->clock_out !== null) {
                $status = '退勤済';
            } else {
                $lastRest = $todayAttendance->rests()->orderByDesc('id')->first();
                if ($lastRest && $lastRest->rest_end === null) {
                    $status = '休憩中';
                } else {
                    $status = '出勤中';
                }
            }
        }

        return view('staff.attendance.index', compact('todayAttendance', 'today', 'user', 'status'));
    }

    //出勤打刻
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

    //退勤打刻
    public function clockOut()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $now = Carbon::now();
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        //退勤打刻
        //出勤時刻データが存在し、退勤時刻データが無い
        if ($todayAttendance && !$todayAttendance->clock_out) {
            $lastRest = $todayAttendance->rests()->orderByDesc('id')->first();
            //まだ休憩入データが無いか、休憩入データ＋休憩戻データがセットで存在する
            if (!$lastRest || $lastRest->rest_end) {
                $todayAttendance->update([
                    'clock_out' => $now->toTimeString(),
                ]);
            }
        }

        return redirect('attendance');
    }

    //休憩入打刻
    public function restStart()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $now = Carbon::now();
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        $lastRest = $todayAttendance->rests()->orderByDesc('id')->first();
        //まだ休憩に入っていないか、前の休憩が終了している場合、新しく休憩に入ることができる
        $canStartRest = !$lastRest || $lastRest->rest_end;

        //出勤中で、新しく休憩に入ることができる状態
        if ($todayAttendance && !$todayAttendance->clock_out && $canStartRest) {
            Rest::create([
                'attendance_id' => $todayAttendance->id,
                'rest_start' => $now->toTimeString(),
            ]);
        }

        return redirect('attendance');
    }

    //休憩戻打刻
    public function restEnd()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $now = Carbon::now();
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        $lastRest = $todayAttendance->rests()->orderByDesc('id')->first();

        $minTime = 15; //休憩入を押してから休憩戻を押せる最低間隔の時間（秒）設定
        if ($lastRest && now()->diffInSeconds($lastRest->rest_start) < $minTime) {
            return redirect()->back()->with('error', $minTime . '秒以上間を開けて再操作してください。');
        } elseif ($todayAttendance && !$todayAttendance->clock_out) {
            if ($lastRest && !$lastRest->rest_end) {
                $lastRest->update([
                    'rest_end' => $now->toTimeString(),
                ]);
            }
        }

        return redirect('attendance');
    }
}
