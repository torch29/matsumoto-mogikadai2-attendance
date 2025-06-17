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
    public function index()
    {
        $attendanceCorrections = AttendanceCorrection::with('attendance.user')->get();
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

    public function requestStampCorrection(Request $request)
    {
        $user = Auth::user();

        AttendanceCorrection::create([
            'attendance_id' => $request->id,
            'corrected_clock_in' => $request->corrected_clock_in,
            'corrected_clock_out' => $request->corrected_clock_out,
            'note' => $request->note,
        ]);

        //$id = $request->id;

        return redirect('/stamp_correction_request/list');
    }
}
