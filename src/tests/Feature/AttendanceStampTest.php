<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

    //出勤ボタンが機能する
    public function test_user_can_stamp_clock_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        //勤務外のステータスのユーザーが打刻画面にアクセス、出勤ボタンがあることを確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        //出勤ボタンを押下するとステータスが出勤中に変わる。データベースに打刻したユーザーのIDがあり、clock_inカラムにデータが存在することを確認
        $response = $this->post('attendance/clockIn');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertNotNull($attendance->clock_in);
    }

    //出勤は一日一回のみできる（退勤済みの場合、出勤できない）
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
    }

    //出勤時刻が勤怠一覧画面で確認できる
    public function test_show_clock_in_time_at_attendance_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        //勤務外のステータスのユーザーが打刻画面にアクセス、出勤ボタンがあることを確認
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        //出勤ボタンを押下する→データベースに打刻したユーザーのIDがあり、clock_inカラムにデータが存在することを確認
        $response = $this->post('attendance/clockIn');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);
        $attendance = Attendance::where('user_id', $user->id)->first();
        $this->assertNotNull($attendance->clock_in);

        //勤怠一覧画面にアクセスし、出勤時刻が表示されていることを確認
        $response = $this->get('/attendance/list');
        $response->assertSee(now()->format('H:i'));
        $response->assertSeeInOrder([
            now()->isoFormat('M月D日'),
            now()->format('H:i'),
        ]);
    }
}
