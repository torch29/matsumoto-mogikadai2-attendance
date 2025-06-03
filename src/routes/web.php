<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//勤怠登録（トップ）画面の表示
Route::get('/attendance', [AttendanceController::class, 'index']);
//勤怠一覧の表示
Route::get('attendance/list', [AttendanceController::class, 'showAttendanceList']);
//申請一覧の表示
Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index']);
