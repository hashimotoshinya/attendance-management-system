<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/attendance_app.css') }}">

    {{-- login_type でCSS切り替え --}}
    @php
        $loginType = session('login_type'); // 'admin' or 'staff'
    @endphp

    

    @yield('css')
</head>
<body>
    <header class="header">
        <div class="logo">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
        </div>

        <nav class="nav">
            @if ($loginType === 'admin')
                <a href="{{ url('/admin/attendance/list') }}">勤怠一覧</a>
                <a href="{{ url('/admin/staff/list') }}">スタッフ一覧</a>
                <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>

                <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            @elseif ($loginType === 'staff')
                {{-- スタッフのステータスによって「勤怠」リンクを出し分ける --}}
                @php
                    // $status が View に渡されている前提
                    $status = $status ?? null;
                @endphp

                @if ($status !== '退勤済')
                    <a href="{{ url('/attendance') }}">勤怠</a>
                @endif
                <a href="{{ url('/attendance/list') }}">勤怠一覧</a>
                <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>

                <form method="POST" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            @endif
        </nav>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>
</html>