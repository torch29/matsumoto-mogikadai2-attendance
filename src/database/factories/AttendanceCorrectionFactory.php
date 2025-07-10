<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;
use App\Models\Attendance;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AttendanceCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::inRandomOrder()->first()->id,
            'corrected_clock_in' => Carbon::createFromTime(9, 0),
            'corrected_clock_out' => Carbon::createFromTime(18, 0),
            'note' => '申請のダミーデータです。',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
