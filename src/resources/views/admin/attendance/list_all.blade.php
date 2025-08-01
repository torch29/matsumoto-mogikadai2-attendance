@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list">
    {{-- 特定の一日の全職員の勤怠一覧 --}}
    <div class="list__title">
        <h3>{{ $targetDate->isoFormat('Y年M月D日') }}の勤怠</h3>
    </div>
    <div class="list__guide-area">
        <div class="list__guide-link">
            <a href="{{ route('admin.attendances.list-by-date', ['date' => $previousDay]) }}">
                <img src="{{ asset('img/arrow.png') }}" class="list__guide-icon" alt="">前日
            </a>
        </div>
        <div class="list__current-date">
            <i for="datepicker" class="fa-regular fa-calendar-days"></i>
            <input type="text" id="datepicker" name="date" value="{{ $targetDate->isoFormat('Y/MM/DD') }}" class="date-selector">
        </div>
        <div class="list__guide-link">
            <a href="{{ route('admin.attendances.list-by-date', ['date' => $nextDay]) }}">
                翌日<img src="{{ asset('img/arrow.png') }}" class="list__guide-link--icon-next" alt="">
            </a>
        </div>
    </div>
    <div class="list-table__wrapper">
        <table class="list-table">
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>

            @foreach( $attendanceOfEachStaffMembers as $attendance )
            <tr>
                <td class="list-table__data">{{ $attendance->name }}</td>
                <td class="list-table__data">{{ $attendance->clock_in_formatted }}</td>
                <td class="list-table__data">{{ $attendance->clock_out_formatted }}</td>
                <td class="list-table__data">{{ $attendance->total_rest_formatted }}</td>
                <td class="list-table__data {{ $attendance->isOvertime() ? 'overtime' : '' }}">
                    {{ $attendance->total_work_formatted }}
                </td>
                @if( !$attendance->id)
                <td class="list-table__data"></td>
                @else
                <td class="list-table__data"><a href="/admin/attendance/{{ $attendance->id }}">詳細</a></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>
    <script src="{{ asset('js/datepicker.js') }}"></script>
</div>
@endsection