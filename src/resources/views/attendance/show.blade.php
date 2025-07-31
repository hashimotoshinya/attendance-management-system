@extends('layouts.attendance_app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">

<div class="detail-container">
    <h2 class="title">勤怠詳細</h2>

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
            </tr>

            <tr>
                <th>出勤・退勤</th>
                <td>
                    @if (!$correctionRequest)
                        <input type="time" name="start_time"
                            value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}">
                        〜
                        <input type="time" name="end_time"
                            value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}">
                        @error('start_time') <p class="error">{{ $message }}</p> @enderror
                        @error('end_time') <p class="error">{{ $message }}</p> @enderror
                    @else
                        {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}
                        〜
                        {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
                    @endif
                </td>
            </tr>

            @php
                $breaks = old('breaks')
                    ?? ($correctionRequest ? $correctionRequest->breaks : ($attendance->breaks->toArray() ?? []));

                if (!is_array($breaks) || empty($breaks)) {
                    $breaks = [];
                }
            @endphp

            @foreach ($breaks as $index => $break)
            <tr>
                <th>休憩{{ $index + 1 }}</th>
                <td>
                    @if (!$correctionRequest)
                        <input type="time" name="breaks[{{ $index }}][start_time]"
                            value="{{ old("breaks.$index.start_time", isset($break['start_time']) ? \Carbon\Carbon::parse($break['start_time'])->format('H:i') : '') }}">
                        〜
                        <input type="time" name="breaks[{{ $index }}][end_time]"
                            value="{{ old("breaks.$index.end_time", isset($break['end_time']) ? \Carbon\Carbon::parse($break['end_time'])->format('H:i') : '') }}">
                        @error("breaks.$index.start_time") <p class="error">{{ $message }}</p> @enderror
                        @error("breaks.$index.end_time") <p class="error">{{ $message }}</p> @enderror
                    @else
                        {{ \Carbon\Carbon::parse($break['start_time'])->format('H:i') }}
                        〜
                        {{ \Carbon\Carbon::parse($break['end_time'])->format('H:i') }}
                    @endif
                </td>
            </tr>
            @endforeach

            @if (!$correctionRequest)
                @php $nextIndex = count(old('breaks', $attendance->breaks ?? [])) @endphp
                <tr>
                    <th>休憩{{ $nextIndex + 1 }}</th>
                    <td>
                        <input type="time" name="breaks[{{ $nextIndex }}][start_time]"
                            value="{{ old("breaks.$nextIndex.start_time", '') }}">
                        〜
                        <input type="time" name="breaks[{{ $nextIndex }}][end_time]"
                            value="{{ old("breaks.$nextIndex.end_time", '') }}">
                        @error("breaks.$nextIndex.start_time") <p class="error">{{ $message }}</p> @enderror
                        @error("breaks.$nextIndex.end_time") <p class="error">{{ $message }}</p> @enderror
                    </td>
                </tr>
            @endif

            <tr>
                <th>備考</th>
                <td>
                    @if (!$correctionRequest)
                        <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                        @error('note') <p class="error">{{ $message }}</p> @enderror
                    @else
                        {{ $attendance->note }}
                    @endif
                </td>
            </tr>
        </table>

        @if (!$correctionRequest)
            <div class="btn-wrapper">
                <button type="submit" class="submit-btn">修正</button>
            </div>
        @else
            <p class="error" style="color: red; font-weight: bold;">
                ※承認待ちのため修正はできません。
            </p>
        @endif
    </form>
</div>
@endsection