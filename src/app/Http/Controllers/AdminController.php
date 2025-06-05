<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.attendance.list');
    }

    public function showStaffList()
    {
        return view('admin.staff.list');
    }
}
