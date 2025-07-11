<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class AdminAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    /* 職員の勤怠情報を作成 */
    private function createAttendanceData(User $staff)
    {
        return $staff->attendances()->create([
            'user_id' => $staff->id,
            'date' => Carbon::now()->toDateString(),
            'clock_in' => '08:00',
            'clock_out' => '18:15',
        ]);
    }

    /* 管理者が修正したいデータを送信する際のデフォルトデータの設定 */
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

    /* 勤怠詳細画面に表示されるデータが選択したものになっている */
    public function test_show_selected_attendance_data_at_detail_page()
    {
        //職員の勤怠情報を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //勤怠一覧画面から詳細画面へ遷移し、選択した勤怠データの詳細が表示されていることを確認
        $response = $this->get('/admin/attendance/list');
        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response->assertViewIs('admin.attendance.detail');
        $response->assertViewHas('attendance', function ($records) use ($attendance) {
            return $records->user_id === $attendance->user_id
                && $records->date->format('Y/m/d') === $attendance->date->format('Y/m/d')
                && $records->clock_in_formatted === $attendance->clock_in_formatted
                && $records->clock_out_formatted === $attendance->clock_out_formatted;
        });
        $response->assertSeeInOrder([
            $staff->name,
            $attendance->date->isoFormat('Y年'),
            $attendance->date->isoFormat('M月D日'),
            $attendance->clock_in_formatted,
            $attendance->clock_out_formatted,
        ]);
    }

    /* 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される */
    public function test_show_error_message_when_clock_in_time_is_after_clock_out_time_at_attendance_detail_page_for_admin()
    {
        //職員の勤怠情報を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //勤怠詳細画面にアクセスし、出勤時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'corrected_clock_in' => '18:01' //出勤時間を退勤時間より後に設定
        ]);
        $response->assertSessionHasErrors(['corrected_clock_out' => '出勤時間もしくは退勤時間が不適切な値です',]);
    }

    /* 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される：既存の休憩を上書きして修正 */
    public function test_show_error_message_when_rest_start_overwrite_time_is_after_clock_out_time_at_attendance_detail_page_for_admin()
    {
        //職員の勤怠情報を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);
        $attendance->rests()->create([
            'rest_start' => '11:30',
            'rest_end' => '12:20',
        ]);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //管理者用勤怠詳細画面にアクセスし、休憩開始時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
            'rest_corrections' => [
                '0' => [
                    'corrected_rest_start' => '18:01',
                ], //既存の休憩開始時間を退勤時間より後の時刻に上書き
            ],
        ]);
        $response->assertSessionHasErrors(['rest_corrections.0.corrected_rest_start' => '休憩時間が不適切な値です',]);
    }

    /* 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される：休憩を新規入力して修正 */
    public function test_show_error_message_when_new_rest_start_time_is_after_clock_out_time_at_attendance_detail_page_for_admin()
    {
        //職員の勤怠情報を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //管理者用勤怠詳細画面にアクセスし、休憩開始時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
            'rest_corrections' => [
                'new' => [
                    'corrected_rest_start' => '18:01',
                ], //休憩開始時間を退勤時間より後に設定したものを新規入力
            ],
        ]);
        $response->assertSessionHasErrors(['rest_corrections.new.corrected_rest_start' => '休憩時間が不適切な値です',]);
    }

    /* 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される */
    public function test_show_error_message_when_rest_end_time_is_after_clock_out_time_at_attendance_detail_page_for_admin()
    {
        //職員の勤怠情報を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //管理者用勤怠詳細画面にアクセスし、休憩終了時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
            'rest_corrections' => [
                'new' => [
                    'corrected_rest_start' => '13:00',
                    'corrected_rest_end' => '18:01'
                ], //休憩終了時間を退勤時間より後に設定
            ],
        ]);
        $response->assertSessionHasErrors(['rest_corrections.new.corrected_rest_end' => '休憩時間もしくは退勤時間が不適切な値です',]);
    }

    /* 備考が未入力の場合、エラーメッセージが表示される */
    public function test_show_error_message_when_correction_without_note_at_attendance_detail_page_for_admin()
    {
        //職員の勤怠情報を作成
        $staff = User::factory()->create();
        $attendance = $this->createAttendanceData($staff);

        //管理者としてログイン
        $admin = User::factory()->create(['is_admin' => 1]);
        $this->actingAs($admin);

        //管理者用勤怠詳細画面にアクセスし、休憩終了時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/admin/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'note' => '',
        ]);
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
