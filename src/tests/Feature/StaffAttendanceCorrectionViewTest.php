<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class StaffAttendanceCorrectionViewTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /* 勤怠情報を作成 */
    private function createAttendanceData(User $user)
    {
        return $user->attendances()->create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => '08:00',
            'clock_out' => '18:15',
        ]);
    }

    /* 職員が修正したいデータを送信して申請する際の、デフォルトデータの設定 */
    private function postCorrectionRequest(array $overrides = [])
    {
        $defaultData = [
            'corrected_clock_in' => '08:15',
            'corrected_clock_out' => '18:00',
            'rest_corrections' => [
                'new' => [
                    'corrected_rest_start' => '11:30',
                    'corrected_rest_end' => '12:30',
                ],
            ],
            'note' => '申請のテスト',
        ];

        $requestData = array_replace_recursive($defaultData, $overrides);
        return $this->post('correction_request', $requestData);
    }


    /* ここからテスト */
    /* 修正申請処理が実行され、管理者が申請一覧画面と承認画面からデータを確認できる */
    public function test_staff_can_correction_request()
    {
        //勤怠情報があるユーザーにログイン
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);
        $this->actingAs($staff);

        //修正申請する。データベースに保存されていることを確認。
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
        ]);
        $correction = $attendance->attendanceCorrections()->first();
        $this->assertDatabaseHas('attendance_corrections', [
            'corrected_clock_in' => $correction->corrected_clock_in->format('H:i:s'),
            'corrected_clock_out' => $correction->corrected_clock_out->format('H:i:s'),
        ]);

        //管理者ユーザーに切り替え
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);
        $this->actingAs($admin);
        //管理者が申請一覧画面にアクセスし、修正申請されたデータが表示されていることを確認
        $response = $this->get('/admin/stamp_correction_request/list');
        $response->assertViewIs('admin.request.list');
        $response->assertViewHas('attendanceCorrections', function ($records) use ($staff, $correction) {
            return $records[0]->attendance->user->name === $staff->name
                && $records[0]->note === $correction->note;
        });
        $response->assertSeeInOrder([
            '申請一覧',
            '承認待ち',
            $staff->name,
            $attendance->date->format('Y/m/d'),
            $correction->note,
            $attendance->created_at->format('Y/m/d'),
        ]);

        //管理者が承認画面にアクセスし、修正申請されたデータが表示されていることを確認
        $response = $this->get('/admin/stamp_correction_request/approve/' . $correction->id);
        $response->assertViewIs('admin.request.approve');
        $response->assertViewHas('attendanceCorrection', function ($record) use ($correction, $staff) {
            return $record->attendance->user->name === $staff->name
                && $record->corrected_clock_in->format('H:i:s') === $correction->corrected_clock_in->format('H:i:s')
                && $record->restCorrections->first()->corrected_rest_start->format('H:i:s') === $correction->restCorrections->first()->corrected_rest_start->format('H:i:s');
        });
        $response->assertSeeInOrder([
            $staff->name,
            $attendance->date->isoFormat('Y年'),
            $attendance->date->isoFormat('M月D日'),
            $correction->corrected_clock_in->isoFormat('H:mm'),
            $correction->corrected_clock_out->isoFormat('H:mm'),
            $correction->note,
            '承認',
        ]);
    }

    /* ユーザーが行った申請が申請一覧の「承認待ち」に表示されている */
    public function test_reflects_pending_list_when_staff_requested_correction()
    {
        //勤怠情報があるユーザーにログイン
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);
        $this->actingAs($staff);

        //修正申請する
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
        ]);
        $correction = $attendance->attendanceCorrections()->first();

        //自分の申請一覧画面にアクセスし、承認待ち欄に申請したデータがあることを確認
        $response = $this->get('/stamp_correction_request/list');
        $response->assertViewIs('staff.request.list');
        $response->assertSeeInOrder([
            '状態',
            '承認待ち',
            $staff->name,
            $attendance->date->format('Y/m/d'),
            $correction->note,
            $attendance->created_at->format('Y/m/d'),
        ]);
    }

    /* 管理者が承認した修正申請は「承認済み」欄に表示されている */
    public function test_reflects_approved_list_that_admin_approved_correction()
    {
        //勤怠情報があるユーザーにログイン
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);
        $this->actingAs($staff);

        //修正申請後、承認ステータスが「承認済み」となる
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
        ]);
        $correction = $attendance->attendanceCorrections()->first();
        $correction->update(['approve_status' => 'approved']);

        //自分の申請一覧画面にアクセスし、承認済み欄に申請したデータがあることを確認
        $response = $this->get('/stamp_correction_request/list?tab=approved');
        $response->assertViewIs('staff.request.list');
        $response->assertSeeInOrder([
            '状態',
            '承認済み',
            $staff->name,
            $attendance->date->format('Y/m/d'),
            $correction->note,
            $attendance->created_at->format('Y/m/d'),
        ]);
    }

    /* 職員が申請一覧画面から「詳細」を押すと詳細画面に遷移する */
    public function test_can_transition_to_correction_detail_page()
    {
        //勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $this->createAttendanceData($user);
        $this->actingAs($user);

        //修正申請する
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
        ]);
        $correction = $attendance->attendanceCorrections()->first();

        //自分の申請一覧画面から詳細画面に遷移。申請したデータが表示されていることを確認
        $response = $this->get('/stamp_correction_request/list');
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertViewIs('staff.attendance.detail');
        $response->assertSeeInOrder([
            $user->name,
            $correction->corrected_clock_in->isoFormat('H:mm'),
            $correction->corrected_clock_out->isoFormat('H:mm'),
            $correction->restCorrections->first()->corrected_rest_start->isoFormat('H:mm'),
            $correction->restCorrections->first()->corrected_rest_end->isoFormat('H:mm'),
            '*承認待ちのため修正はできません。',
        ]);
    }
}
