<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

    /* 管理者は、勤怠一覧画面からその日になされた全ユーザーの勤怠情報を確認できる */
    public function test_admin_can_check_all_attendance_data_for_staff()
    {
        //職員を２人作成し、２人の勤怠情報を登録する
        $user1 = User::factory()->create([
            'name' => 'テスト　職員１'
        ]);
        $user2 = User::factory()->create([
            'name' => 'サンプル　職員２'
        ]);
        //user1は出勤と退勤の打刻がある
        $attendance1 = $user1->attendances()->create([
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('07:50'),
            'clock_out' => Carbon::parse('17:45'),
        ]);
        //user2は出勤と休憩入・休憩戻の打刻があり、まだ退勤していない
        $attendance2 = $user2->attendances()->create([
            'date' => Carbon::now()->toDateString(),
            'clock_in' => Carbon::parse('08:05'),
        ]);
        $restFor2 = $attendance2->rests()->create([
            'rest_start' => Carbon::parse('11:30'),
            'rest_end' => Carbon::parse('12:30'),
        ]);
        //管理者としてログイン
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);
        $this->actingAs($admin);

        //管理者が当日の勤怠一覧画面にアクセスし、打刻されているデータが表示されていることを確認
        $response = $this->get('/admin/attendance/list');
        $response->assertViewIs('admin.attendance.list_all');
        $response->assertViewHas('attendanceOfEachStaffMembers', function ($records) use ($attendance2) {
            return $records[1]->id === $attendance2->id
                && $records[1]->clock_in_formatted === $attendance2->clock_in_formatted
                && $records[1]->clock_out_formatted === $attendance2->clock_out_formatted
                && $records[1]->total_rest_formatted === $attendance2->total_rest_formatted;
        });
        $response->assertSeeInOrder([
            $user1->name,
            $attendance1->clock_in_formatted,
            $attendance1->clock_out_formatted,
            $attendance1->total_work_formatted,
            $user2->name,
            $attendance2->clock_in_formatted,
            $restFor2->total_rest_formatted,
            $attendance2->total_work_formatted,
        ]);
    }

    /* 管理者が勤怠一覧画面に遷移した際、現在の日付が表示される */
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

    /*「前日」を押下したときに前日の勤怠情報が表示される */
    public function test_show_previous_day_when_click_previous_day_link_at_all_attendance_list_for_admin()
    {
        //前日の勤怠情報を作成
        $user = User::factory()->create();
        $previousDay = Carbon::now()->subDay(1)->toDateString();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => $previousDay,
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);

        //管理者としてログイン
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);
        $this->actingAs($admin);

        //管理者用勤怠一覧画面から前日リンクをクリックし、前日の日付と前日に登録された勤怠情報が表示されていることを確認
        $response = $this->get('/admin/attendance/list');
        $response = $this->get('/admin/attendance/list?date=' . $previousDay);
        $response->assertSeeInOrder(
            [
                Carbon::parse($previousDay)->isoFormat('Y年M月D日'),
                $attendance->clock_in_formatted,
                $attendance->clock_out_formatted,
                $attendance->total_work_formatted,
            ]
        );
    }

    /*「翌日」を押下したときに翌日の勤怠一覧が表示される */
    public function test_show_next_day_when_click_next_day_link_at_all_attendance_list_for_admin()
    {
        //当日の勤怠情報を作成
        $user = User::factory()->create();
        $nextDay = Carbon::now()->addDay(1)->toDateString();
        $attendance = $user->attendances()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(), //当日の勤怠情報を作成
            'clock_in' => Carbon::parse('08:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);

        //管理者としてログイン
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);
        $this->actingAs($admin);

        //管理者用勤怠一覧画面から翌日のリンクをクリックし、勤怠情報が表示されておらず、翌日の日付が表示されている
        $response = $this->get('/admin/attendance/list');
        $response = $this->get('/admin/attendance/list?date=' . $nextDay);
        $response->assertSee(Carbon::parse($nextDay)->isoFormat('Y年M月D日'));
        $response->assertDontSee(
            [
                $attendance->clock_in_formatted,
                $attendance->clock_out_formatted,
                $attendance->total_work_formatted,
            ]
        );
    }
}
