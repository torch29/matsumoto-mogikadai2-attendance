@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="approve__content">
    @if (session('error'))
    <div class="item__alert">
        <input type="checkbox" id="alert-close" class="alert-close">
        <div class="alert-message">
            <label for="alert-close" class="alert-close__button">×</label>
            {{ session('error') }}
        </div>
    </div>
    @endif
    <div class="approve__title">
        <h3>勤怠詳細</h3>
    </div>
    <div class="approve-table__wrapper">
        <table class="approve-table">
            <tr class="approve-table__row">
                <th class="approve-table__heading">名前</th>
                <td class="approve-table__data-left" colspan="2">{{ $attendanceCorrection->attendance->user->name }}</td>
            </tr>
            <tr class="approve-table__row">
                <th class="approve-table__heading">日付</th>
                <td class="approve-table__data-left">{{ $attendanceCorrection->attendance->date->isoFormat('Y年') }}</td>
                <td class="approve-table__data">{{ $attendanceCorrection->attendance->date->isoFormat('M月D日') }}</td>
            </tr>
            <form action="/admin/approve" method="post">
                @csrf
                <tr class="approve-table__row">
                    <th class="approve-table__heading">出勤・退勤</th>
                    <td class="approve-table__data-left">
                        <input type="text" class="approve-table__input" name="approve_clock_in" value="{{ $attendanceCorrection->corrected_clock_in->isoFormat('H:mm') }}" readonly>　～
                    </td>
                    <td class="approve-table__data">
                        <input type="text" class="approve-table__input" name="approve_clock_out" value="{{ $attendanceCorrection->corrected_clock_out->isoFormat('H:mm') }}" readonly>
                    </td>
                </tr>
                @foreach( $attendanceCorrection->restCorrections as $i => $rest )
                <tr class="approve-table__row">
                    <th class="approve-table__heading">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                    <td class="approve-table__data-left">
                        <input type="text" class="approve-table__input" name="approve_rest_start" value="{{ $rest->corrected_rest_start->isoFormat('H:mm') }}" readonly>　～
                    </td>
                    <td class="approve-table__data">
                        <input type="text" class="approve-table__input" name="approve_start_end" value="{{ $rest->corrected_rest_end->isoFormat('H:mm') }}" readonly>
                    </td>
                </tr>
                @endforeach
                <tr class="approve-table__row">
                    <th class="approve-table__heading">備考</th>
                    <td class="approve-table__data" colspan="2">
                        <textarea name="" id="" class="approve-table__textarea" readonly>{{ $attendanceCorrection->note }}</textarea>
                    </td>
                </tr>
        </table>
        @if( $attendanceCorrection->approve_status === 'approved' )
        <p>承認済み</p>
        @else
        <div class="approve-form__button">
            <input type="hidden" name="correctionId" value="{{ $attendanceCorrection->id }}">
            <button class="approve-form__button-submit">承認</button>
        </div>
        @endif
        </form>
    </div>
</div>
@endsection