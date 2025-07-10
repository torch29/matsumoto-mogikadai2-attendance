<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    /* 職員自身の勤怠一覧表示 */
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

    /* 勤怠詳細画面の表示 */
    public function showDetail($id)
    {
        $user = Auth::user();

        $attendances = Attendance::with('user', 'rests', 'attendanceCorrections.restCorrections')->where('id', $id);

        //一般職員は自分のデータのみ取得
        if (!$user->is_admin) {
            $attendances->where('user_id', $user->id);
        }
        $attendance = $attendances->firstOrFail();

        //該当の勤怠データがない場合エラーメッセージを表示して返す
        if (!$attendance) {
            return redirect()->back()->with('error', '該当のデータがありません。');
        }

        //最新の修正申請を取得
        $latestCorrection = $attendance->attendanceCorrections->sortByDesc('created_at')->first();

        $displayClockIn = optional($latestCorrection)->corrected_clock_in ?? $attendance->clock_in;
        $displayClockOut = optional($latestCorrection)->corrected_clock_out ?? $attendance->clock_out;
        $displayNote = optional($latestCorrection)->note ?? null;

        //修正申請があればrest_correctionsを、なければrestsのデータを表示
        $restRecords = $latestCorrection
            ? $latestCorrection->restCorrections->map(function ($rest) {
                return (object)[
                    'rest_start' => $rest->corrected_rest_start,
                    'rest_end' => $rest->corrected_rest_end,
                ];
            })
            : $attendance->rests;

        $view = $user->is_admin
            ? 'admin.attendance.detail'
            : 'staff.attendance.detail';

        //一般職員用の勤怠詳細画面表示
        return view($view, compact('attendance', 'displayClockIn', 'displayClockOut', 'displayNote', 'restRecords', 'latestCorrection'));
    }
}
