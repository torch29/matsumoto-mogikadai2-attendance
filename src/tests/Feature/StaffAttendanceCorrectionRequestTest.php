<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;


class StaffAttendanceCorrectionRequestTest extends TestCase
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
    /* 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される */
    public function test_show_error_message_when_clock_in_time_is_after_clock_out_time_at_attendance_detail_page()
    {
        //勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $this->createAttendanceData($user);
        $this->actingAs($user);

        //勤怠詳細画面にアクセスし、出勤時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/attendance/' . $attendance->id);
        $response->assertViewIs('staff.attendance.detail');
        $response = $this->postCorrectionRequest([
            'corrected_clock_in' => Carbon::parse('18:01')->format('H:i'), //出勤時間を退勤時間より後に設定
        ]);
        $response->assertSessionHasErrors([
            'corrected_clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /* 休憩開始時間が退勤時間より後の時刻になっている場合、エラーメッセージが表示される：既存の休憩を上書き修正して申請 */
    public function test_show_error_message_when_rest_start_overwrite_time_is_after_clock_out_time_at_attendance_detail_page()
    {
        //勤怠情報（休憩つき）があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $this->createAttendanceData($user);
        $attendance->rests()->create([
            'rest_start' => Carbon::parse('11:30'),
            'rest_end' => Carbon::parse('12:20'),
        ]);
        $this->actingAs($user);

        //勤怠詳細画面にアクセスし、休憩開始時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
            'rest_corrections' => [
                '0' => [
                    'corrected_rest_start' => '18:01',
                ], //既存の休憩開始時間を退勤時間より後の時刻に上書き
            ],
        ]);
        $response->assertSessionHasErrors([
            'rest_corrections.0.corrected_rest_start' => '休憩時間が不適切な値です',
        ]);
    }

    /* 休憩開始時間が退勤時間より後の時刻になっている場合、エラーメッセージが表示される：休憩を新規入力して申請 */
    public function test_show_error_message_when_new_rest_start_time_is_after_clock_out_time_at_attendance_detail_page()
    {
        //勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $this->createAttendanceData($user);
        $this->actingAs($user);

        //勤怠詳細画面にアクセスし、休憩開始時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
            'rest_corrections' => [
                'new' => [
                    'corrected_rest_start' => '18:01',
                ], //休憩開始時間を退勤時間より後に設定したものを新規入力
            ],
        ]);
        $response->assertSessionHasErrors([
            'rest_corrections.new.corrected_rest_start' => '休憩時間が不適切な値です',
        ]);
    }

    /* 休憩終了時間が退勤時間より後の時刻になっている場合、エラーメッセージが表示される */
    public function test_show_error_message_when_rest_end_time_is_after_clock_out_time_at_attendance_detail_page()
    {
        //勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $this->createAttendanceData($user);
        $this->actingAs($user);

        //勤怠詳細画面にアクセスし、休憩終了時間を退勤時間より後の時刻に設定して修正申請すると、指定のバリデーションメッセージが表示される
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'attendance_id' => $attendance->id,
            'rest_corrections' => [
                'new' => [
                    'corrected_rest_start' => '13:00',
                    'corrected_rest_end' => '18:01'
                ], //休憩終了時間を退勤時間より後に設定
            ],
        ]);
        $response->assertSessionHasErrors([
            'rest_corrections.new.corrected_rest_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /* 備考欄が未入力の場合エラーメッセージが表示される */
    public function test_show_error_message_when_correction_request_without_note_at_attendance_detail_page()
    {
        //勤怠情報があるユーザーにログイン
        $user = User::factory()->create();
        $attendance = $this->createAttendanceData($user);
        $this->actingAs($user);

        //勤怠詳細画面にアクセスし、備考欄を未入力のまま送信すると指定のバリデーションメッセージが表示される
        $response = $this->get('/attendance/' . $attendance->id);
        $response = $this->postCorrectionRequest([
            'note' => '',
        ]);
        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
