@extends('layouts.staff_app')

@section('title', '勤怠')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
    <div class="status">
        <span class="status-badge">{{ $status ?? '勤務外' }}</span>
    </div>

    @php
    $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
    $now = \Carbon\Carbon::now();
    @endphp

    <div class="date-time">
        <div class="date">{{ $now->format('Y年n月j日') }}({{ $weekDays[$now->dayOfWeek] }})</div>
        <div class="time">{{ $now->format('H:i') }}</div>
    </div>

    <div class="action">
        @if ($status === '勤務外')
            <form method="POST" action="{{ route('attendance.start') }}">
                @csrf
                <button class="btn">出勤</button>
            </form>
        @elseif ($status === '出勤中')
            <form method="POST" action="{{ route('attendance.break.start') }}">
                @csrf
                <button class="btn">休憩</button>
            </form>
            <form method="POST" action="{{ route('attendance.end') }}">
                @csrf
                <button class="btn">退勤</button>
            </form>
        @elseif ($status === '休憩中')
            <form method="POST" action="{{ route('attendance.break.end') }}">
                @csrf
                <button class="btn">休憩戻</button>
            </form>
        @elseif ($status === '退勤済')
            <div class="message">お疲れ様でした。</div>
        @endif
    </div>
@endsection