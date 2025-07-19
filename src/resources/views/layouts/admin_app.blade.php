<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/admin_app.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>

        <nav class="nav">
            <a href="{{ url('/admin/attendance/list') }}">勤怠一覧</a>
            <a href="{{ url('/admin/staff/list') }}">スタッフ一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>

            <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
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