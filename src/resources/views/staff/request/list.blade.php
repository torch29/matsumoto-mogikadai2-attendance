@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list">
    {{-- 一般職員でログイン時の申請一覧表示 --}}
    <div class="list__title">
        <h3>申請一覧</h3>
    </div>
    <div class="request-list__tab">
        <ul class="tab-menu">
            <li class="{{ $tab === 'pending' ? 'active' : '' }}">
                <a href="{{ route('correction_requests.list',['tab' => 'pending']) }}">承認待ち</a>
            </li>
            <li class="{{ $tab === 'approved' ? 'active' : '' }}">
                <a href="{{ route('correction_requests.list',['tab' => 'approved']) }}">承認済み</a>
            </li>
        </ul>
    </div>
    <div class="list-table__wrapper">
        <table class="list-table">
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @foreach( $attendanceCorrections as $correction )
            <tr>
                <td>{{ $correction->approvalStatusLabel() }}</td>
                <td>{{ $correction->attendance->user->name }}</td>
                <td>{{ $correction->correction_target_date_formatted }}</td>
                <td>{{ $correction->note }}</td>
                <td>{{ $correction->requested_at_formatted }}</td>
                <td><a href="/attendance/{{ $correction['attendance_id'] }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection