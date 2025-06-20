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
        /* Seederにて指定
        $date = $this->faker->dateTimeBetween('-1 week', 'now', 'Asia/Tokyo');
        $clockIn = Carbon::createFromFormat('Y-m-d H:i', $date->format('Y-m-d') . ' ' . $this->faker->dateTimeBetween('8:30', '10:00')->format('H:i'));
        */

        $clockIn = $this->faker->dateTimeBetween('8:30', '10:00');
        $clockOut = (clone $clockIn)->modify('+' . rand(4, 10) . ' hours +' . rand(0, 59) . ' minutes');

        return [
            'user_id' => rand(2, 6), //user_id及びdateはSeederにて上書きする
            'date' => Carbon::instance($clockIn)->startOfDay(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
