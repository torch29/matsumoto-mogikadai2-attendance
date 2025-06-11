@extends('layouts.app')

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
        {{ $currentDay->isoFormat('Y/MM') }}
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
            @foreach( $attendanceRecords as $attendance )
            <tr class="attendance-table__row">
                <td class="attendance-table__data">{{ $attendance['date'] }}</td>
                <td>{{ $attendance['clock_in'] }}</td>
                <td class="attendance-table__data">{{ $attendance['clock_out'] }}</td>
                <td class="attendance-table__data">1:00</td>
                <td class="attendance-table__data">8:00</td>
                <td class="attendance-table__data">詳細</td>
            </tr>
            @endforeach
            </tr>
        </table>
    </div>
</div>
@endsection