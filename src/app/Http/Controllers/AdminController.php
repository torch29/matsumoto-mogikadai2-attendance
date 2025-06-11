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

    public function showAttendanceListAll()
    {
        //「選ばれたある特定の一日」の全員の勤怠情報

        //日付のリクエストがあったらその日、なければnowを表示にしたい（？）
        $date = Carbon::today();
        $titleDate = Carbon::today()->isoFormat('Y年M月D日');
        $attendances = Attendance::whereDate('date', $date)->get();

        return view('admin.attendance.list_all', compact('attendances', 'titleDate'));
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

        $attendances = Attendance::where('user_id', $id)
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
                'clock_in' => $record && $record->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '',
                'clock_out' => $record && $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '',
                'record' => $record,
            ];
        }

        return view('admin.attendance.list_by_staff', compact('staff', 'dates', 'attendanceRecords', 'currentDay'));
    }
}
