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
                <td class="attendance-table__data">06/01（木）</td>
                <td class="attendance-table__data">09:00</td>
                <td class="attendance-table__data">18:00</td>
                <td class="attendance-table__data">1:00</td>
                <td class="attendance-table__data">8:00</td>
                <td class="attendance-table__data">詳細</td>
            </tr>
            <tr class="attendance-table__row">
                <td class="attendance-table__data">XX/XX（◇）</td>
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