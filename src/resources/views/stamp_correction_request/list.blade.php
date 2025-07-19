@extends('layouts.staff_app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">

<div class="container">
    <h1 class="title">申請一覧</h1>

    <div class="tabs">
        <button class="tab-button {{ $activeTab === 'pending' ? 'active' : '' }}" onclick="switchTab('pending')">承認待ち</button>
        <button class="tab-button {{ $activeTab === 'approved' ? 'active' : '' }}" onclick="switchTab('approved')">承認済み</button>
    </div>

    <div id="pending-tab" class="tab-content" style="{{ $activeTab === 'pending' ? '' : 'display:none;' }}">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingRequests as $request)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $request->attendance->user->name }}</td>
                    <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.show', $request->attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="approved-tab" class="tab-content" style="{{ $activeTab === 'approved' ? '' : 'display:none;' }}">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($approvedRequests as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->attendance->user->name }}</td>
                    <td>{{ $request->attendance->date->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ $request->updated_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.show', $request->attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    function switchTab(tab) {
        document.getElementById('pending-tab').style.display = tab === 'pending' ? 'block' : 'none';
        document.getElementById('approved-tab').style.display = tab === 'approved' ? 'block' : 'none';

        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`.tab-button[onclick="switchTab('${tab}')"]`).classList.add('active');
    }
</script>
@endsection