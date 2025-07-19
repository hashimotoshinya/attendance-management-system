@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-container">
    <h1 class="title">ログイン</h1>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <input type="hidden" name="login_type" value="staff">

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input id="email" type="email" name="email" autofocus>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input id="password" type="password" name="password">
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="login-btn">ログインする</button>
        </div>

        @if(session('status'))
            <div class="error">{{ session('status') }}</div>
        @endif

        <div class="register-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection