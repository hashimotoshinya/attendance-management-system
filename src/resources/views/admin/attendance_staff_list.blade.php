@extends('layouts.attendance_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('content')
<div class="staff-container">
    <h2 class="title">スタッフ一覧</h2>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                @if(session('login_type') === 'admin')
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><a href="{{ url('/admin/attendance/staff/' . $user->id) }}">詳細</a></td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endsection