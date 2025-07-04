<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class StaffAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //自分が行った勤怠情報が全て表示されている
    public function test_show_own_attendance_data()
    {
        //勤怠情報が登録されたユーザーにログイン
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

        //勤怠一覧画面にて、登録されている情報が順番通りに表示されている
        $response = $this->get('/attendance/list');
        $totalWorkFormatted = optional($attendance)->total_work_formatted;
        $response->assertSeeInOrder(
            [
                now()->isoFormat('M月D日'),
                $attendance->clock_in->format('H:i'),
                $attendance->clock_out->format('H:i'),
                $attendance->total_rest_formatted, //Attendanceモデルから呼び出し
                $attendance->total_work_formatted,
            ]
        );
    }

    public function test_show_current_month_at_attendance_list()
    {
        //ユーザーにログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance/list');
        $response->assertSee(now()->isoFormat('M月'));
    }

    //「前月」を押下したときに表示月の前月の情報が表示される
    public function test_show_previous_month_when_click_previous_month_link_at_attendance_list()
    {
        //前月に勤怠情報が登録されたユーザーにログイン
        $user = User::factory()->create();
        $previousMonth = Carbon::now()->subMonthNoOverflow(1)->toDateString();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => $previousMonth,
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $attendance->rests()->create([
            'rest_start' => Carbon::parse('11:30'),
            'rest_end' => Carbon::parse('12:20'),
        ]);
        $this->actingAs($user);

        //勤怠一覧画面から"前月"リンクをクリックし、該当の勤怠データが表示されている
        $response = $this->get('/attendance/list');
        $response = $this->get('/attendance/list?date=' . $previousMonth);
        $response->assertSeeInOrder(
            [
                Carbon::parse($previousMonth)->isoFormat('M月D日'),
                $attendance->clock_in->format('H:i'),
                $attendance->clock_out->format('H:i'),
                $attendance->total_rest_formatted, //Attendanceモデルから呼び出し
                $attendance->total_work_formatted,
            ]
        );
    }

    //「翌月」を押下したときに表示付きの翌月の情報が表示される
    public function test_show_next_month_when_click_next_month_link_at_attendance_list()
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
        $nextMonth = Carbon::now()->addMonthNoOverflow(1)->toDateString();

        //勤怠一覧画面から"翌月"リンクをクリックし、勤怠データが表示されていない、翌月の月日が表示されている
        $response = $this->get('/attendance/list');
        $response = $this->get('/attendance/list?date=' . $nextMonth);
        $response->assertSeeInOrder(
            [
                Carbon::parse($nextMonth)->isoFormat('M月D日'),
            ]
        );
        $response->assertDontSee([
            $attendance->clock_in->format('H:i'),
            $attendance->clock_out->format('H:i'),
            $attendance->total_rest_formatted,
            $attendance->total_work_formatted,
        ]);
    }

    //「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_move_detail_page_when_click_detail_link_at_attendance_list()
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

        //勤怠一覧画面から"詳細"リンクをクリックするとその日の詳細画面へ遷移することを確認
        $response = $this->get('/attendance/list');
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertViewIs('staff.attendance.detail');
        $response->assertSeeInOrder([
            '勤怠詳細',
            Carbon::parse(now())->isoFormat('M月D日'),
        ]);
    }
}
