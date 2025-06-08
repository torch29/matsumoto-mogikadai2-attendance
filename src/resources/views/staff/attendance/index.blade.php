@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="main__content-wrapper">
    <div class="main__content">
        <div class="status-label">勤務外</div>
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
            <form action="attendance/stamp" method="post">
                @csrf
                <div class="attendance-form__button">
                    <button class="attendance-form__button-submit">出勤</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection