@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list__content">
    {{-- 特定の一日の全職員の勤怠一覧 --}}
    <div class="list__title">
        <h3>{{ $targetDate->isoFormat('Y年M月D日') }}の勤怠</h3>
    </div>
    <div class="list__guide-area">
        <div class="list__guide-link">
            <a href="{{ route('admin.attendances.list-by-date', ['date' => $previousDay]) }}">
                <img src="{{ asset('img/arrow.png') }}" class="link__icon" alt="">前日
            </a>
        </div>
        <div class="list__current-date">
            <i for="datepicker" class="fa-regular fa-calendar-days"></i>
            <input type="text" id="datepicker" name="date" value="{{ $targetDate->isoFormat('Y/MM/DD') }}" class="date-selector">
        </div>
        <div class="list__guide-link">
            <a href="{{ route('admin.attendances.list-by-date', ['date' => $nextDay]) }}">
                翌日<img src="{{ asset('img/arrow.png') }}" class="link__icon-next" alt="">
            </a>
        </div>
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

            @foreach( $attendanceOfEachStaffMembers as $attendance )
            <tr class="attendance-table__row">
                <td class="attendance-table__data">{{ $attendance->name }}</td>
                <td class="attendance-table__data">{{ $attendance->clock_in_formatted }}</td>
                <td class="attendance-table__data">{{ $attendance->clock_out_formatted }}</td>
                <td class="attendance-table__data">{{ $attendance->total_rest_formatted }}</td>
                <td class="attendance-table__data {{ $attendance->isOvertime() ? 'overtime' : '' }}">{{ $attendance->total_work_formatted }}</td>
                @if( !$attendance->id)
                <td class="attendance-table__data"></td>
                @else
                <td class="attendance-table__data"><a href="/admin/attendance/{{ $attendance->id }}">詳細</a></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>
    <script src="{{ asset('js/datepicker.js') }}"></script>
</div>
@endsection