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
    ※一般職員（{{ Auth::id() }}：{{ Auth::user()->name }}）でログイン中 {{-- あとで消す --}}
    <div class="list__guide-area">
        <div class="list__guide-link"><a href="{{ url('/attendance/list?date=' . $previousMonth) }}">←前月</a></div>
        select:{{ $selectDate }}　　
        previous:{{ $previousMonth }}　　
        当月：{{ $selectDate->isoFormat('Y/MM') }}
        <div class="list__guide-link"><a href="{{ url('/attendance/list?date=' . $nextMonth) }}">翌月→</a></div>
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
            @foreach( $attendanceRecords as $attendance )
            <tr class="attendance-table__row">
                <td class="attendance-table__data">{{ $attendance['date'] ?? '' }}</td>
                <td class="attendance-table__data">{{ $attendance['clock_in'] ?? '' }}</td>
                <td class="attendance-table__data">{{ $attendance['clock_out'] ?? ''}}</td>
                <td class="attendance-table__data">{{ $attendance['total_rest_formatted'] ?? '' }}</td>
                <td class="attendance-table__data">{{ $attendance['total_work_formatted']  ?? '' }}</td>
                @if( !$attendance['id'])
                <td class="attendance-table__data"><a href="/attendance/{{ $attendance['id'] }}"><span class="table__data--none">詳細</span></a></td>
                @else
                <td class="attendance-table__data"><a href="/attendance/{{ $attendance['id'] }}">詳細</a></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection