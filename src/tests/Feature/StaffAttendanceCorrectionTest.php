<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AttendanceCorrection;

class StaffAttendanceCorrectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_show_error_message_when_clock_in_time_is_after_clock_out_time_at_attendance_detail_page()
    {
        //勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => '08:00',
            'clock_out' => '18:00',
        ]);
        $this->actingAs($user);

        //勤怠詳細画面にアクセスし、出勤時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertViewIs('staff.attendance.detail');
        $response->assertSeeInOrder(
            [
                '名前',
                $user->name,
            ]
        );
        $response = $this->post('/correction_request', [
            'corrected_clock_in' => Carbon::parse('18:01')->format('H:i'), //出勤時間を退勤時間より後に設定
            'corrected_clock_out' => Carbon::parse('18:00')->format('H:i'),
            'note' => '申請のテスト',
        ]);
        $response->assertSessionHasErrors([
            'corrected_clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_show_error_message_when_rest_start_time_is_after_clock_out_time_at_attendance_detail_page()
    {
        //勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $this->actingAs($user);

        //勤怠詳細画面にアクセスし、休憩開始時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->post('/correction_request', [
            'attendance_id' => $attendance->id,
            'corrected_clock_in' => '08:00',
            'corrected_clock_out' => '18:00',
            'rest_corrections' => [
                'new' => [
                    'corrected_rest_start' => '18:01',
                ],
            ],
            'note' => '申請のテスト',
        ]);
        $response->assertSessionHasErrors([
            'rest_corrections.new.corrected_rest_start' => '休憩時間が不適切な値です',
        ]);
    }
}
