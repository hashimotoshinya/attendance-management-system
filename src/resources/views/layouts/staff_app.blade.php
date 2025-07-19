<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/staff_app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>

        <nav class="nav">
            @if (!isset($status) || $status !== '退勤済')
                <a href="{{ url('/attendance') }}">勤怠</a>
            @endif

            <a href="{{ url('/attendance/list') }}">勤怠一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>
</html>