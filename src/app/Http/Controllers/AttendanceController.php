<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        return view('staff.attendance.index');
    }

    public function showAttendanceList()
    {
        return view('staff.attendance.list');
    }
}
