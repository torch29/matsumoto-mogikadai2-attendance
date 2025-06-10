<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('ログイン情報が正しくありません。'),
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

        // 管理者が管理者ログイン画面からログインするときの遷移先
        if ($request->is('admin/login') && $user->is_admin) {
            return redirect('/admin/attendance/list');
        }

        // 一般職員＆管理者が一般ログイン画面からログインするときの遷移先
        return redirect()->intended('/attendance');
    }
}
