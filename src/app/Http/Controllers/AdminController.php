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
        $titleDate = Carbon::today();
        $attendances = Attendance::whereDate('date', $date)
            ->with(['user', 'rests'])
            ->get();

        $attendanceRecords = [];

        foreach ($attendances as $attendance) {
            $clockIn = optional($attendance)->clock_in;
            $clockOut = optional($attendance)->clock_out;
            $clockInFormatted = optional($attendance)->clock_in_formatted;
            $clockOutFormatted = optional($attendance)->clock_out_formatted;

            //休憩合計時間をAttendanceモデルから取得
            $rests = optional($attendance)->rests ?? collect();
            $totalRestMinutes = optional($attendance)->total_rest_minutes;
            $totalRestFormatted = optional($attendance)->total_rest_formatted;

            //実労働時間をAttendanceモデルから取得
            $totalWorkHours = optional($attendance)->total_work_minutes;
            $totalWorkFormatted = optional($attendance)->total_work_formatted;

            //viewファイルに渡すための設定
            $attendanceRecords[] = [
                'name' => $attendance->user->name,
                'id' => optional($attendance)->id,
                'clock_in' => $clockInFormatted,
                'clock_out' => $clockOutFormatted,
                'total_rest' => $totalRestMinutes,
                'total_rest_formatted' => $totalRestFormatted,
                'total_work_hours' => $totalWorkHours,
                'total_work_formatted' => $totalWorkFormatted,
            ];
        }

        return view('admin.attendance.list_all', compact('attendanceRecords', 'titleDate'));
    }

    public function showStaffList()
    {
        $staffLists = User::with('attendances')->get();

        return view('admin.staff.list', compact('staffLists'));
    }

    //選択された１人のスタッフの１ヶ月分の勤怠一覧を表示
    public function showAttendanceListByStaff(Request $request, $id)
    {
        if (!$id) {
            return redirect()->back()->with('error', '該当のデータがありません。');
        }
        $staff = User::with('attendances')->find($id);

        if (!$staff) {
            return redirect('/admin/staff/list')->with('error', '該当のデータがありません。');
        }

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
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$firstOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->mapWithKeys(function ($attendance) {
                return [$attendance->date->toDateString() => $attendance];
            });

        //viewファイルに渡すための設定
        $attendanceRecords = [];
        foreach ($dates as $date) {
            $attendance = $attendances[$date->toDateString()] ?? null;

            $clockIn = optional($attendance)->clock_in;
            $clockOut = optional($attendance)->clock_out;
            $clockInFormatted = optional($attendance)->clock_in_formatted;
            $clockOutFormatted = optional($attendance)->clock_out_formatted;

            //休憩合計時間とそのフォーマットをAttendanceモデルから取得
            $rests = optional($attendance)->rests ?? collect();
            $totalRestMinutes = optional($attendance)->total_rest_minutes;
            $totalRestFormatted = optional($attendance)->total_rest_formatted;

            //実労働時間とそのフォーマットをAttendanceモデルから取得
            $totalWorkHours = optional($attendance)->total_work_minutes;
            $totalWorkFormatted = optional($attendance)->total_work_formatted;

            $attendanceRecords[] = [
                'id' => optional($attendance)->id,
                'date' => $date->isoFormat('M月D日（ddd）'),
                'clock_in' => $clockInFormatted,
                'clock_out' => $clockOutFormatted,
                'total_rest' => $totalRestMinutes,
                'total_rest_formatted' => $totalRestFormatted,
                'total_work_hours' => $totalWorkHours,
                'total_work_formatted' => $totalWorkFormatted,
            ];
        }

        return view('admin.attendance.list_by_staff', compact('staff', 'dates', 'attendanceRecords', 'selectDate', 'previousMonth', 'nextMonth'));
    }
}
