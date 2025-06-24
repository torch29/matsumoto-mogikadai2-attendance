<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\Rest;
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
            //一般職員は自分の勤怠のみ取得
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

    //勤怠データの修正申請
    public function store(Request $request)
    {
        $attendance = Attendance::with('attendanceCorrections')->find($request->attendance_id);

        //データがない or 管理者ではない＆自分自身のデータでもない
        if (!$attendance) {
            return redirect()->back()->with('error', 'データが見つかりません。');
        }
        if (!Auth::user()->is_admin && $attendance->user_id !== Auth::id()) {
            return redirect()->back()->with('error', '自分以外のデータは修正できません。');
        }

        //最新の修正申請を取得、承認待ちの場合は修正申請できない
        $latestCorrection = $attendance->attendanceCorrections->sortByDesc('created_at')->first();

        if ($latestCorrection && $latestCorrection->approve_status == 'pending') {
            return redirect()->back()->with('error', '承認待ちのため現在修正はできません。');
        }

        //データベースへ保存
        try {
            DB::beginTransaction();
            $attendanceCorrection = AttendanceCorrection::create([
                'attendance_id' => $request->attendance_id,
                'corrected_clock_in' => $request->corrected_clock_in,
                'corrected_clock_out' => $request->corrected_clock_out,
                'note' => $request->note,
            ]);

            //休憩の修正は配列でくる
            $restCorrections = $request->input('rest_corrections', []);

            foreach ($restCorrections as $restCorrection) {
                //「new（空）」の行をスキップする
                if (empty($restCorrection['corrected_rest_start']) && empty($restCorrection['corrected_rest_end'])) {
                    continue;
                }

                RestCorrection::create([
                    'attendance_correction_id' => $attendanceCorrection->id,
                    'corrected_rest_start' => $restCorrection['corrected_rest_start'],
                    'corrected_rest_end' => $restCorrection['corrected_rest_end'],
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $view = Auth::user()->is_admin
            ? '/admin/stamp_correction_request/list'
            : '/stamp_correction_request/list';

        return redirect($view);
    }

    public function showApprove($id)
    {
        $attendanceCorrection = AttendanceCorrection::with('attendance.user', 'attendance.rests', 'restCorrections')->find($id);

        return view('admin.request.approve', compact('attendanceCorrection'));
    }

    public function approve(Request $request)
    {
        $correction = AttendanceCorrection::with('attendance', 'restCorrections')->find($request->correctionId);

        if ($correction->approve_status === 'approved') {
            return redirect()->back()->with('error', '承認済みのデータです。');
        };

        DB::transaction(function () use ($correction) {
            $correction->attendance->update([
                'clock_in' => $correction->corrected_clock_in,
                'clock_out' => $correction->corrected_clock_out,
            ]);

            $correction->attendance->rests()->delete();

            foreach ($correction->restCorrections as $restCorrection) {
                Rest::create([
                    'attendance_id' => $correction->attendance->id,
                    'rest_start' => $restCorrection->corrected_rest_start,
                    'rest_end' => $restCorrection->corrected_rest_end,
                ]);
            }

            $correction->update([
                'approve_status' => 'approved',
            ]);
        });
        return redirect();
    }
}
