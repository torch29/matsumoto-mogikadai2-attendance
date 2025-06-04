@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="list__title">
        <h3>勤怠一覧</h3>
    </div>
    <div class="list__guide-area">
        <div class="list__guide-link">←前月</div>
        当月
        <div class="list__guide-link">翌月→</div>
    </div>
    <div class="list__content-table">
        <table class=" list__table">
            <tr class="list__table-row">
                <th class="list__table-heading">日付</th>
                <th class="list__table-heading">出勤</th>
                <th class="list__table-heading">退勤</th>
                <th class="list__table-heading">休憩</th>
                <th class="list__table-heading">合計</th>
                <th class=" list__table-heading">詳細</th>
            </tr>
            <tr class="list__table-row">
                <td class="list__table-data">06/01（木）</td>
                <td class="list__table-data">09:00</td>
                <td class="list__table-data">18:00</td>
                <td class="list__table-data">1:00</td>
                <td class="list__table-data">8:00</td>
                <td class="list__table-data">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection