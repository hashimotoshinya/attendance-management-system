<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrectRequest;

class AttendanceCorrectRequestController extends Controller
{
    /**
     * 一覧画面表示（承認待ち・承認済）
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $loginType = session('login_type');

        if ($loginType === 'admin') {
            $pendingRequests = AttendanceCorrectRequest::where('status', 'pending')
                ->with('attendance.user')
                ->latest()
                ->get();

            $approvedRequests = AttendanceCorrectRequest::where('status', 'approved')
                ->with('attendance.user')
                ->latest()
                ->get();
        } else {
            $pendingRequests = AttendanceCorrectRequest::whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->with('attendance.user')
                ->where('status', 'pending')
                ->latest()
                ->get();

            $approvedRequests = AttendanceCorrectRequest::whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
                ->where('status', 'approved')
                ->with('attendance.user')
                ->latest()
                ->get();
        }

        return view('stamp_correction_request.list', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'activeTab' => 'pending',
        ]);
    }

    /**
     * 詳細表示画面（管理者用承認前確認）
     */
    public function show($id)
    {
        $correctionRequest = AttendanceCorrectRequest::with('attendance.user')->findOrFail($id);

        return view('stamp_correction_request.approve', [
            'correctionRequest' => $correctionRequest,
            'attendance' => $correctionRequest->attendance,
        ]);
    }

    /**
     * 承認処理（PUT）
     */
    public function approve(Request $request, $id)
    {
        $correctionRequest = AttendanceCorrectRequest::with('attendance')->findOrFail($id);

        if ($correctionRequest->status === 'approved') {
            return redirect()
                ->route('stamp_correction_request.show', $id)
                ->with('error', 'すでに承認済みです。');
        }

        // 勤怠データ更新（例として start_time のみ）
        $attendance = $correctionRequest->attendance;
        if ($correctionRequest->start_time) {
            $attendance->start_time = $correctionRequest->start_time;
        }
        if ($correctionRequest->end_time) {
            $attendance->end_time = $correctionRequest->end_time;
        }
        if ($correctionRequest->break_time) {
            $attendance->breaks = $correctionRequest->breaks;
        }
        if ($correctionRequest->note) {
            $attendance->note = $correctionRequest->note;
        }
        $attendance->save();

        // ステータス変更
        $correctionRequest->status = 'approved';
        $correctionRequest->save();

        return redirect()
            ->route('stamp_correction_request.show', $id)
            ->with('success', '修正申請を承認しました。');
    }
}