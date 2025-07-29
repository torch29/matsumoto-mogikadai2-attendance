@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list">
    <div class="list__title">
        <h3>{{ $staff->name }}さんの勤怠</h3>
    </div>
    <div class="list__guide-area">
        <div class="list__guide-link">
            <a href="{{ route('admin.attendances.list-by-staff', ['id' => $staff->id, 'date' => $previousMonth]) }}">
                <img src="{{ asset('img/arrow.png') }}" class="link__icon" alt="">前月
            </a>
        </div>
        <div class="list__current-date">
            <i for="datepicker" class="fa-regular fa-calendar-days"></i>
            <span><input type="text" id="monthPicker" class="month-selector" value="{{ $selectDate->format('Y/m') }}">
            </span>
        </div>
        <div class="list__guide-link">
            <a href="{{ route('admin.attendances.list-by-staff', ['id' => $staff->id, 'date' => $nextMonth]) }}">
                翌月<img src="{{ asset('img/arrow.png') }}" class="list__guide-link--icon-next" alt="">
            </a>
        </div>
    </div>

    <div class="list-table__wrapper">
        <table class="list-table">
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach( $attendanceRecords as $attendance )
            <tr>
                <th class="list-table__data"><span class="attendance-table__data--date">{{ $attendance->date->isoFormat('M月D日（ddd）') }}</span></th>
                <td>{{ $attendance->clock_in_formatted }}</td>
                <td class="attendance-table__data">{{ $attendance->clock_out_formatted }}</td>
                <td class="attendance-table__data">{{ $attendance->total_rest_formatted }}</td>
                <td class="attendance-table__data {{ $attendance->isOvertime() ? 'overtime' : '' }}">{{ $attendance->total_work_formatted }}</td>
                @if( empty($attendance->id) )
                {{-- 勤怠情報がない場合”詳細”を表示しない --}}
                <td class="attendance-table__data"></td>
                @else
                <td class="attendance-table__data"><a href="/admin/attendance/{{ $attendance->id }}">詳細</a></td>
                @endif
            </tr>
            @endforeach
        </table>
        <form action="/admin/export/{{ $staff->id }}" method="get">
            @csrf
            <div class="list__actions">
                <button class="list__actions--export-button">CSV出力</button>
            </div>
        </form>
    </div>
    <script src="{{ asset('js/monthpicker.js') }}"></script>
</div>
@endsection