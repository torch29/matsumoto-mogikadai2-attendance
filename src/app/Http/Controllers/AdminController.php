<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    /* 管理者ログイン画面表示 */
    public function login()
    {
        return view('admin.auth.login');
    }

    /*「選ばれたある特定の一日」の全員の勤怠情報 */
    public function showAttendanceListAll(Request $request)
    {
        //日付のリクエストがあったらその日、なければ当日を表示
        $targetDate = $request->date
            ? Carbon::parse($request->date)->startOfDay()
            : Carbon::today();

        $staffMembers = User::with(['attendances' => function ($query) use ($targetDate) {
            $query->where('date', $targetDate);
        }])
            ->where('is_admin', 0) //管理者を除外する
            ->leftJoin('attendances', function ($join) use ($targetDate) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->where('attendances.date', '=', $targetDate);
            })
            ->orderByRaw('attendances.clock_in IS NULL') //打刻がない人は後に
            ->select('users.*') //Userのカラムのみ取得する
            ->get();

        //前日と翌日を取得
        $previousDay = Carbon::parse($targetDate)->subDay()->toDateString();
        $nextDay = Carbon::parse($targetDate)->addDay()->toDateString();

        $attendanceOfEachStaffMembers = [];

        foreach ($staffMembers as $staff) {
            $attendance = $staff->attendances->first();

            //勤怠データがない場合、空のインスタンスを作成する
            if (!$attendance) {
                $attendance = new Attendance();
            }
            $attendance->name = $staff->name;
            $attendanceOfEachStaffMembers[] =
                $attendance;
        }

        return view('admin.attendance.list_all', compact('attendanceOfEachStaffMembers', 'attendance', 'targetDate', 'previousDay', 'nextDay'));
    }

    /* スタッフ一覧の表示 */
    public function showStaffList()
    {
        $staffLists = User::with('attendances')->get();

        return view('admin.staff.list', compact('staffLists'));
    }

    /* 選択された１人のスタッフの１ヶ月分の勤怠一覧（スタッフ別勤怠一覧画面）を表示 */
    public function showAttendanceListByStaff(Request $request, $id)
    {
        if (!$id) {
            return redirect()->back()->with('error', '該当のデータがありません。');
        }
        $staff = User::with('attendances')->find($id);

        if (!$staff) {
            return redirect('/admin/staff/list')->with('error', '該当のデータがありません。');
        }

        //指定が無ければ今月を、指定があればその月を設定
        $selectDate = $request->date
            ? Carbon::parse($request->date)->startOfDay()
            : Carbon::today();
        $firstOfMonth = $selectDate->copy()->firstOfMonth();
        $endOfMonth = $selectDate->copy()->endOfMonth();

        $previousMonth = Attendance::getPreviousMonth($selectDate);
        $nextMonth = Attendance::getNextMonth($selectDate);

        //1日～末日までの日付の配列を作成
        $dates = [];
        for ($date = $firstOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dates[] = $date->copy();
        }

        //今月分の勤怠を取得（休憩を含む）。日付でキー指定する
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$firstOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->mapWithKeys(function ($attendance) {
                return [$attendance->date->toDateString() => $attendance];
            });

        //viewファイルに渡すための設定
        $attendanceRecords = [];
        foreach ($dates as $date) {
            $attendance = $attendances[$date->toDateString()] ?? null;

            if (!$attendance) {
                $attendance = new Attendance([
                    'date' => $date->isoFormat('M月D日（ddd）'),
                ]);
            }
            $attendanceRecords[] = $attendance;
        }

        return view('admin.attendance.list_by_staff', compact('staff', 'dates', 'attendanceRecords', 'selectDate', 'previousMonth', 'nextMonth'));
    }

    /* 勤怠一覧画面からCSV出力 */
    public function exportCsv(Request $request, $id)
    {
        $selectDate = $request->date
            ? Carbon::parse($request->date)->startOfDay()
            : Carbon::today();
        $firstOfMonth = $selectDate->copy()->firstOfMonth();
        $endOfMonth = $selectDate->copy()->endOfMonth();

        //今月分の勤怠を取得（休憩を含む）。日付でキー指定する
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$firstOfMonth, $endOfMonth])
            ->with('rests')
            ->orderBy('date')
            ->get();

        $staff = User::findOrFail($id);
        $staffName = str_replace(array(' ', '　'), '', $staff->name);
        $filename = $staffName . '_' . $selectDate->format('Y_m') . '.csv';
        $header = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($attendances, $staffName) {
            $stream = fopen('php://output', 'w');
            fputs($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['職員名', '日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                fputcsv($stream, [
                    $staffName,
                    $attendance->date->format('Y/m/d'),
                    optional($attendance->clock_in)->format('H:i'),
                    optional($attendance->clock_out)->format('H:i'),
                    $attendance->total_rest_formatted,
                    $attendance->total_work_formatted,
                ]);
            }

            fclose($stream);
        };
        return response()->stream($callback, 200, $header);
    }
}
