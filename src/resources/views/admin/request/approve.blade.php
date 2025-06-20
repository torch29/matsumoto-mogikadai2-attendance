@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="approve__content">
    <div class="approve__title">
        <h3>勤怠詳細</h3>
    </div>
    <div class="approve-table__wrapper">
        <form action="" class="approve-form">
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
                <tr class="approve-table__row">
                    <th class="approve-table__heading">出勤・退勤</th>
                    <td class="approve-table__data-left">
                        <input type="text" class="approve-table__input" value="{{ $attendanceCorrection->corrected_clock_in->isoFormat('H:mm') }}">　～
                    </td>
                    <td class="approve-table__data">
                        <input type="text" class="approve-table__input" value="{{ $attendanceCorrection->corrected_clock_out->isoFormat('H:mm') }}">
                    </td>
                </tr>
                <tr class="approve-table__row">
                    <th class="approve-table__heading">休憩</th>
                    <td class="approve-table__data-left">
                        <input type="text" class="approve-table__input" value="休憩入時間">　～
                    </td>
                    <td class="approve-table__data">
                        <input type="text" class="approve-table__input" value="休憩戻時間">
                    </td>
                </tr>
                <tr class="approve-table__row">
                    <th class="approve-table__heading">休憩2</th>
                    <td class="approve-table__data-left">
                        <input type="text" class="approve-table__input" value="休憩入">
                    </td>
                    <td class="approve-table__data">
                        <input type="text" class="approve-table__input" value="休憩戻">
                    </td>
                </tr>
                <tr class="approve-table__row">
                    <th class="approve-table__heading">備考</th>
                    <td class="approve-table__data" colspan="2">
                        <textarea name="" id="" class="approve-table__textarea" readonly>{{ $attendanceCorrection->note }}</textarea readonly>
                    </td>
                </tr>
            </table>
            <div class="approve-form__button">
                <button class="approve-form__button-submit">承認</button>
            </div>
        </form>
    </div>
</div>
@endsection