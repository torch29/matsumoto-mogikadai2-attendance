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
        if (Auth::user()->is_admin) {
            $attendanceCorrections = AttendanceCorrection::with('attendance.user')->get();
            $view = 'admin.request.list';
        } else {
            $attendanceCorrections = AttendanceCorrection::with('attendance.user')
                ->whereHas('attendance', function ($query) {
                    $query->where('user_id', Auth::id());
                })->get();
            $view = 'staff.request.list';
        }

        $stampCorrectionRecords = [];

        foreach ($attendanceCorrections as $correction) {
            $correctionTargetDateFormatted = $correction->correction_target_date_formatted;
            //$correctionClockInFormatted = $correction->correction_clock_in_formatted;
            //$correctionClockOutFormatted = $correction->correction_clock_out_formatted;
            $requestedAtFormatted = $correction->requested_at_formatted;

            //viewファイルに渡す配列
            $stampCorrectionRecords[] = [
                'id' => $correction->id,
                'status' => $correction->approvalStatusLabel(),
                'name' => $correction->attendance->user->name,
                'correction_target_date' => $correctionTargetDateFormatted,
                'note' => $correction->note,
                'requested_at' => $requestedAtFormatted,
            ];
        }
        return view($view, compact('stampCorrectionRecords'));
    }

    //一般職員による、自分の勤怠データの修正申請
    public function requestStampCorrection(Request $request)
    {
        $attendance = Attendance::with('attendanceCorrections')->find($request->attendance_id);

        if (!$attendance || $attendance->user_id !== Auth::id()) {
            return redirect()->back()->with('error', '自分以外のデータは修正できません。');
        }

        /*書きかけ 申請の承認待ち中は、編集できない
        if ($attendance->attendanceCorrections->status == 'pending') {
            return redirect();
        }
            */
        //dd($request);

        $attendanceCorrection = AttendanceCorrection::create([
            'attendance_id' => $request->attendance_id,
            'corrected_clock_in' => $request->corrected_clock_in,
            'corrected_clock_out' => $request->corrected_clock_out,
            'note' => $request->note,
        ]);

        $restCorrections = $request->all();

        foreach ($restCorrections['rest_corrections'] as $restCorrection) {
            /*「new」行など、空の行をスキップする（オプション）
            if (empty($restCorrection['corrected_rest_start']) && empty($restCorrection['corrected_rest_end'])) {
                continue;
            }*/
            //dd($restCorrections['rest_corrections']);
            RestCorrection::create([
                'attendance_correction_id' => $attendanceCorrection->id,
                'corrected_rest_start' => $restCorrection['corrected_rest_start'],
                'corrected_rest_end' => $restCorrection['corrected_rest_end'],
            ]);
        }

        //$id = $request->id;

        return redirect('/stamp_correction_request/list');
    }

    public function showApprove($id)
    {
        $attendanceCorrection = AttendanceCorrection::with('attendance.user', 'restCorrections')->find($id);

        return view('admin.request.approve', compact('attendanceCorrection'));
    }
}
