<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceApproveTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /* 下準備  */
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

    //職員の修正申請データを作成
    private function createAttendanceCorrectionData(Attendance $attendance)
    {
        $correction = $attendance->attendanceCorrections()->create([
            'attendance_id' => $attendance->id,
            'corrected_clock_in' => '08:15',
            'corrected_clock_out' => '18:00',
            'note' => '申請のテスト',
        ]);

        $correction->restCorrections()->create([
            'corrected_rest_start' => '11:30',
            'corrected_rest_end' => '12:30',
        ]);

        return $correction;
    }

    /*  テストここから  */
    //管理者用 修正申請一覧の承認待ち欄に、承認待ちのデータが表示されている
    public function test_admin_can_check_all_list_of_pending_correction_requests()
    {
        //職員と修正申請データをそれぞれ3人分作成
        $staffMembers = User::factory()->count(3)->create();
        $corrections = [];
        foreach ($staffMembers as $staff) {
            $attendance = $this->createAttendanceData($staff);
            $attendanceCorrection = $this->createAttendanceCorrectionData($attendance);
            $corrections[] = ['staff' => $staff, 'correction' => $attendanceCorrection];
        }

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //修正申請一覧ページにアクセスし、職員の名前と申請理由が[承認待ち]欄に表示されていることを確認
        $response = $this->get('/admin/stamp_correction_request/list');
        $response->assertViewIs('admin.request.list');
        foreach ($corrections as $correction) {
            $response->assertSee($correction['staff']->name);
            $response->assertSee($correction['correction']->note);
        }
        $response->assertViewHas('stampCorrectionRecords', fn($records) => count($records) === 3);
        $response->assertSeeInOrder([
            '状態',
            '承認待ち',
            $corrections[0]['staff']->name,
            $corrections[1]['staff']->name,
        ]);
    }

    //管理者用 修正申請一覧の承認済み欄に、承認済みの修正申請が表示されている
    public function test_admin_can_check_all_list_of_approved_correction_requests()
    {
        //職員と修正申請データをそれぞれ3人分作成
        $staffMembers = User::factory()->count(3)->create();
        $corrections = [];
        foreach ($staffMembers as $staff) {
            $attendance = $this->createAttendanceData($staff);
            $attendanceCorrection = $this->createAttendanceCorrectionData($attendance);
            $attendanceCorrection->update(['approve_status' => 'approved']);
            $corrections[] = ['staff' => $staff, 'correction' => $attendanceCorrection];
        }

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //修正申請一覧ページの承認済みタブにアクセスし、職員の名前と申請理由が[承認済み]欄にあることを確認
        $response = $this->get('/admin/stamp_correction_request/list?tab=approved');
        $response->assertViewIs('admin.request.list');
        foreach ($corrections as $correction) {
            $response->assertSee($correction['staff']->name);
            $response->assertSee($correction['correction']->note);
        }
        $response->assertViewHas('stampCorrectionRecords', fn($records) => count($records) === 3);
        $response->assertSeeInOrder([
            '状態',
            '承認済み',
            $corrections[0]['staff']->name,
            $corrections[1]['staff']->name,
        ]);
    }

    //管理者用 修正申請承認画面において修正申請の内容が表示されている
    public function test_show_correction_request_data_in_detail_page_for_admin()
    {
        //職員と修正申請データを作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);
        $attendanceCorrection = $this->createAttendanceCorrectionData($attendance);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //修正申請承認画面にアクセスし、申請されたデータが表示されていることを確認
        $response = $this->get('/admin/stamp_correction_request/approve/' . $attendanceCorrection->id);
        $response->assertViewIs('admin.request.approve');
        $response->assertViewHas('attendanceCorrection', function ($correction) use ($attendanceCorrection) {
            return $correction->id === $attendanceCorrection->id
                && $correction->corrected_clock_in == $attendanceCorrection->corrected_clock_in;
        });
        $response->assertSeeInOrder([
            $staff->name,
            $attendanceCorrection->corrected_clock_in->isoFormat('H:mm'),
            $attendanceCorrection->corrected_clock_out->isoFormat('H:mm'),
            $attendanceCorrection->restCorrections->first()->corrected_rest_start->isoFormat('H:mm'),
            $attendanceCorrection->restCorrections->first()->corrected_rest_end->isoFormat('H:mm'),
            '承認',
        ]);
    }
}
