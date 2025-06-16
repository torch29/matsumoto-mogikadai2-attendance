@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="list__title">
        <h3>{{ $staff->name }}さんの勤怠</h3>
    </div>
    <div class="list__guide-area">
        <div class="list__guide-link">←前月</div>
        {{ $selectDate->isoFormat('Y/MM') }}
        <div class="list__guide-link">翌月→</div>
    </div>
    {{-- dd($attendanceRecords) --}}
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
                <th class="attendance-table__data"><span class="attendance-table__data--date">{{ $attendance['date'] }}</span></th>
                <td>{{ $attendance['clock_in'] }}</td>
                <td class="attendance-table__data">{{ $attendance['clock_out'] }}</td>
                <td class="attendance-table__data">{{ $attendance['total_rest_formatted'] }}</td>
                <td class="attendance-table__data">{{ $attendance['total_work_formatted'] }}</td>
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