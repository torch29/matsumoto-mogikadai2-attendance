<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

    //職員の勤怠情報を作成
    private function createAttendanceData(User $user)
    {
        return $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => '08:00',
            'clock_out' => '18:15',
        ]);
    }

    //管理者は、スタッフ一覧画面にて全職員の氏名とメールアドレスを確認できる
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
        $response->assertSee('スタッフ一覧');
        foreach ($staffMembers as $staff) {
            $response->assertSee($staff->name);
            $response->assertSee($staff->email);
        }
    }

    //選択した職員の「スタッフ別勤怠一覧画面」が表示される
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
        $response->assertSeeInOrder([
            $staff->name . 'さんの勤怠',
            now()->isoFormat('M月D日（ddd）'),
            $attendance->clock_in->format('H:i'),
            $attendance->clock_out->format('H:i'),
            $attendance->total_work_formatted,
        ]);
    }
}
