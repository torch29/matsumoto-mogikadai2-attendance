@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    {{-- 一般職員でログイン時の表示 --}}
    <div class="list__title">
        <h3>申請一覧</h3>
    </div>
    <div class="request-list__tab">
        承認待ち
        承認済み
    </div>
    一般職員：自分だけ　{{-- あとで消す --}}
    <div class="request-table__wrapper">
        <table class="request-table">
            <tr class="request-table__row">
                <th class="request-table__heading">状態</th>
                <th class="request-table__heading">名前</th>
                <th class="request-table__heading">対象日時</th>
                <th class="request-table__heading">申請理由</th>
                <th class="request-table__heading">申請日時</th>
                <th class=" request-table__heading">詳細</th>
            </tr>
            <tr class="request-table__row">
                <td class="request-table__data">承認待ち</td>
                <td class="request-table__data">サンプル　名前</td>
                <td class="request-table__data">2025/06/06</td>
                <td class="request-table__data">遅延のため</td>
                <td class="request-table__data">2025/06/08</td>
                <td class="request-table__data">詳細</td>
            </tr>
            <tr class="request-table__row">
                <td class="request-table__data">承認待ち</td>
                <td class="request-table__data">サンプル　名前</td>
                <td class="request-table__data">2025/06/07</td>
                <td class="request-table__data">体調不良のため</td>
                <td class="request-table__data">2025/06/07</td>
                <td class="request-table__data">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection