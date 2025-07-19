@extends('layouts.staff_app')

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
                    <input type="time" name="start_time"
                        value="{{ old('start_time', \Carbon\Carbon::parse($attendance->start_time)->format('H:i')) }}">
                    〜
                    <input type="time" name="end_time"
                        value="{{ old('end_time', \Carbon\Carbon::parse($attendance->end_time)->format('H:i')) }}">
                    @error('start_time') <p class="error">{{ $message }}</p> @enderror
                    @error('end_time') <p class="error">{{ $message }}</p> @enderror
                </td>
            </tr>

            @foreach ($attendance->breaks as $index => $break)
            <tr>
                <th>休憩{{ $index + 1 }}</th>
                <td>
                    <input type="time" name="breaks[{{ $index }}][start_time]"
                        value="{{ old('breaks.$index.start_time', \Carbon\Carbon::parse($break->start_time)->format('H:i')) }}">
                    〜
                    <input type="time" name="breaks[{{ $index }}][end_time]"
                        value="{{ old('breaks.$index.end_time', \Carbon\Carbon::parse($break->end_time)->format('H:i')) }}">
                    <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                </td>
            </tr>
            @endforeach

            {{-- 追加用 --}}
            <tr>
                <th>休憩{{ $attendance->breaks->count() + 1 }}</th>
                <td>
                    <input type="time" name="breaks[{{ $attendance->breaks->count() }}][start_time]" {{ $editRequest ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="breaks[{{ $attendance->breaks->count() }}][end_time]" {{ $editRequest ? 'disabled' : '' }}>
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" {{ $editRequest ? 'disabled' : '' }}>{{ old('note', $attendance->note) }}</textarea>
                    @error('note') <p class="error">{{ $message }}</p> @enderror
                </td>
            </tr>
        </table>

        {{-- 修正ボタン表示／非表示 --}}
        @if (!$editRequest)
            <div class="btn-wrapper">
                <button type="submit" class="submit-btn">修正</button>
            </div>
        @endif
    </form>
    {{-- 承認待ち表示 --}}
    @if ($editRequest)
        <p class="error" style="color: red; font-weight: bold;">
            ※承認待ちのため修正はできません。
        </p>
    @endif
</div>
@endsection