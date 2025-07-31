@extends('layouts.attendance_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <h2 class="title">{{ $date->format('Y年n月j日') }}の勤怠</h2>

    <div class="date-navigation">
        <form method="GET" action="{{ route('admin.attendance.list') }}" class="prev-form">
            <input type="hidden" name="date" value="{{ $date->copy()->subDay()->format('Y-m-d') }}">
            <button type="submit" class="date-button">&larr; 前日</button>
        </form>

        <div class="current-date">
            <span>🗓️{{ $date->format('Y/m/d') }}</span>
        </div>

        <form method="GET" action="{{ route('admin.attendance.list') }}" class="next-form">
            <input type="hidden" name="date" value="{{ $date->copy()->addDay()->format('Y-m-d') }}">
            <button type="submit" class="date-button">翌日 &rarr;</button>
        </form>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->start_time ? $attendance->start_time->format('H:i') : '' }}</td>
                    <td>{{ $attendance->end_time ? $attendance->end_time->format('H:i') : '' }}</td>
                    <td>{{ $attendance->total_break_time ?? '' }}</td>
                    <td>{{ $attendance->total_work_time ?? '' }}</td>
                    <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
                </tr>
            @empty
                <tr><td colspan="6">該当する勤怠データはありません。</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection