<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
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
        //「選ばれたある特定の一日」の「全員の勤怠情報」が必要

        //日付のリクエストがあったらその日、なければnowを表示にしたい（？）
        $date = Carbon::today();
        $titleDate = Carbon::today()->isoFormat('Y年M月D日');
        $attendances = Attendance::whereDate('date', $date)->get();

        return view('admin.attendance.list_all', compact('attendances', 'titleDate'));
    }

    public function showStaffList()
    {
        return view('admin.staff.list');
    }

    public function showAttendanceListByStaff()
    {
        return view('admin.attendance.list_by_staff');
    }
}
