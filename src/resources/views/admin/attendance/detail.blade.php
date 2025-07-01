@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__content">
    @if (session('error'))
    <div class="item__alert">
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
    <div class="detail-table__wrapper">
        <form action="/admin/correction" class="detail-form" method="post">
            @csrf
            <table class="detail-table">
                <tr class="detail-table__row">
                    <th class="detail-table__heading">名前</th>
                    <td class="detail-table__data-left" colspan="2"><span>{{ $attendance->user->name }}</span></td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">日付</th>
                    <td class="detail-table__data-left"><span>{{ $attendance->date->isoFormat('Y') }}年</span></td>
                    <td class="detail-table__data"><span>{{ $attendance->date->isoFormat('M') . '月' . $attendance->date->isoFormat('D') . '日'  }}</span></td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">出勤・退勤</th>
                    <td class="detail-table__data-left">
                        <input type="time" class="detail-table__input" name="corrected_clock_in" value="{{ old("corrected_clock_in", optional($displayClockIn)->format('H:i')) }}">　～
                    </td>
                    <td class="detail-table__data">
                        <input type="time" class="detail-table__input" name="corrected_clock_out" value="{{ old("corrected_clock_out", optional($displayClockOut)->format('H:i')) }}">
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
                <tr class="detail-table__row">
                    <th class="detail-table__heading">
                        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                    </th>
                    <td class=" detail-table__data-left">
                        <input type="time" class="detail-table__input" name="rest_corrections[{{ $i }}][corrected_rest_start]" value="{{ old("rest_corrections.$i.corrected_rest_start", optional($rest->rest_start)->format('H:i')) }}">　～
                    </td>
                    <td class="detail-table__data">
                        <input type="time" class="detail-table__input" name="rest_corrections[{{ $i }}][corrected_rest_end]" value="{{ old("rest_corrections.$i.corrected_rest_end", optional($rest->rest_end)->format('H:i')) }}">
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
                <tr class="detail-table__row">
                    <th class="detail-table__heading">
                        {{ count($restRecords) === 0 ? '休憩' : '休憩' . (count($restRecords) + 1) }}
                    </th>
                    <td class="detail-table__data-left">
                        <input type="time" class="detail-table__input" name="rest_corrections[new][corrected_rest_start]" value="{{ old("rest_corrections.new.corrected_rest_start") }}">　～
                    </td>
                    <td class="detail-table__data">
                        <input type="time" class="detail-table__input" name="rest_corrections[new][corrected_rest_end]" value="{{ old("rest_corrections.new.corrected_rest_end") }}">
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
                <tr class="detail-table__row">
                    <th class="detail-table__heading">備考</th>
                    <td class="detail-table__data" colspan="2">
                        <textarea name="note" id="" class="detail-table__textarea" placeholder="例：電車遅延のため">{{ old('note', $displayNote) }}</textarea>
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
            @if ($errors->any())
            <div class="form__error">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            {{ $latestCorrection ? $latestCorrection->approve_status : 'なし' }}
            @if ( $latestCorrection && $latestCorrection->approve_status === 'pending' )
            <div>*承認待ちのため現在修正はできません。</div>
            @else
            <div class="detail-form__button">
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                <button class="detail-form__button-submit">修正</button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection