@extends('layouts.attendance_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="container">
    <h2 class="title">勤怠一覧</h2>

    <div class="month-switch">
        <a href="?month={{ $previousMonth->format('Y-m') }}" class="btn-month">← 前月</a>
        <span class="current-month">{{ $currentMonth->format('Y年m月') }}</span>
        <a href="?month={{ $nextMonth->format('Y-m') }}" class="btn-month">翌月 →</a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dates as $date)
                @php
                    $attendance = $attendances->get($date->format('Y-m-d'));

                    $weekdayMap = ['日', '月', '火', '水', '木', '金', '土'];
                    $weekday = $weekdayMap[$date->dayOfWeek];
                    $formattedDate = $date->format('m/d') . "($weekday)";

                    $start = $end = $break = $total = '';
                    if ($attendance) {
                        $start = $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '';
                        $end = $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '';
                        $break = $attendance->total_break_time ?? '';
                        $total = $attendance->total_work_time ?? '';
                    }
                @endphp
                <tr>
                    <td>{{ $formattedDate }}</td>
                    <td>{{ $start }}</td>
                    <td>{{ $end }}</td>
                    <td>{{ $break }}</td>
                    <td>{{ $total }}</td>
                    <td>
                        @if($attendance)
                            <a href="{{ route('attendance.show', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection