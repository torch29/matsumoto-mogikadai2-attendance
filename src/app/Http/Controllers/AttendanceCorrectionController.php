<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;

class AttendanceCorrectionController extends Controller
{
    //申請一覧画面の表示
    public function index()
    {
        /* 全員の申請一覧を表示　　管理者に流用できるかも
        $attendanceCorrections = AttendanceCorrection::with('attendance.user')->get();
        */
        $attendanceCorrections = AttendanceCorrection::with('attendance.user')
            ->whereHas('attendance', function ($query) {
                $query->where('user_id', Auth::id());
            })->get();

        $stampCorrectionRecords = [];

        foreach ($attendanceCorrections as $correction) {
            $correctionTargetDateFormatted = $correction->correction_target_date_formatted;
            $correctionClockInFormatted = $correction->correction_clock_in_formatted;
            $correctionClockOutFormatted = $correction->correction_clock_out_formatted;
            $requestedAtFormatted = $correction->requested_at_formatted;

            //viewファイルに渡す配列
            $stampCorrectionRecords[] = [
                'status' => $correction->approvalStatusLabel(),
                'name' => $correction->attendance->user->name,
                'correction_target_date' => $correctionTargetDateFormatted,
                'correction_clock_in' => $correctionClockInFormatted,
                'correction_clock_out' => $correctionClockOutFormatted,
                'note' => $correction->note,
                'requested_at' => $requestedAtFormatted,
            ];
        }

        return view('staff.request.list', compact('stampCorrectionRecords'));
    }

    //一般職員による、自分の勤怠データの修正申請
    public function requestStampCorrection(Request $request)
    {
        $attendance = Attendance::find($request->attendance_id);

        if (!$attendance || $attendance->user_id !== Auth::id()) {
            return redirect()->back()->with('error', '自分以外のデータは修正できません。');
        }

        AttendanceCorrection::create([
            'attendance_id' => $request->attendance_id,
            'corrected_clock_in' => $request->corrected_clock_in,
            'corrected_clock_out' => $request->corrected_clock_out,
            'note' => $request->note,
        ]);
        //$id = $request->id;

        return redirect('/stamp_correction_request/list');
    }
}
