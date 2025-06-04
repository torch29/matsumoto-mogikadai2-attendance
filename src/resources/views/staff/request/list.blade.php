@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="list__title">
        <h3>申請一覧</h3>
    </div>
    <div class="request-list__tab">
        承認待ち
        承認済み
    </div>
    <div class="request-table__wrapper">
        <table class="request-table">
            <tr class="request-table__row">
                <th class="request-table__heading">日付</th>
                <th class="request-table__heading">出勤</th>
                <th class="request-table__heading">退勤</th>
                <th class="request-table__heading">休憩</th>
                <th class="request-table__heading">合計</th>
                <th class=" request-table__heading">詳細</th>
            </tr>
            <tr class="request-table__row">
                <td class="request-table__data">06/01（木）</td>
                <td class="request-table__data">09:00</td>
                <td class="request-table__data">18:00</td>
                <td class="request-table__data">1:00</td>
                <td class="request-table__data">8:00</td>
                <td class="request-table__data">詳細</td>
            </tr>
            <tr class="request-table__row">
                <td class="request-table__data">XX/XX（◇）</td>
                <td class="request-table__data">08:30</td>
                <td class="request-table__data">17:30</td>
                <td class="request-table__data">1:00</td>
                <td class="request-table__data">8:00</td>
                <td class="request-table__data">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection