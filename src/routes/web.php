<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceStampController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\AttendanceCorrection;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

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

//メール認証実装用のルート
Route::get('email/verify', function () {
    return view('staff.auth.verify');
})->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');
//認証用メールの再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証用メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

//ユーザ認証＋メール認証を要するルート
Route::middleware('auth', 'verified')->group(function () {
    Route::prefix('attendance')->group(function () {
        //勤怠登録（トップ）画面の表示
        Route::get('/', [AttendanceStampController::class, 'index']);
        //勤怠一覧の表示
        Route::get('/list', [AttendanceController::class, 'showAttendanceList']);
        //勤怠打刻
        Route::post('clockIn', [AttendanceStampController::class, 'clockIn']);
        //退勤打刻
        Route::post('clockOut', [AttendanceStampController::class, 'clockOut']);
        //休憩入打刻
        Route::post('restStart', [AttendanceStampController::class, 'restStart']);
        //休憩戻打刻
        Route::post('restEnd', [AttendanceStampController::class, 'restEnd']);
        //勤怠詳細画面の表示
        Route::get('/{id}', [AttendanceController::class, 'showDetail']);
    });
    //詳細画面からボタン押下にて修正の申請をする
    Route::post('/correction_request', [AttendanceCorrectionController::class, 'store']);
    //申請一覧の表示
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'index'])->name('correction_requests.list');
});


//管理者権限での認証を要するルート
Route::middleware(['auth', 'verified', 'adminOnly'])->group(function () {
    Route::prefix('admin')->group(function () {
        //管理者権限で勤怠一覧画面を表示
        Route::get('/attendance/list', [AdminController::class, 'showAttendanceListAll'])->name('admin.attendances.list-by-date');
        //管理者権限でスタッフ一覧画面を表示
        Route::get('/staff/list', [AdminController::class, 'showStaffList'])->name('admin.staff-list');
        //管理者権限でスタッフ別勤怠一覧表示
        Route::get('/attendance/staff/{id}', [AdminController::class, 'showAttendanceListByStaff'])->name('admin.attendances.list-by-staff');
        //勤怠詳細画面の表示
        Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('admin.attendance.detail');
        //申請一覧の表示
        Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'index'])->name('admin.correction_requests.list');
        //申請承認画面の表示
        Route::get('/stamp_correction_request/approve/{id}', [AttendanceCorrectionController::class, 'showApprove'])->name('admin.showApprove');
        //詳細画面からボタン押下にて修正の申請をする
        Route::post('/correction', [AttendanceCorrectionController::class, 'adminCorrection']);
        Route::post('/approve', [AttendanceCorrectionController::class, 'approve']);
        //CSV出力
        Route::post('/export/{id}', [AdminController::class, 'exportCsv']);
    });
});

//ユーザー登録画面
Route::middleware('guest')->group(
    function () {
        Route::get('/register', [RegisteredUserController::class, 'create']);
        Route::post('/register', [RegisteredUserController::class, 'store']);
    }
);

//一般職員画面からのログイン
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
//管理者ログイン画面の表示
Route::get('/admin/login', [AdminController::class, 'login']);
//管理者としてログインする
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
//ログアウトする
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
