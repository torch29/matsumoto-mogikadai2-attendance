<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CorrectionRequestController extends Controller
{
    public function index()
    {
        return view('staff.request.list');
    }
}
