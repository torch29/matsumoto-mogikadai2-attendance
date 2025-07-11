<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserAuthTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function test_show_message_user_register_without_name()
    {
        $response = $this->get('/register');
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'dummy@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_user_register_without_email()
    {
        $response = $this->get('/register');
        $response = $this->post('/register', [
            'name' => 'Dummy User',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_user_register_with_8characters_less_password()
    {
        $response = $this->get('/register');
        $response = $this->post('/register', [
            'name' => 'Dummy User',
            'email' => 'dummy@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_user_register_with_mismatch_password()
    {
        $response = $this->get('/register');
        $response = $this->post('/register', [
            'name' => 'Dummy User',
            'email' => 'dummy@example.com',
            'password' => 'password',
            'password_confirmation' => 'pass1234',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_user_register_without_password()
    {
        $response = $this->get('/register');
        $response = $this->post('/register', [
            'name' => 'Dummy User',
            'email' => 'dummy@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_user_can_register_success()
    {
        $response = $this->get('/register');
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'dummy@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'dummy@example.com',
            'is_admin' => 0,
        ]);
    }

    public function test_show_message_user_login_without_email()
    {
        $user = User::factory()->create();
        $this->assertGuest();

        $response = $this->get('/login');
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_user_login_without_password()
    {
        $user = User::factory()->create();
        $this->assertGuest();

        $response = $this->get('/login');
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => '',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
        $this->assertGuest();
    }

    public function test_show_message_user_login_with_wrong_data()
    {
        $user = User::factory()->create();
        $this->assertGuest();

        $response = $this->get('/login');
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);
        $response->assertSessionHasErrors([
            'email' => trans('auth.failed')
        ]);
        $this->assertGuest();
    }

    public function test_user_can_login_successfully()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password')
        ]);
        $this->assertGuest();

        $response = $this->get('/login');
        $response->assertViewIs('staff.auth.login');
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/attendance');
    }
}
