<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

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

    //職員が修正したいデータを送信して申請する際の、デフォルトデータの設定
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
}
