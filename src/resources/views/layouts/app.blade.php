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
                <a href="/">
                    <img src="{{ asset('img/logo.svg') }}" class="header__title-img" alt="COACHTECHロゴ">
                </a>
            </h1>
            <ul class="header-nav__list">
                <li class="header-nav__list-item">
                    <ul class="header-nav__list--user">
                        @if (Auth::check())
                        <li class="header-nav__user-item">
                            <form action="/logout" class="header-nav__form" method="post">
                                @csrf
                                <button class="header-nav__button-submit">ログアウト</button>
                            </form>
                        </li>
                        @else
                        <li class="header-nav__user-item">
                            <form action="/login" method="get">
                                @csrf
                                <button class="header-nav__button-submit">ログイン</button>
                            </form>
                        </li>
                        @endif
                        <li class="header-nav__user-item"><a href="/mypage">マイページ</a></li>
                        <a href="/sell" class="header-nav__button">出品</a>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

</body>

</html>