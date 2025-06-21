<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class AttendanceController extends Controller
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

        if ($lastRest && now()->diffInSeconds($lastRest->rest_start) < 60) {
            return redirect()->back()->with('error', '間を開けて再操作してください。');
        } elseif ($todayAttendance && !$todayAttendance->clock_out) {
            if ($lastRest && !$lastRest->rest_end) {
                $lastRest->update([
                    'rest_end' => $now->toTimeString(),
                ]);
            }
        }

        return redirect('attendance');
    }

    //職員自身の勤怠一覧
    public function showAttendanceList(Request $request)
    {
        $user = Auth::user();

        //指定が無ければ今月を、指定があればその月を設定
        $selectDate = $request->date
            ? Carbon::parse($request->date)->startOfDay()
            : Carbon::today();
        $firstOfMonth = $selectDate->copy()->firstOfMonth();
        $endOfMonth = $selectDate->copy()->endOfMonth();

        $previousMonth = Attendance::getPreviousMonth($selectDate);
        $nextMonth = Attendance::getNextMonth($selectDate);

        //1日～末日までの日付の配列を作成
        $dates = [];
        for ($date = $firstOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dates[] = $date->copy();
        }

        //今月分の勤怠を取得（休憩を含む）。日付でキー指定する
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$firstOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->mapWithKeys(function ($attendance) {
                return [$attendance->date->toDateString() => $attendance];
            });

        $attendanceRecords = [];

        foreach ($dates as $date) {
            $attendance = $attendances[$date->toDateString()] ?? null;

            $clockIn = optional($attendance)->clock_in;
            $clockOut = optional($attendance)->clock_out;
            $clockInFormatted = optional($attendance)->clock_in_formatted;
            $clockOutFormatted = optional($attendance)->clock_out_formatted;

            //休憩合計時間とそのフォーマットをAttendanceモデルから取得
            $rests = optional($attendance)->rests ?? collect();
            $totalRestSeconds = optional($attendance)->total_rest_seconds;
            $totalRestFormatted = optional($attendance)->total_rest_formatted;

            //実労働時間とそのフォーマットをAttendanceモデルから取得
            $totalWorkHours = optional($attendance)->total_work_minutes;
            $totalWorkFormatted = optional($attendance)->total_work_formatted;

            $attendanceRecords[] = [
                'id' => optional($attendance)->id,
                'date' => $date->isoFormat('M月D日（ddd）'),
                'clock_in' => $clockInFormatted,
                'clock_out' => $clockOutFormatted,
                'total_rest' => $totalRestSeconds,
                'total_rest_formatted' => $totalRestFormatted,
                'total_work_hours' => $totalWorkHours,
                'total_work_formatted' => $totalWorkFormatted,
            ];
        }

        return view('staff.attendance.list', compact('attendances', 'user', 'attendanceRecords', 'selectDate', 'previousMonth', 'nextMonth'));
    }

    //勤怠詳細画面の表示
    public function showDetail($id)
    {
        $user = Auth::user();
        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->with('user', 'rests', 'attendanceCorrections.restCorrections')
            ->first();

        //該当の勤怠データがない場合エラーメッセージを表示して返す
        if (!$attendance) {
            return redirect()->back()->with('error', '該当のデータがありません。');
        }

        $latestCorrection = $attendance->attendanceCorrections->sortByDesc('created_at')->first();

        $displayClockIn = optional($latestCorrection)->corrected_clock_in ?? $attendance->clock_in;
        $displayClockOut = optional($latestCorrection)->corrected_clock_out ?? $attendance->clock_out;
        $displayNote = optional($latestCorrection)->note ?? null;

        $restRecords = $latestCorrection
            ? $latestCorrection->restCorrections->map(function ($rest) {
                return (object)[
                    'rest_start' => $rest->corrected_rest_start,
                    'rest_end' => $rest->corrected_rest_end,
                ];
            })
            : $attendance->rests;

        /*
        $displayRecords[] = [
            'displayClockIn' => optional($latestCorrection)->corrected_clock_in ?? $attendance->clock_in,
            'displayClockOut' => optional($latestCorrection)->corrected_clock_out ?? $attendance->clock_out,
            'displayNote' => optional($latestCorrection)->note ?? null,
        ];
        */

        //一般職員用の勤怠詳細画面表示
        return view('staff.attendance.detail', compact('attendance', 'displayClockIn', 'displayClockOut', 'displayNote', 'restRecords'));
    }
}
