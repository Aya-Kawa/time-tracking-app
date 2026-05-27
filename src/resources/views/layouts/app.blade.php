<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__innner">
            <a href="/" class="header__logo">Coachtech</a>
            @php
                $isVerifyPage = request()->routeIs('verification.notice');
                $isPublicItemPage = request()->routeIs('items.index', 'items.show');
            @endphp
            @if (!$isVerifyPage && (auth()->check() || $isPublicItemPage))
                <form action="{{ route('items.index') }}" class="header__search" method="GET">
                    <input type="text" name="keyword" value="{{ $keyword ?? '' }}" placeholder="何をお探しですか?">

                    @if(request('tab') === 'mylist')
                        <input type="hidden" name="tab" value="mylist">
                    @endif
                </form>

                <nav class="header__nav">
                    @auth
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit">ログアウト</button>
                        </form>

                        <a href="{{ route('mypage') }}">マイページ</a>
                        <a href="{{ route('sell.create') }}" class="sell-button">出品</a>
                    @else
                        <a href="{{ route('login') }}">ログイン</a>
                        <a href="{{ route('sell.create') }}" class="sell-button">出品</a>
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>