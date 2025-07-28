@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail">
    @if (session('error'))
    <div class="alert">
        <input type="checkbox" id="alert-close" class="alert-close">
        <div class="alert-message">
            <label for="alert-close" class="alert-close__button">×</label>
            {{ session('error') }}
        </div>
    </div>
    @endif
    <div class="detail__title">
        <h3>勤怠詳細</h3>
    </div>
    <div class="detail-form__wrapper">
        <table class="detail-form__table">
            <tr>
                <th>名前</th>
                <td class="approve-table__data-left" colspan="2">{{ $attendanceCorrection->attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="detail-table__data-left">{{ $attendanceCorrection->attendance->date->isoFormat('Y年') }}</td>
                <td>{{ $attendanceCorrection->attendance->date->isoFormat('M月D日') }}</td>
            </tr>
            <form action="/admin/approve" method="post">
                @csrf
                <tr>
                    <th>出勤・退勤</th>
                    <td class="detail-table__data-left">
                        <input type="text" class="detail-form__input--approve" name="approve_clock_in" value="{{ $attendanceCorrection->corrected_clock_in->isoFormat('H:mm') }}" readonly>　～
                    </td>
                    <td>
                        <input type="text" class="detail-form__input--approve" name="approve_clock_out" value="{{ $attendanceCorrection->corrected_clock_out->isoFormat('H:mm') }}" readonly>
                    </td>
                </tr>
                @foreach( $attendanceCorrection->restCorrections as $i => $rest )
                <tr>
                    <th>{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</th>
                    <td class="approve-table__data-left">
                        <input type="text" class="detail-form__input--approve" name="approve_rest_start" value="{{ $rest->corrected_rest_start->isoFormat('H:mm') }}" readonly>　～
                    </td>
                    <td>
                        <input type="text" class="detail-form__input--approve" name="approve_start_end" value="{{ $rest->corrected_rest_end->isoFormat('H:mm') }}" readonly>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>備考</th>
                    <td colspan="2">
                        <textarea name="" id="" class="detail-form__textarea--approve" readonly>{{ $attendanceCorrection->note }}</textarea>
                    </td>
                </tr>
        </table>
        @if( $attendanceCorrection->approve_status === 'approved' )
        <div class="detail-form__actions">
            <p class="detail-form__completed">承認済み</p>
        </div>
        @else
        <div class="detail-form__actions">
            <input type="hidden" name="correctionId" value="{{ $attendanceCorrection->id }}">
            <button class="detail-form__submit">承認</button>
        </div>
        @endif
        </form>
    </div>
</div>
@endsection