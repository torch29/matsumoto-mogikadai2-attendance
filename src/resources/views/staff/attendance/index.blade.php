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
        @if( !$todayAttendance )
        <div class="status-label">勤務外</div>
        @else
        <div class="status-label">出勤中</div>
        <div class="status-label">休憩中</div>
        <div class="status-label">退勤済</div>
        @endif
        <div class="main__content-date">
            {{ \Carbon\Carbon::now()->isoFormat('Y年 M月D日（ddd）') }}
        </div>
        <div class="main__content-datetime" id="clock"></div>
        <script src="{{ asset('js/display_time.js') }}"></script>

        <div class="attendance-form">
            <div class="attendance-form__button">
                @if( !$todayAttendance )
                <form action="attendance/clockIn" method="post">
                    @csrf
                    <button class="attendance-form__button-submit">出勤</button>
                </form>
                @else
                <button class="attendance-form__button-submit--return">休憩戻</button>
                <button class="attendance-form__button-submit--left">退勤</button>
                <button class="attendance-form__button-submit--right">休憩入</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection