<?php

namespace Tests\Feature;

use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;


class AttendanceDisplayTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /* 現在の日時情報が表示される */
    public function test_attendance_page_displays_current_datetime()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $now = Carbon::now();

        //現在の年月日が表示される
        $response->assertSee($now->isoFormat('Y年 M月D日（ddd）'));
    }

    /* 勤務外の場合、ステータスに[勤務外]と表示される */
    public function test_attendance_page_displays_off_duty_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertViewIs('staff.attendance.index');

        $response->assertSee('勤務外');
    }

    /* 出勤中の場合、ステータスに[出勤中]と表示される */
    public function test_attendance_page_displays_at_work_status()
    {
        $user = User::factory()->create();
        $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertViewIs('staff.attendance.index');

        $response->assertSee('出勤中');
    }

    /* 休憩中の場合、ステータスに[休憩中]と表示される */
    public function test_attendance_page_displays_in_rest_status()
    {
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
        ]);
        $attendance->rests()->create([
            'rest_start' => Carbon::parse('12:00'),
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertViewIs('staff.attendance.index');

        $response->assertSee('休憩中');
    }

    /* 退勤済みの場合、ステータスに[退勤済]と表示される */
    public function test_attendance_page_displays_already_clocked_out_status()
    {
        $user = User::factory()->create();
        $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $this->actingAs($user);

        $response = $this->get('/attendance');
        $response->assertViewIs('staff.attendance.index');

        $response->assertSee('退勤済');
    }
}
