<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminController extends Controller
{
    //管理者ログイン画面表示
    public function login()
    {
        return view('admin.auth.login');
    }

    public function showAttendanceListAll()
    {
        $attendances = Attendance::with('user')->get();

        return view('admin.attendance.list_all', compact('attendances'));
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
