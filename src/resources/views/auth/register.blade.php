@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endsection

@section('content')
<div class="register__content">
    <div class="section__title">
        <h2>会員登録</h2>
    </div>
    <form action="/register" class="register-form" method="post">
        @csrf
        <label for="name" class="register-form__item-label">ユーザー名</label>
        <input type="text" name="name" id="name" class="register-form__item-input" value="{{ old('name') }}">
        <div class="form__error">
            @error('name')
            {{ $message }}
            @enderror
        </div>
        <label for="email" class="register-form__item-label">メールアドレス</label>
        <input type="email" name="email" id="email" class="register-form__item-input" value="{{ old('email') }}">
        <div class="form__error">
            @error('email')
            {{ $message }}
            @enderror
        </div>
        <label for="password" class="register-form__item-label">パスワード</label>
        <input type="password" name="password" id="password" class="register-form__item-input">
        <label for="password_confirmation" class="register-form__item-label">確認用パスワード</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="register-form__item-input">
        <div class="form__error">
            @error('password')
            {{ $message }}
            @enderror
        </div>
        <div class="register-form__button">
            <button class="register-form__button-submit" type="submit">登録する</button>
        </div>
    </form>

    <div class="guide-link"><a href="/login">ログインはこちら</a></div>
</div>
@endsection