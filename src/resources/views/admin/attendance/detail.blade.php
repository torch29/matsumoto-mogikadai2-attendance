@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')
<div class="detail__content">
    <div class="detail__title">
        <h3>勤怠詳細</h3>
    </div>
    <div class="detail-table__wrapper">
        <form action="" class="detail-form">
            <table class="detail-table">
                <tr class="detail-table__row">
                    <th class="detail-table__heading">名前</th>
                    <td class="detail-table__data-left" colspan="2">{{ $attendance->user->name }}</td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">日付</th>
                    <td class="detail-table__data-left">{{ $attendance->date->isoFormat('Y') }}年</td>
                    <td class="detail-table__data">{{ $attendance->date->isoFormat('M') . '月' . $attendance->date->isoFormat('D') . '日'  }}</td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">出勤・退勤</th>
                    <td class="detail-table__data-left">
                        <input type="text" class="detail-table__input" name="clock_in" value="{{ old("clock_in", optional($attendance->clock_in)->isoFormat('H:mm')) }}">
                    </td>
                    <td class="detail-table__data">
                        ～　　<input type="text" class="detail-table__input" name="clock_out" value="{{ old("clock_out", optional($attendance->clock_out)->isoFormat('H:mm')) }}">
                    </td>
                </tr>
                @foreach( $attendance->rests as $i => $rest )
                <tr class="detail-table__row">
                    <th class="detail-table__heading">
                        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                    </th>
                    <td class=" detail-table__data-left">
                        <input type="text" class="detail-table__input" name="rests[{{ $i }}][rest_start]" value="{{ old("rests.$i.rest_start", optional($rest->rest_start)->isoFormat('H:mm')) }}">
                    </td>
                    <td class="detail-table__data">
                        ～　　<input type="text" class="detail-table__input" name="rests[{{ $i }}][rest_end]" value="{{ old("rests.$i.rest_end", optional($rest->rest_end)->isoFormat('H:mm')) }}">
                    </td>
                </tr>
                @endforeach
                <tr class="detail-table__row">
                    <th class="detail-table__heading">
                        {{ count($attendance->rests) === 0 ? '休憩' : '休憩' . (count($attendance->rests) + 1) }}
                    </th>
                    <td class="detail-table__data-left">
                        <input type="text" class="detail-table__input" name="rests[new][rest_start]" value="{{ old("rests.new.rest_start") }}">
                    </td>
                    <td class="detail-table__data">
                        ～　　<input type="text" class="detail-table__input" name="rests[new][rest_end]" value="{{ old("rests.new.rest_end") }}">
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">備考</th>
                    <td class="detail-table__data" colspan="2">
                        <textarea name="note" id="" class="detail-table__textarea" placeholder="例：電車遅延のため">{{ old('note') }}</textarea>
                    </td>
                </tr>
            </table>
            <div class="detail-form__button">
                <button class="detail-form__button-submit">修正</button>
            </div>
        </form>
    </div>
</div>
@endsection