<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class StaffAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //勤怠詳細画面の「名前」がログインユーzぁの氏名になっている
    public function test_show_users_own_name_at_attendance_detail_page()
    {
        //当日に勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $this->actingAs($user);

        //勤怠一覧画面から詳細画面へ遷移し、名前欄にユーザーの名前が表示されていることを確認
        $response = $this->get('/attendance/list');
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertViewIs('staff.attendance.detail');
        $response->assertSeeInOrder(
            [
                '名前',
                $user->name,
            ]
        );
    }

    //勤怠詳細画面の「日付」が選択した日付になっている
    public function test_show_selected_date_at_attendance_detail_page()
    {
        //当日に勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $this->actingAs($user);

        //勤怠一覧画面から当日の詳細画面へ遷移し、登録された日付が表示されていることを確認
        $response = $this->get('/attendance/list');
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSeeInOrder(
            [
                $user->name,
                '日付',
                $attendance->date->isoFormat('Y年'),
                $attendance->date->isoFormat('M月D日'),
            ]
        );
    }

    public function test_show_clock_in_and_clock_out_time_at_attendance_detail_page()
    {
        //当日に勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $attendance->rests()->create([
            'rest_start' => Carbon::parse('11:30'),
            'rest_end' => Carbon::parse('12:20'),
        ]);
        $this->actingAs($user);

        //勤怠一覧画面から当日の詳細画面へ遷移し、登録された日付が表示されていることを確認
        $response = $this->get('/attendance/list');
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSeeInOrder(
            [
                $user->name,
                $attendance->date->isoFormat('M月D日'),
                '出勤・退勤',
                $attendance->clock_in->format('H:i'),
                $attendance->clock_out->format('H:i'),
            ]
        );
    }
}
