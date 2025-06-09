@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="main__content-wrapper">
    <div class="main__content">
        @if( !$today_attendance )
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
        <script>
            function nowTime() {
                const timeSetting = {
                    timeZone: 'Asia/Tokyo',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                }
                const timeString = new Date().toLocaleTimeString('ja-JP', timeSetting);

                document.getElementById("clock").textContent = timeString;
            }

            nowTime(); // 初回表示
            setInterval(nowTime, 1000); // 毎秒更新
        </script>

        <div class="attendance-form">
            <div class="attendance-form__button">
                @if( !$today_attendance )
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