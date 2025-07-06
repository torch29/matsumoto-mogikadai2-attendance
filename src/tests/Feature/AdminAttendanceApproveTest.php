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
        return $attendance->attendanceCorrections()->create([
            'corrected_clock_in' => '08:15',
            'corrected_clock_out' => '18:00',
            'rest_corrections' => [
                'new' => [
                    'corrected_rest_start' => '11:30',
                    'corrected_rest_end' => '12:30',
                ],
            ],
            'note' => '申請のテスト',
        ]);
    }

    //管理者用 修正申請一覧の承認待ち欄に、承認待ちのデータが表示されている
    public function test_admin_can_check_all_staffs_information()
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
        $response->assertSeeInOrder([
            '状態',
            '承認待ち',
            $corrections[0]['staff']->name,
            $corrections[1]['staff']->name,
        ]);
    }
}
