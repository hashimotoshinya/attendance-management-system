@extends('layouts.attendance_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/approve.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">勤怠詳細</h1>

    <div class="detail-container">
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $correctionRequest->attendance->user->name ?? '' }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($correctionRequest->attendance->date)->format('Y年n月j日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="start_time"
                        value="{{ \Carbon\Carbon::parse($correctionRequest->start_time ?? $correctionRequest->attendance->start_time)->format('H:i') }}"
                        disabled>
                    〜
                    <input type="time" name="end_time"
                        value="{{ \Carbon\Carbon::parse($correctionRequest->end_time ?? $correctionRequest->attendance->end_time)->format('H:i') }}"
                        disabled>
                </td>
            </tr>

            @php
                $correctionBreaks = $correctionRequest->breaks;
                $hasCorrectionBreaks = is_array($correctionBreaks) && count($correctionBreaks) > 0;

                if ($hasCorrectionBreaks) {
                    $breaks = $correctionBreaks;
                } else {
                    $breaks = $correctionRequest->attendance->breaks->map(function ($b) {
                        return [
                            'start_time' => $b->start_time,
                            'end_time' => $b->end_time,
                        ];
                    })->toArray();
                }

                if (empty($breaks)) {
                    $breaks[] = ['start_time' => null, 'end_time' => null];
                }
            @endphp

            @foreach ($breaks as $index => $break)
            <tr>
                <th>休憩{{ $index + 1 }}</th>
                <td>
                    <input type="time" name="breaks[{{ $index }}][start_time]"
                        value="{{ !empty($break['start_time']) ? \Carbon\Carbon::parse($break['start_time'])->format('H:i') : '' }}"
                        disabled>
                    〜
                    <input type="time" name="breaks[{{ $index }}][end_time]"
                        value="{{ !empty($break['end_time']) ? \Carbon\Carbon::parse($break['end_time'])->format('H:i') : '' }}"
                        disabled>
                </td>
            </tr>
            @endforeach

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" disabled>{{ $correctionRequest->note ?? $correctionRequest->attendance->note }}</textarea>
                </td>
            </tr>
        </table>
    </div>

    @if ($correctionRequest->status === 'approved')
        <button class="submit-btn approved-btn" disabled>承認済み</button>
    @else
        <form action="{{ route('stamp_correction_request.approve', $correctionRequest->id) }}" method="POST">
            @csrf
            @method('PUT')
            <button type="submit" class="submit-btn">承認する</button>
        </form>
    @endif
</div>
@endsection