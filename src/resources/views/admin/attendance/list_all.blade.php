@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="list__title">
        <h3>{{ $titleDate }}の勤怠</h3>
    </div>
    <div class="list__guide-area">
        <div class="list__guide-link">←前日</div>
        当日
        <div class="list__guide-link">翌日→</div>
    </div>
    <div class="attendance-table__wrapper">
        <table class="attendance-table">
            <tr class="attendance-table__row">
                <th class="attendance-table__heading">名前</th>
                <th class="attendance-table__heading">出勤</th>
                <th class="attendance-table__heading">退勤</th>
                <th class="attendance-table__heading">休憩</th>
                <th class="attendance-table__heading">合計</th>
                <th class=" attendance-table__heading">詳細</th>
            </tr>
            @foreach( $attendanceRecords as $attendance )
            <tr class="attendance-table__row">
                <td class="attendance-table__data">{{ $attendance['name'] }}</td>
                <td class="attendance-table__data">{{ $attendance['clock_in'] }}</td>
                <td class="attendance-table__data">{{ $attendance['clock_out'] }}</td>
                <td class="attendance-table__data">{{ $attendance['total_rest_formatted'] }}</td>
                <td class="attendance-table__data">{{ $attendance['total_work_formatted'] }}</td>
                <td class="attendance-table__data">詳細</td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection