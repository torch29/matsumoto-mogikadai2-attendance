<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Rest;

class RestStampTest extends TestCase
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

    //休憩ボタンが機能する
    public function test_user_can_stamp_rest_start()
    {
        //ステータスが出勤中のユーザーにログイン
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
        ]);
        $this->actingAs($user);

        //ステータスが[出勤中]であり、[休憩入]ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        //出勤ボタンを押下するとステータスが出勤中に変わる。データベースに打刻したユーザーのIDがあり、clock_inカラムにデータが存在することを確認
        $response = $this->post('attendance/restStart');
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
        $rest = Rest::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($rest->rest_start);
    }

    //休憩は一日に何回もできる
    public function test_user_can_stamp_rest_start_several_times_a_day()
    {
        $user = User::factory()->create();
        $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
        ]);
        $this->actingAs($user);

        //[休憩入]を打刻し指定秒数経過後、[休憩戻]を打刻
        $response = $this->get('/attendance');
        $response = $this->post('attendance/restStart');
        $response = $this->get('/attendance');
        $this->travelForStamp();
        $response = $this->post('/attendance/restEnd');

        //打刻画面にアクセスし、[休憩入]ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
        $this->travelBack();
    }

    //休憩戻ボタンが機能する
    public function test_user_can_stamp_end_start()
    {
        $user = User::factory()->create();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
        ]);
        $this->actingAs($user);

        //[休憩入]を打刻し指定秒数経過後、[休憩戻]を打刻する
        $response = $this->get('/attendance');
        $response = $this->post('attendance/restStart');
        $response = $this->get('/attendance');
        $this->travelForStamp();
        $response = $this->post('/attendance/restEnd');

        //打刻画面にアクセスし、[休憩入]ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
        $this->travelBack();
        $rest = Rest::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($rest->rest_end);
    }

    //休憩戻は一日に何回でも可能
    public function test_user_can_stamp_rest_end_several_times_a_day()
    {
        $user = User::factory()->create();
        $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:00'),
        ]);
        $this->actingAs($user);

        //[休憩入]～[休憩戻]を打刻後、再度[休憩入]を打刻
        $response = $this->get('/attendance');
        $response = $this->post('attendance/restStart');
        $response = $this->get('/attendance');
        $this->travelForStamp();
        $response = $this->post('/attendance/restEnd');
        $response = $this->get('/attendance');
        $this->travelForStamp();
        $response = $this->post('attendance/restStart');

        //打刻画面にアクセスし[休憩戻]ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
        $this->travelBack();
    }

    //休憩時間が勤怠一覧画面で確認できる
    public function test_show_rest_time_at_attendance_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        //[休憩入]～[休憩戻]を打刻後、再度[休憩入]を打刻
        $response = $this->get('/attendance');
        $response = $this->post('attendance/restStart');
        $response = $this->get('/attendance');
        $this->travelForStamp();
        $response = $this->post('/attendance/restEnd');

        //休憩入・休憩戻を押下後、勤怠一覧画面にアクセスし日付を確認する
        $response = $this->post('attendance/clockIn');
        $response = $this->get('/attendance/list');
        $response->assertSeeInOrder(
            [
                now()->isoFormat('M月D日'),
                ('0:00'), //休憩合計時間は15秒のため0:00表記となる
            ]
        );
    }
}
