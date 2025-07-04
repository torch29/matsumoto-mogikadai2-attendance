<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class StaffAttendanceInformationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /*
    public function test_show_own_attendance_data()
    {
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $rest = $attendance->rests()->create([
            'rest_start' => Carbon::parse('11:30'),
            'rest_end' => Carbon::parse('12:20'),
        ]);
        $this->actingAs($user);


        $response = $this->get('/attendance/list');
        $response->assertSeeInOrder(
            [
                now()->isoFormat('M月D日'),
            ]
        );
    }
        */
}
