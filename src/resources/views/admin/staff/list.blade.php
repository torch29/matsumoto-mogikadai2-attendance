@extends('layouts.admin-app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_list.css') }}">
@endsection

@section('content')
<div class="list__content">
    @if (session('error'))
    <div class="alert">
        <input type="checkbox" id="alert-close" class="alert-close">
        <div class="alert-message">
            <label for="alert-close" class="alert-close__button">×</label>
            {{ session('error') }}
        </div>
    </div>
    @endif
    <div class="list__title">
        <h3>スタッフ一覧</h3>
    </div>
    <div class="staff-table__wrapper">
        <table class="staff-table">
            <tr class="staff-table__row">
                <th class="staff-table__heading">名前</th>
                <th class="staff-table__heading">メールアドレス</th>
                <th class="staff-table__heading">月次勤怠</th>
            </tr>
            @foreach( $staffLists as $staff )
            @continue( $staff->is_admin )
            <tr class="staff-table__row">
                <td class="staff-table__data">{{ $staff->name }}</td>
                <td class="staff-table__data">{{ $staff->email }}</td>
                <td class="staff-table__data"><a href="{{ route('admin.attendances.list-by-staff', ['id' => $staff->id]) }}">詳細</a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection