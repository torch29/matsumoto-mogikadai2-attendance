<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.attendance.list_all');
    }

    public function showStaffList()
    {
        return view('admin.staff.list');
    }

    //管理者ログイン画面表示
    public function login()
    {
        return view('admin.auth.login');
    }
}
