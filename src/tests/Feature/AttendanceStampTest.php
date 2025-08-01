<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStampTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    private function travelForStamp(): void
    {
        //アプリで打刻間隔に10秒以上必要としているため、15秒進める
        $this->travel(15)->seconds();
    }

    /* 出勤ボタンが機能する */
    public function test_user_can_stamp_clock_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        //勤務外のステータスのユーザーが打刻画面にアクセス、出勤ボタンがあることを確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
        $response->assertSee('出勤');
        $response->assertSee('attendance/clockIn');

        //出勤ボタンを押下するとステータスが出勤中に変わる。データベースに打刻したユーザーのIDがあり、clock_inカラムにデータが存在することを確認
        $response = $this->post('attendance/clockIn');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_in' => $attendance->clock_in->format('H:i:s'),
        ]);

        //詳細画面でも打刻した時刻が表示されている
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertViewHas('attendance', function ($records) use ($attendance) {
            return $records->clock_in_formatted === $attendance->clock_in_formatted;
        });
        $response->assertSeeInOrder(
            [
                $user->name,
                $attendance->date->isoFormat('M月D日'),
                '出勤・退勤',
                $attendance->clock_in_formatted,
            ]
        );
    }

    /* 出勤は一日一回のみできる（退勤済みの場合、出勤できない） */
    public function test_user_can_stamp_clock_in_only_once_a_day()
    {
        //退勤済みのユーザーを作成
        $user = User::factory()->create();
        $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
        $this->actingAs($user);

        //打刻画面にアクセスし、ステータスが[退勤済]であることと、出勤ボタンが表示されていないことを確認
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');
        $response->assertDontSee('attendance/clockIn');
    }

    /* 出勤時刻が勤怠一覧画面で確認できる */
    public function test_show_clock_in_time_at_attendance_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        //勤務外のステータスのユーザーが打刻画面にアクセス、出勤ボタンがあることを確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        //出勤ボタンを押下後、勤怠一覧画面にアクセスし出勤打刻した時刻が表示されていることを確認
        $response = $this->post('attendance/clockIn');
        $response = $this->get('/attendance/list');
        $attendance = Attendance::where('user_id', $user->id)->first();
        $response->assertSeeInOrder([
            $attendance->date->isoFormat('M月D日'),
            $attendance->clock_in_formatted,
        ]);
    }

    /* 退勤ボタンが機能する */
    public function test_user_can_stamp_clock_out()
    {
        //ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
        ]);
        $this->actingAs($user);

        //ステータスが[出勤中]であり、[退勤]ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        $response->assertSee('退勤');
        $response->assertSee('attendance/clockOut');

        //退勤ボタンを押下するとステータスが退勤済に変わる。データベースに打刻したユーザーのIDがあり、clock_outカラムにデータが存在することを確認
        $response = $this->post('attendance/clockOut');
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');
        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'clock_out' => $attendance->clock_out->format('H:i:s'),
        ]);

        //詳細画面でも打刻した時刻が表示されている
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertSeeInOrder(
            [
                $user->name,
                $attendance->date->isoFormat('M月D日'),
                '出勤・退勤',
                $attendance->clock_out_formatted,
            ]
        );
    }

    /* 退勤時刻が勤怠一覧画面で確認できる */
    public function test_show_clock_out_time_at_attendance_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        //勤務外のステータスのユーザーが打刻画面にアクセス、出勤ボタンがあることを確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        //出勤及び退勤ボタン押下後、勤怠一覧画面にアクセスし退勤打刻した時刻が表示されていることを確認
        $response = $this->post('attendance/clockIn');
        $response = $this->get('/attendance');
        $this->travelForStamp();
        $response = $this->post('/attendance/clockOut');
        $response = $this->get('/attendance/list');
        $attendance = Attendance::where('user_id', $user->id)->first();
        $response->assertSeeInOrder([
            $attendance->date->isoFormat('M月D日'),
            $attendance->clock_out_formatted,
        ]);
        $this->travelBack();
    }
}
