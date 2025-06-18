@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="main__content-wrapper">
    {{-- 確認用　あとで消す　後で--}}
    <div class="check">ログイン中のID：<span>{{ Auth::id() }}</span>
    </div>
    <div class="check"><span>{{ Auth::user()->name }}</span>　さん</div>
    <div class="check">is_admin:<span>{{ $user->is_admin }}</span> （※1=true=admin, 0=false=従業員）</div>
    {{-- 確認用ここまで　あとで消す　後で --}}
    <div class="main__content">
        @if (session('error'))
        <div class="item__alert">
            <input type="checkbox" id="alert-close" class="alert-close">
            <div class="alert-message">
                <label for="alert-close" class="alert-close__button">×</label>
                {{ session('error') }}
            </div>
        </div>
        @endif
        @if( $status === '勤務外' )
        <div class="status-label">勤務外</div>
        @elseif ( $status === '出勤中' )
        <div class="status-label">出勤中</div>
        @elseif ( $status === '休憩中' )
        <div class="status-label">休憩中</div>
        @elseif ( $status === '退勤済' )
        <div class="status-label">退勤済</div>
        @endif
        <div class="main__content-date">
            {{ \Carbon\Carbon::now()->isoFormat('Y年 M月D日（ddd）') }}
        </div>
        <div class="main__content-datetime" id="clock">
            <script src="{{ asset('js/display_time.js') }}"></script>
        </div>

        <div class="attendance-form">
            <div class="attendance-form__button">
                @if( $status === '勤務外' )
                <form action="attendance/clockIn" method="post">
                    @csrf
                    <button class="attendance-form__button-submit">出勤</button>
                </form>
                @elseif ( $status === '出勤中' )
                <form action="attendance/clockOut" method="post">
                    @csrf
                    <button class="attendance-form__button-submit--left">退勤</button>
                </form>
                <form action="attendance/restStart" method="post">
                    @csrf
                    <button class="attendance-form__button-submit--right" onclick="this.disabled = true; this.form.submit();">休憩入</button>
                </form>
                @elseif ( $status === '休憩中' )
                <form action="attendance/restEnd" method="post">
                    @csrf
                    <button class="attendance-form__button-submit--return">休憩戻</button>
                </form>
                @elseif ( $status === '退勤済' )
                <p>お疲れさまでした。</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection