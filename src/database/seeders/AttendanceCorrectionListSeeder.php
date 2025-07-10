<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $correctionRequests = [
            [
                'attendance_id' => 10,
                'corrected_clock_in' => '09:15',
                'corrected_clock_out' => '13:25',
                'note' => '退勤打刻後、急遽顧客対応したため',
                'approve_status' => 'pending', //承認待ちのデータ
                'created_at' => now(),
            ],

            [
                'attendance_id' => 11,
                'corrected_clock_in' => '08:45',
                'corrected_clock_out' => '17:45',
                'note' => '出勤時の打刻忘れのため',
                'approve_status' => 'approved', //承認済みのデータ
                'created_at' => now(),
            ],
        ];

        foreach ($correctionRequests as $request) {
            DB::table('attendance_corrections')->insert($request);
        }
    }
}
