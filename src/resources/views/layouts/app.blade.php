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
                <a href="/attendance">
                    <img src="{{ asset('img/logo.svg') }}" class="header__title-img" alt="COACHTECHロゴ">
                </a>
            </h1>
            @if (Auth::check())
            <ul class="header-nav__list">
                <li class="header-nav__list-item"><a href="/attendance">勤怠</a></li>
                <li class="header-nav__list-item"><a href="/attendance/list">勤怠一覧</a></li>
                <li class="header-nav__list-item"><a href="/stamp_correction_request/list">申請</a></li>
                <li class="header-nav__list-item">
                    <form action="/logout" class="header-nav__form" method="post">
                        @csrf
                        <button class="header-nav__button-submit">ログアウト</button>
                    </form>
                </li>
                @else
                <li class="header-nav__list-item">

                </li>
            </ul>
            @endif
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

</body>

</html>