<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a href="{{ route('attendance.index') }}" class="header__logo">
                <img src="{{ asset('images/logo.png') }}" alt="COACHTECH">
            </a>

            @auth
                <nav class="header__nav">
                    @if(Auth::user()->admin_status)
                        <a href="{{ route('admin.attendance.list') }}">
                            勤怠一覧
                        </a>
                        <a href="{{ route('admin.staff.list') }}">
                            スタッフ一覧
                        </a>
                        <a href="{{ route('admin.correction.index') }}">
                            申請一覧
                        </a>

                    @else
                        <a href="{{ route('attendance.index') }}">
                            勤怠
                        </a>
                        <a href="{{ route('attendance.list') }}">
                            勤怠一覧
                        </a>
                        <a href="{{ route('stamp_correction_request.index') }}">
                            申請
                        </a>
                        <a href="{{ route('attendance.report') }}">
                            レポート
                        </a>

                    @endif
                    <form action="{{ route('logout') }}" method="POST">

                        @csrf
                        <button type="submit">
                            ログアウト
                        </button>
                    </form>
                </nav>

            @endauth
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>