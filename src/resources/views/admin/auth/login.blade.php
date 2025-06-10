@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="login__content">
    <div class="section__title">
        <h2>管理者ログイン</h2>
    </div>
    <form action="/admin/login" class="login-form" method="post">
        @csrf
        <input type="hidden" name="is_admin_login" value="1">
        <label for="email" class="login-form__item-label">メールアドレス</label>
        <input type="email" name="email" id="email" class="login-form__item-input" value="{{ old('email') }}">
        <div class="form__error">
            @error('email')
            {{ $message }}
            @enderror
        </div>
        <label for="password" class="login-form__item-label">パスワード</label>
        <input type="password" name="password" id="password" class="login-form__item-input">
        <div class="form__error">
            @error('password')
            {{ $message }}
            @enderror
        </div>
        <div class="login-form__button">
            <button class="login-form__button-submit" type="submit">管理者ログインする</button>
        </div>
    </form>

</div>
@endsection