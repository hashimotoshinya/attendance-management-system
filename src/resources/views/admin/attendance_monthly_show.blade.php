@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/monthly_show.css') }}">
@endsection

@section('content')
<h2>{{ $user->name }}さんの勤怠一覧</h2>
<div class="container">
    <div class="month-switch">
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" class="btn-month">← 前月</a>
        <span class="current-month">{{ $currentMonth->format('Y年m月') }}</span>
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" class="btn-month">翌月 →</a>
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
                            <a href="{{ route('admin.attendance_show', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="csv-button-area">
    <form action="{{ route('admin.attendance.export_csv', ['id' => $user->id]) }}" method="GET">
        <input type="hidden" name="month" value="{{ $currentMonth->format('Y-m') }}">
        <button type="submit" class="csv-button">CSV出力</button>
    </form>
</div>
@endsection
