<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('ログイン情報が登録されていません'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // 管理者ログイン画面からのログインの場合、is_adminをチェックする
        if ($request->is('admin/login') && !$user->is_admin) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('管理者アカウントでログインしてください。'),
            ]);
        }

        //管理者が一般画面からログインしようとしたら拒否する
        if ($request->is('login') && $user->is_admin) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => __('こちらは一般職員のログイン画面です。管理者画面からログインしてください。'),
            ]);
        }

        // 管理者が管理者ログイン画面からログインするときの遷移先
        if ($request->is('admin/login') && $user->is_admin) {
            return redirect('/admin/attendance/list');
        }

        // 一般職員＆管理者が一般ログイン画面からログインするときの遷移先
        return redirect()->intended('/attendance');
    }

    //ログアウトの処理
    public function destroy(Request $request)
    {
        $user = Auth::user();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 管理者のログアウト後の遷移先
        if ($user && $user->is_admin) {
            return redirect('/admin/login');
        }

        // 一般職員のログアウト後の遷移先
        return redirect('/login');
    }
}
