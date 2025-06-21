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
    public function index(Request $request)
    {
        //URLからtabパラメータの取得、デフォルトはpending
        $tab = $request->query('tab', 'pending');
        $attendanceCorrectionsQuery = AttendanceCorrection::with('attendance.user');

        if (!Auth::user()->is_admin) {
            //条件追加し一般職員は自分の勤怠のみ取得
            $userId = Auth::id();
            $attendanceCorrectionsQuery->whereHas('attendance', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            });
            $view = 'staff.request.list';
        } else {
            //管理者は全員分のデータ
            $view = 'admin.request.list';
        }

        //tabのステータスによる絞り込み
        if ($tab === 'approved') {
            $attendanceCorrectionsQuery->where('approve_status', 'approved');
        } else {
            $attendanceCorrectionsQuery->where('approve_status', 'pending');
        }
        $attendanceCorrections = $attendanceCorrectionsQuery->get();

        //viewファイルに渡すためのフォーマット
        $stampCorrectionRecords = [];

        foreach ($attendanceCorrections as $attendanceCorrection) {
            $correctionTargetDateFormatted = $attendanceCorrection->correction_target_date_formatted;
            $requestedAtFormatted = $attendanceCorrection->requested_at_formatted;

            //viewファイルに渡す配列
            $stampCorrectionRecords[] = [
                'id' => $attendanceCorrection->id,
                'attendance_id' => $attendanceCorrection->attendance_id,
                'status' => $attendanceCorrection->approvalStatusLabel(),
                'name' => $attendanceCorrection->attendance->user->name,
                'correction_target_date' => $correctionTargetDateFormatted,
                'note' => $attendanceCorrection->note,
                'requested_at' => $requestedAtFormatted,
            ];
        }
        return view($view, compact('stampCorrectionRecords', 'tab'));
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
            //「new（空）」の行をスキップする
            if (empty($restCorrection['corrected_rest_start']) && empty($restCorrection['corrected_rest_end'])) {
                continue;
            }
            //dd($restCorrection);
            //dd($restCorrection['corrected_rest_start']);
            RestCorrection::create([
                'attendance_correction_id' => $attendanceCorrection->id,
                'corrected_rest_start' => $restCorrection['corrected_rest_start'],
                'corrected_rest_end' => $restCorrection['corrected_rest_end'],
            ]);
        }

        return redirect('/stamp_correction_request/list');
    }

    public function showApprove($id)
    {
        $attendanceCorrection = AttendanceCorrection::with('attendance.user', 'restCorrections')->find($id);

        return view('admin.request.approve', compact('attendanceCorrection'));
    }
}
