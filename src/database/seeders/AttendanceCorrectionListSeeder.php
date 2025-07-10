<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;

class AttendanceCorrectionListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pendingCorrection = AttendanceCorrection::create([
            'attendance_id' => 10,
            'corrected_clock_in' => '09:15',
            'corrected_clock_out' => '13:25',
            'note' => '退勤打刻後、急遽顧客対応したため',
            'approve_status' => 'pending', //承認待ちのデータ
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $approvedCorrection = AttendanceCorrection::create([
            'attendance_id' => 11,
            'corrected_clock_in' => '08:45',
            'corrected_clock_out' => '17:45',
            'note' => '出勤時の打刻忘れのため',
            'approve_status' => 'approved', //承認済みのデータ
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $approvedCorrection->restCorrections()->create([
            'corrected_rest_start' => '11:45',
            'corrected_rest_end' => '12:30',
        ]);

        $attendanceIds = Attendance::whereBetween('id', [12, 20])->pluck('id')->take(4)->values();

        foreach ($attendanceIds->slice(0, 2)->values() as $index => $attendanceId) {
            AttendanceCorrection::factory()->create([
                'attendance_id' => $attendanceId,
                'approve_status' => 'pending', //承認待ちのデータ
                'created_at' => now()->subDays(2 - $index), // $indexが 0→2日前, 1→1日前, 2→当日
                'updated_at' => now()->subDays(2 - $index),
            ]);
        }

        foreach ($attendanceIds->slice(2, 2)->values() as $index => $attendanceId) {
            AttendanceCorrection::factory()->create([
                'attendance_id' => $attendanceId,
                'approve_status' => 'approved', //承認済みのデータ
                'created_at' => now()->subDays(2 - $index), // $indexが 0→2日前, 1→1日前, 2→当日
                'updated_at' => now()->subDays(2 - $index),
            ]);
        }
    }
}
