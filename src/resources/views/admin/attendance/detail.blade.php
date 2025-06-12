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
                    <td class="detail-table__data-left" colspan="2">サンプル　名前</td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">日付</th>
                    <td class="detail-table__data-left">20XX年</td>
                    <td class="detail-table__data">〇月×日</td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">出勤・退勤</th>
                    <td class="detail-table__data-left">
                        <input type="text" class="detail-table__input" value="出勤時刻">
                    </td>
                    <td class="detail-table__data">
                        ～　　<input type="text" class="detail-table__input" value="退勤時刻">
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">休憩</th>
                    <td class="detail-table__data-left">
                        <input type="text" class="detail-table__input" value="休憩入">
                    </td>
                    <td class="detail-table__data">
                        ～　　<input type="text" class="detail-table__input" value="休憩戻">
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">休憩2</th>
                    <td class="detail-table__data-left">
                        <input type="text" class="detail-table__input" value="休憩入">
                    </td>
                    <td class="detail-table__data">
                        <input type="text" class="detail-table__input" value="休憩戻">
                    </td>
                </tr>
                <tr class="detail-table__row">
                    <th class="detail-table__heading">備考</th>
                    <td class="detail-table__data" colspan="2">
                        <textarea name="" id="" class="detail-table__textarea">電車遅延のため</textarea>
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