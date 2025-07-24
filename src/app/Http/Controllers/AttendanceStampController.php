<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;

class AttendanceStampController extends Controller
{
    public function index()
    {
        $today = Carbon::now()->format("Y-m-d");

        //当日の勤怠情報がある
        $todayAttendance = Attendance::todayForUser(auth()->id())->first();

        //viewに渡すstatusの設定をモデルから呼び出す
        $status = $todayAttendance->current_status ?? '勤務外';

        return view('staff.attendance.index', compact('todayAttendance', 'today', 'status'));
    }

    /* 出勤打刻 */
    public function clockIn()
    {
        $user = Auth::user();
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

    /* 退勤打刻 */
    public function clockOut()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        $minIntervalResult = $this->checkInterval(optional($todayAttendance)->clock_in);
        if ($minIntervalResult) {
            return $minIntervalResult;
        }

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

    /* 休憩入打刻 */
    public function restStart()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        $lastRest = $todayAttendance->rests()->orderByDesc('id')->first();
        //まだ一度も休憩に入っていないか、前の休憩が終了している場合、新しく休憩に入ることができる
        $canStartRest = !$lastRest || $lastRest->rest_end;

        $minIntervalResult = $this->checkInterval(optional($todayAttendance)->clock_in);
        if ($minIntervalResult) {
            return $minIntervalResult;
        }
        if ($lastRest && $lastRest->rest_end) {
            $minIntervalResult = $this->checkInterval($lastRest->rest_end);
            if ($minIntervalResult) {
                return $minIntervalResult;
            }
        }

        //出勤中で、新しく休憩に入ることができる状態なら、休憩入打刻できる
        if ($todayAttendance && !$todayAttendance->clock_out && $canStartRest) {
            Rest::create([
                'attendance_id' => $todayAttendance->id,
                'rest_start' => $now->toTimeString(),
            ]);
        }

        return redirect('attendance');
    }

    /* 休憩戻打刻 */
    public function restEnd()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $todayAttendance = Attendance::todayForUser($user->id)->first();

        $lastRest = $todayAttendance->rests()->orderByDesc('id')->first();
        $minIntervalResult = $this->checkInterval(optional($lastRest)->rest_start);
        if ($minIntervalResult) {
            return $minIntervalResult;
        }

        //休憩中である場合、休憩戻が打刻できる
        if ($todayAttendance && !$todayAttendance->clock_out) {
            if ($lastRest && !$lastRest->rest_end) {
                $lastRest->update([
                    'rest_end' => $now->toTimeString(),
                ]);
            }
        }

        return redirect('attendance');
    }

    /* 前回の打刻から指定した秒数経過していなければ打刻できない設定 */
    private function checkInterval(?Carbon $lastStampTime)
    {
        $minTime = 10; //秒数の設定

        //前回打刻がない or 指定した秒数以上経過していればnullを返す（次の処理に進む）
        if (!$lastStampTime || now()->diffInSeconds($lastStampTime) >= $minTime) {
            return null;
        }
        return redirect()->back()->with('error', $minTime . '秒以上間を開けて再操作してください。');
    }
}
