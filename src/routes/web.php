<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

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

//ユーザ認証を要するルート
Route::middleware('auth')->group(function () {
    Route::prefix('attendance')->group(function () {
        //勤怠登録（トップ）画面の表示
        Route::get('/', [AttendanceController::class, 'index']);
        //勤怠一覧の表示
        Route::get('/list', [AttendanceController::class, 'showAttendanceList']);
        //勤怠打刻
        Route::post('clockIn', [AttendanceController::class, 'clockIn']);
        //勤怠詳細画面の表示　※あとで/detailを{id}に変更する
        Route::get('/detail', [AttendanceController::class, 'showDetail']);
    });
    //申請一覧の表示
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index']);
});


//管理者権限での認証を要するルート
Route::middleware(['auth', 'adminOnly'])->group(function () {
    Route::prefix('admin')->group(function () {
        //管理者権限で勤怠一覧画面を表示
        Route::get('/attendance/list', [AdminController::class, 'showAttendanceListAll']);
        //管理者権限でスタッフ一覧画面を表示
        Route::get('/staff/list', [AdminController::class, 'showStaffList']);
        //管理者権限でスタッフ別勤怠一覧表示　パスの修正必要（{id}を足す）
        Route::get('/attendance/staff/{id}', [AdminController::class, 'showAttendanceListByStaff']);
    });
});

//ユーザー登録画面
Route::middleware('guest')->group(
    function () {
        Route::get('/register', [RegisteredUserController::class, 'create']);
        Route::post('/register', [RegisteredUserController::class, 'store']);
    }
);

//一般職員としてログインする
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
//管理者ログイン画面の表示
Route::get('/admin/login', [AdminController::class, 'login']);
//管理者としてログインする
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
//ログアウトする
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
