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
    <div class="detail__content">
        <form action="/admin/correction" class="detail-form" method="post">
            @csrf
            <table class="detail-form__table">
                <tr>
                    <th>名前</th>
                    <td class="detail-table__data-left" colspan="2"><span>{{ $attendance->user->name }}</span></td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td class="detail-table__data-left"><span>{{ $attendance->date->isoFormat('Y') }}年</span></td>
                    <td>
                        <span>{{ $attendance->date->isoFormat('M') . '月' . $attendance->date->isoFormat('D') . '日'  }}</span>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td class="detail-table__data-left">
                        <input type="time" class="detail-form__input" name="corrected_clock_in"
                            value="{{ old("corrected_clock_in", optional($displayClockIn)->format('H:i')) }}">　～
                    </td>
                    <td>
                        <input type="time" class="detail-form__input" name="corrected_clock_out"
                            value="{{ old("corrected_clock_out", optional($displayClockOut)->format('H:i')) }}">
                    </td>
                    <td class="table__data--error">
                        <p class="error__message">
                            @error('corrected_clock_in')
                            {{ $message }}
                            @enderror
                        </p>
                        <p class="error__message">
                            @error('corrected_clock_out')
                            {{ $message }}
                            @enderror
                        </p>
                    </td>
                </tr>
                @foreach( $restRecords as $i => $rest )
                <tr>
                    <th class="detail-table__heading">
                        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                    </th>
                    <td class=" detail-table__data-left">
                        <input type="time" class="detail-form__input" name="rest_corrections[{{ $i }}][corrected_rest_start]" value="{{ old("rest_corrections.$i.corrected_rest_start", optional($rest->rest_start)->format('H:i')) }}">　～
                    </td>
                    <td>
                        <input type="time" class="detail-form__input" name="rest_corrections[{{ $i }}][corrected_rest_end]" value="{{ old("rest_corrections.$i.corrected_rest_end", optional($rest->rest_end)->format('H:i')) }}">
                    </td>
                    <td class="table__data--error">
                        <p class="error__message">
                            @error("rest_corrections.$i.corrected_rest_start")
                            {{ $message }}
                            @enderror
                        </p>
                        <p class="error__message">
                            @error("rest_corrections.$i.corrected_rest_end")
                            {{ $message }}
                            @enderror
                        </p>
                    </td>
                </tr>
                @endforeach
                <tr>
                    <th>
                        {{ count($restRecords) === 0 ? '休憩' : '休憩' . (count($restRecords) + 1) }}
                    </th>
                    <td class="detail-table__data-left">
                        <input type="time" class="detail-form__input" name="rest_corrections[new][corrected_rest_start]" value="{{ old("rest_corrections.new.corrected_rest_start") }}">　～
                    </td>
                    <td>
                        <input type="time" class="detail-form__input" name="rest_corrections[new][corrected_rest_end]" value="{{ old("rest_corrections.new.corrected_rest_end") }}">
                    </td>
                    <td class="table__data--error">
                        <p class="error__message">
                            @error("rest_corrections.new.corrected_rest_start")
                            {{ $message }}
                            @enderror
                        </p>
                        <p class="error__message">
                            @error("rest_corrections.new.corrected_rest_end")
                            {{ $message }}
                            @enderror
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td colspan="2">
                        <textarea name="note" id="" class="detail-form__textarea" placeholder="例：電車遅延のため">{{ old('note', $displayNote) }}</textarea>
                    </td>
                    <td>
                        <p class="error__message">
                            @error('note')
                            {{ $message }}
                            @enderror
                        </p>
                    </td>
                </tr>
            </table>

            @if ( $latestCorrection && $latestCorrection->approve_status === 'pending' )
            <div class="detail__status-message">*職員からの修正申請がきています。先に承認画面から承認してください。</div>
            @else
            <div class="detail-form__actions">
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                <button class="detail-form__submit">修正</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection