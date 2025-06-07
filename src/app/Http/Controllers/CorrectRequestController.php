<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CorrectRequestController extends Controller
{
    public function index()
    {
        return view('staff.request.list');
    }
}
