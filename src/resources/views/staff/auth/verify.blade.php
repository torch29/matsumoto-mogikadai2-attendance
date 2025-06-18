@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endsection

@section('content')
<div class="verify__content">
    <div class="verify__content-inner">
        <div class="verify__content--info">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>
            <div class="verify-link__button">
                <a href="http://localhost:8025/" class="verify-link__button-submit">認証はこちらから</a>
            </div>
            <form class="verify__form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <div class="verify-form__button">
                    <button class="verify-form__button-submit" type="submit">認証メールを再送する</button>
                </div>
            </form>
            @if (session('message'))
            <div class="verify__alert-success">
                {{ session('message') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection