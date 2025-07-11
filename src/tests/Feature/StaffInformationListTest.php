<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class StaffInformationListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /* 職員の勤怠情報を作成 */
    private function createAttendanceData(User $user)
    {
        return $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => '08:00',
            'clock_out' => '18:15',
        ]);
    }

    /* 管理者は、スタッフ一覧画面にて全職員の氏名とメールアドレスを確認できる */
    public function test_admin_can_check_all_staffs_information()
    {
        //職員を3人作成
        $staffMembers = User::factory()->count(3)->create();

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //スタッフ一覧ページにアクセスし、全職員の氏名とメールアドレスが表示されていることを確認
        $response = $this->get('/admin/staff/list');
        $response->assertViewIs('admin.staff.list');
        $response->assertViewHas('staffLists', function ($records) use ($staffMembers) {
            foreach ($staffMembers as $i => $staff) {
                if (
                    $records[$i]->name !== $staff->name ||
                    $records[$i]->email !== $staff->email
                ) {
                    return false;
                }
            }
            return true;
        });
        $response->assertViewHas('staffLists', fn($records) => count($staffMembers) === 3);
        $response->assertSee('スタッフ一覧');
        foreach ($staffMembers as $staff) {
            $response->assertSee($staff->name);
            $response->assertSee($staff->email);
        }
    }

    /* 選択した職員の「スタッフ別勤怠一覧画面」が表示される */
    public function test_admin_can_check_attendance_list_of_selected_staff()
    {
        //勤怠情報のある職員を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //スタッフ別勤怠一覧画面にアクセスし、登録された勤怠情報が表示されていることを確認
        $response = $this->get('/admin/staff/list');
        $response = $this->get(route('admin.attendances.list-by-staff', ['id' => $staff->id]));
        $response->assertViewIs('admin.attendance.list_by_staff');
        $response->assertViewHas('staff', function ($records) use ($staff) {
            return $records->name === $staff->name
                && $records->clock_in === $staff->clock_in
                && $records->clock_out === $staff->clock_out
                && $records->total_work_formatted === $staff->total_work_formatted;
        });
        $response->assertSeeInOrder([
            $staff->name . 'さんの勤怠',
            now()->isoFormat('M月D日（ddd）'),
            $attendance->clock_in_formatted,
            $attendance->clock_out_formatted,
            $attendance->total_work_formatted,
        ]);
    }

    /*「前月」を押下したときに前月の情報が表示される */
    public function test_show_previous_month_when_click_previous_month_link_at_attendance_list_for_admin()
    {
        //前月に勤怠情報のある職員を作成
        $staff = User::factory()->create();
        $previousMonth = Carbon::now()->subMonthNoOverflow(1)->toDateString();
        $attendance = $staff->attendances()->create([
            'user_id' => $staff->id,
            'date' => $previousMonth,
            'clock_in' => '08:00',
            'clock_out' => '18:00',
        ]);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //スタッフ別勤怠一覧画面にアクセスし、前月の日付と前月に登録された勤怠情報が表示されていることを確認
        $response = $this->get('/admin/staff/list');
        $response = $this->get(route('admin.attendances.list-by-staff', ['id' => $staff->id, 'date' => $previousMonth]));
        $response->assertSeeInOrder([
            $staff->name . 'さんの勤怠',
            Carbon::parse($previousMonth)->isoFormat('M月D日（ddd）'),
            $attendance->clock_in_formatted,
            $attendance->clock_out_formatted,
            $attendance->total_work_formatted,
        ]);
    }

    /*「翌月」を押下したときに、翌月の情報が表示される */
    public function test_show_next_month_when_click_next_month_link_at_attendance_list_for_admin()
    {
        //勤怠情報のある職員を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);
        $nextMonth = Carbon::now()->addMonthNoOverflow(1)->toDateString();

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //スタッフ別勤怠一覧画面にアクセスし、勤怠データが表示されていないことと、翌月のの日付が表示されていることを確認
        $response = $this->get('/admin/staff/list');
        $response = $this->get(route('admin.attendances.list-by-staff', ['id' => $staff->id, 'date' => $nextMonth]));
        $response->assertSeeInOrder([
            $staff->name . 'さんの勤怠',
            Carbon::parse($nextMonth)->isoFormat('M月D日（ddd）'),
        ]);
        $response->assertDontSee([
            $attendance->clock_in_formatted,
            $attendance->clock_out_formatted,
            $attendance->total_work_formatted,
        ]);
    }

    /* スタッフ別勤怠一覧画面から「詳細」を押下すると、該当の勤怠詳細画面に遷移する */
    public function test_admin_can_transition_detail_page_when_click_detail_link_at_attendance_list()
    {
        //勤怠情報のある職員を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //スタッフ別勤怠一覧画面から詳細画面へ遷移し、勤怠情報が表示されていることを確認
        $response = $this->get('/admin/staff/list');
        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertViewIs('admin.attendance.detail');
        $response->assertSeeInOrder([
            $staff->name,
            $attendance->date->isoFormat('Y年'),
            $attendance->date->isoFormat('M月D日'),
            $attendance->clock_in_formatted,
            $attendance->clock_out_formatted,
        ]);
    }
}
