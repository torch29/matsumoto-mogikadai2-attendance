<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;

class EmailVerifyTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    //ユーザーが会員登録した際、認証用のメールが送信される
    public function test_user_receives_verification_email_after_register()
    {
        Notification::fake();

        //会員登録画面から登録する
        $response = $this->get('/register');
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'dummy@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'dummy@example.com')->first();
        //登録されたメールアドレスを持つユーザーに送信されていることを確認
        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    public function test_has_a_URL_for_email_verification()
    {
        //メール認証されていないユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $this->actingAs($user);

        $response = $this->get('/email/verify');
        $response->assertSee('メール認証を完了してください。');
        $response->assertSee('認証はこちらから');

        $response->assertSee('http://localhost:8025');
    }

    //認証用メールのURLをクリックしてメール認証を完了すると、勤怠打刻画面に遷移する
    public function test_user_is_authenticated_by_click_that_verification_email()
    {
        //メール認証されていないユーザーを作成
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        //認証メールに表示されるURLの設定
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        //まだメール認証されていないことを確認
        $this->assertFalse($user->hasVerifiedEmail());
        $this->actingAs($user);

        //認証メールのURLをクリックすると、メール認証され、勤怠打刻画面に遷移することを確認
        $response = $this->get($url);
        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $response->assertRedirect('/attendance');
    }
}
