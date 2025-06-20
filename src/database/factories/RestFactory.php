<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $restStart = Carbon::now()->addHours(rand(0, 5));
        $restEnd = (clone $restStart)->addMinutes(rand(10, 60));

        return [
            'rest_start' => $restStart,
            'rest_end' => $restEnd,
        ];
    }
}
