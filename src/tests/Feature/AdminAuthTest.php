<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminAuthTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_show_message_admin_login_without_email()
    {
        $user = User::factory()->create([
            'is_admin' => 1,
        ]);
        $response = $this->get('/admin/login');
        $this->assertGuest();
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_admin_login_without_password()
    {
        $user = User::factory()->create([
            'is_admin' => 1,
        ]);

        $response = $this->get('/admin/login');
        $this->assertGuest();
        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => '',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_admin_login_with_wrong_data()
    {
        $user = User::factory()->create([
            'is_admin' => 1,
        ]);

        $response = $this->get('/admin/login');
        $this->assertGuest();
        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors([
            'email' => trans('auth.failed')
        ]);
        $this->assertGuest();
    }

    public function test_admin_can_login_successfully()
    {
        $admin = User::factory()->create([
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);
        $response = $this->get('/admin/login');
        $response->assertViewIs('admin.auth.login');

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'password'
        ]);

        $this->assertAuthenticatedAs($admin);
    }

    //管理者は一般職員用ログインページからはログインできない
    public function test_admin_can_not_login_from_user_login_page()
    {
        $admin = User::factory()->create([
            'password' => bcrypt('password'),
            'is_admin' => 1,
        ]);
        $response = $this->get('/login');
        $response->assertViewIs('staff.auth.login');

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors([
            'email' => 'こちらは一般職員のログイン画面です。管理者画面からログインしてください。'
        ]);
        $this->assertGuest();
    }

    //一般職員は管理者用ログインページにログインできない
    public function test_user_can_not_login_from_admin_login_page()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);
        $response = $this->get('/admin/login');
        $response->assertViewIs('admin.auth.login');

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertSessionHasErrors([
            'email' => '管理者アカウントでログインしてください。'
        ]);
        $this->assertGuest();
    }
}
