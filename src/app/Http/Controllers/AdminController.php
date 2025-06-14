<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    //管理者ログイン画面表示
    public function login()
    {
        return view('admin.auth.login');
    }

    //「選ばれたある特定の一日」の全員の勤怠情報
    public function showAttendanceListAll()
    {
        //日付のリクエストがあったらその日、なければ当日を表示
        $date = Carbon::today();
        $titleDate = Carbon::today()->isoFormat('Y年M月D日');
        $attendances = Attendance::whereDate('date', $date)
            ->with(['user', 'rests'])
            ->get();

        $attendanceRecords = [];

        foreach ($attendances as $attendance) {
            //休憩時間の合計
            $totalRestMinutes = $attendance->rests->sum(function ($rest) {
                if ($rest->rest_start && $rest->rest_end) {
                    return Carbon::parse($rest->rest_end)->diffInMinutes(Carbon::parse($rest->rest_start));
                }
                return 0;
            });

            //(出勤～退勤) - 休憩時刻 により、実労働時間を計算
            $totalWorkHours = null;
            $totalWorkFormatted = null;
            if ($attendance->clock_in && $attendance->clock_out) {
                $totalWorkHours = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $totalRestMinutes;

                //実労働時間を[H:mm]形式に整える（万が一、出勤～退勤時間合計 < 休憩時間合計 の場合はマイナス表示する）
                if ($totalWorkHours >= 0) {
                    $totalWorkFormatted = Carbon::createFromTime(0, 0)
                        ->addMinutes($totalWorkHours)
                        ->isoFormat('H:mm');
                } else {
                    $absolute = abs($totalWorkHours);
                    $totalWorkFormatted = '-' . Carbon::createFromTime(0, 0)
                        ->addMinutes($absolute)
                        ->isoFormat('H:mm');
                }
            }

            //viewファイルに渡すための設定
            $attendanceRecords[] = [
                'name' => $attendance->user->name,
                'id' => $attendance->id,
                'clock_in' => $attendance->clock_in_formatted,
                'clock_out' => $attendance->clock_out_formatted,
                'total_rest' => $totalRestMinutes,
                'total_rest_formatted' => Carbon::createFromTime(0, 0)->addMinutes($totalRestMinutes)->isoFormat('H:mm'),
                'total_work_hours' => $totalWorkHours,
                'total_work_formatted' => $totalWorkFormatted
            ];
        }

        return view('admin.attendance.list_all', compact('attendanceRecords', 'titleDate'));
    }

    public function showStaffList()
    {
        $staffLists = User::with('attendances')->get();

        return view('admin.staff.list', compact('staffLists'));
    }

    //スタッフ一覧で選択された１人のスタッフの１ヶ月分の勤怠一覧を表示
    public function showAttendanceListByStaff($id)
    {
        $staff = User::with('attendances')->findOrFail($id);

        //指定が無ければ今日が属する月を、指定があればその月を設定したい
        $currentDay = Carbon::today();
        $firstOfMonth = $currentDay->copy()->firstOfMonth();
        $endOfMonth = $currentDay->copy()->endOfMonth();

        //1日～末日までの日付の配列を作成
        $dates = [];
        for ($date = $firstOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dates[] = $date->copy();
        }

        //今月分の勤怠を取得（休憩を含む）。日付でキー指定する
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$firstOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->KeyBy('date');

        //viewファイルに渡すための設定
        $attendanceRecords = [];
        foreach ($dates as $date) {
            $attendance = $attendances[$date->toDateString()] ?? null;

            $clockIn = optional($attendance)->clock_in;
            $clockOut = optional($attendance)->clock_out;
            $clockInFormatted = optional($attendance)->clock_in_formatted;
            $clockOutFormatted = optional($attendance)->clock_out_formatted;

            //休憩合計時間を計算
            $rests = optional($attendance)->rests ?? collect();
            $totalRestMinutes = $rests->sum(
                function ($rest) {
                    if ($rest->rest_start && $rest->rest_end) {
                        return Carbon::parse($rest->rest_end)->diffInMinutes(Carbon::parse($rest->rest_start));
                    }
                    return 0;
                }
            );

            //休憩合計時間の整形
            $totalRestFormatted = $totalRestMinutes > 0
                ? Carbon::createFromTime(0, 0)->addMinutes($totalRestMinutes)->isoFormat('H:mm')
                : null;

            //(出勤～退勤) - 休憩時刻 により、実労働時間を計算
            $totalWorkHours = null;
            $totalWorkFormatted = null;
            if ($clockIn && $clockOut) {
                $totalWorkHours = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($clockIn)) - $totalRestMinutes;
                if ($totalWorkHours >= 0) {
                    $totalWorkFormatted = Carbon::createFromTime(0, 0)->addMinutes($totalWorkHours)->isoFormat('H:mm');
                } else {
                    $absolute = abs($totalWorkHours);
                    $totalWorkFormatted = '-' . Carbon::createFromTime(0, 0)->addMinutes($absolute)->isoFormat('H:mm');
                }
            }

            $attendanceRecords[] = [
                'date' => $date->isoFormat('M月D日（ddd）'),
                'clock_in' => $clockInFormatted,
                'clock_out' => $clockOutFormatted,
                'total_rest' => $totalRestMinutes,
                'total_rest_formatted' => $totalRestFormatted,
                'total_work_hours' => $totalWorkHours,
                'total_work_formatted' => $totalWorkFormatted,
            ];
        }

        return view('admin.attendance.list_by_staff', compact('staff', 'dates', 'attendanceRecords', 'currentDay'));
    }

    public function showDetail()
    {
        return view('admin/attendance/detail');
    }
}
