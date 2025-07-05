<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //管理者は、勤怠一覧画面からその日になされた全ユーザーの勤怠情報を確認できる
    public function test_admin_can_check_all_attendance_data_for_staff()
    {
        $user1 = User::factory()->create([
            'name' => 'テスト　職員１'
        ]);
        $user2 = User::factory()->create([
            'name' => 'サンプル　職員２'
        ]);
        $attendance1 = $user1->attendances()->create([
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('07:50'),
            'clock_out' => Carbon::parse('17:45'),
        ]);
        $attendance2 = $user2->attendances()->create([
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:05'),
        ]);
        $restFor2 = $attendance2->rests()->create([
            'rest_start' => '11:30',
            'rest_end' => '12:30',
        ]);
        //管理者としてログイン
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);
        $this->actingAs($admin);

        //管理者が当日の勤怠一覧画面にアクセスし、打刻されているデータが表示されていることを確認
        $response = $this->get('/admin/attendance/list');
        $response->assertViewIs('admin.attendance.list_all');
        $response->assertSeeInOrder([
            $user1->name,
            $attendance1->clock_in->format('H:i'),
            $attendance1->clock_out->format('H:i'),
            $attendance1->total_work_formatted,
            $user2->name,
            $attendance2->clock_in->format('H:i'),
            $restFor2->total_rest_formatted,
            $attendance2->total_work_formatted,
        ]);
    }

    //管理者が勤怠一覧画面に遷移した際、現在の日付が表示される
    public function test_show_current_date_at_all_attendance_list()
    {
        //管理者としてログイン
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);
        $this->actingAs($admin);

        //勤怠一覧画面にアクセスし、現在の日付が表示されていることを確認
        $response = $this->get('/admin/attendance/list');
        $response->assertViewIs('admin.attendance.list_all');
        $response->assertSee(now()->isoFormat('Y年M月D日'));
    }
}
