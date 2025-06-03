@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endsection

@section('content')
<div class="login__content">
    <div class="section__title">
        <h2>ログイン</h2>
    </div>
    <form action="/login" class="login-form" method="post">
        @csrf
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
            <button class="login-form__button-submit" type="submit">ログインする</button>
        </div>
    </form>
    <div class="guide-link"><a href="/register">会員登録はこちら</a></div>

</div>
@endsection