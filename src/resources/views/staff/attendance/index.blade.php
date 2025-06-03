@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="main__content">
    <div class="status-label">勤務外</div>
    <div class="main__content-date">
        {{ \Carbon\Carbon::now()->isoFormat('Y/M/D（ddd）') }}
    </div>
    <div class="main__content-datetime">
        {{ \Carbon\Carbon::now()->format('H:i') }}
    </div>
    <div class="attendance-form">
        <form action="">
            <div class="attendance-form__button">
                <button class="attendance-form__button-submit">出勤</button>
            </div>
        </form>
    </div>
</div>
@endsection