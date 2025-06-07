@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="list__title">
        <h3>20XX年〇月◇日の勤怠</h3>
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
            <tr class="attendance-table__row">
                <td class="attendance-table__data">サンプル　さん</td>
                <td class="attendance-table__data">09:00</td>
                <td class="attendance-table__data">18:00</td>
                <td class="attendance-table__data">1:00</td>
                <td class="attendance-table__data">8:00</td>
                <td class="attendance-table__data">詳細</td>
            </tr>
            <tr class="attendance-table__row">
                <td class="attendance-table__data">テスト　さん</td>
                <td class="attendance-table__data">08:30</td>
                <td class="attendance-table__data">17:30</td>
                <td class="attendance-table__data">1:00</td>
                <td class="attendance-table__data">8:00</td>
                <td class="attendance-table__data">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection