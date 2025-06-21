@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    {{-- 一般職員でログイン時の申請一覧表示 --}}
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
            @foreach( $stampCorrectionRecords as $correction )
            <tr class="request-table__row">
                <td class="request-table__data">{{ $correction['status'] }}</td>
                <td class="request-table__data">{{ $correction['name'] }}</td>
                <td class="request-table__data">{{ $correction['correction_target_date'] }}</td>
                <td class="request-table__data">{{ $correction['note'] }}</td>
                <td class="request-table__data">{{ $correction['requested_at'] }}</td>
                <td class="request-table__data"><a href="/attendance/{{ $correction['attendance_id'] }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection