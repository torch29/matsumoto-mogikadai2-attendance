<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理アプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <nav class="header-nav">
            <h1 class="header-nav__ttl">
                <img src="{{ asset('img/logo.svg') }}" class="header__title-img" alt="COACHTECHロゴ">
            </h1>
            @if ( Auth::check() && Auth::user()->is_admin )
            <ul class="header-nav__list">
                <li class="header-nav__list-item"><a href="/admin/attendance/list">勤怠一覧</a></li>
                <li class="header-nav__list-item"><a href="/admin/staff/list">スタッフ一覧</a></li>
                <li class="header-nav__list-item"><a href="/admin/stamp_correction_request/list">申請一覧</a></li>
                <li class="header-nav__list-item">
                    <form action="/logout" class="header-nav__form" method="post">
                        @csrf
                        <button class="header-nav__button-submit">ログアウト</button>
                    </form>
                </li>
                @endif
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

</body>

</html>