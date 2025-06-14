<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-1 week', 'now', 'Asia/Tokyo');
        $clockIn = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $this->faker->dateTimeBetween('8:30', '10:00')->format('H:i'));
        $clockOut = (clone $clockIn)->addHours(rand(4, 10))->addMinutes(rand(0, 59));

        return [
            'user_id' => rand(2, 6),
            'date' => $clockIn->copy()->startOfDay(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
