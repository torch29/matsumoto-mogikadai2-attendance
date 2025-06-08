@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    {{-- 従業員でログイン時。自分のみ --}}
    <div class="list__title">
        <h3>勤怠一覧</h3>
    </div>
    ※一般職員でログイン中 {{-- あとで消す --}}
    <div class="list__guide-area">
        <div class="list__guide-link">←前月</div>
        当月
        <div class="list__guide-link">翌月→</div>
    </div>
    <div class="attendance-table__wrapper">
        <table class="attendance-table">
            <tr class="attendance-table__row">
                <th class="attendance-table__heading">日付</th>
                <th class="attendance-table__heading">出勤</th>
                <th class="attendance-table__heading">退勤</th>
                <th class="attendance-table__heading">休憩</th>
                <th class="attendance-table__heading">合計</th>
                <th class=" attendance-table__heading">詳細</th>
            </tr>
            <tr class="attendance-table__row">
                @foreach( $attendances as $attendance )
                <td class="attendance-table__data">{{ $attendance->date }}</td>
                <td class="attendance-table__data">{{ $attendance->clock_in }}</td>
                <td class="attendance-table__data">{{ $attendance->clock_out }}</td>
                <td class="attendance-table__data">1:00</td>
                <td class="attendance-table__data">8:00</td>
                <td class="attendance-table__data">詳細</td>
                @endforeach
            </tr>
        </table>
    </div>
</div>
@endsection