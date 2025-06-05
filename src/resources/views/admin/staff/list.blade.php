@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_list.css') }}">
@endsection

@section('content')
<div class="list__content">
    <div class="list__title">
        <h3>スタッフ一覧</h3>
    </div>
    <div class="staff-table__wrapper">
        <table class="staff-table">
            <tr class="staff-table__row">
                <th class="staff-table__heading">名前</th>
                <th class="staff-table__heading">メールアドレス</th>
                <th class="staff-table__heading">月次勤怠</th>
            </tr>
            <tr class="staff-table__row">
                <td class="staff-table__data">サンプル　さん</td>
                <td class="staff-table__data">test@example.com</td>
                <td class="staff-table__data">詳細</td>
            </tr>
            <tr class="staff-table__row">
                <td class="staff-table__data">テスト　さん</td>
                <td class="staff-table__data">test2@example.com</td>
                <td class="staff-table__data">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection