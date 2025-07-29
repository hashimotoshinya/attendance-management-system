<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrectRequest;

class AttendanceCorrectRequestController extends Controller
{
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

    public function show($id)
    {
        $correctionRequest = AttendanceCorrectRequest::with('attendance.user')->findOrFail($id);

        return view('stamp_correction_request.approve', [
            'correctionRequest' => $correctionRequest,
            'attendance' => $correctionRequest->attendance,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $correctionRequest = AttendanceCorrectRequest::with('attendance')->findOrFail($id);

        if ($correctionRequest->status === 'approved') {
            return redirect()
                ->route('stamp_correction_request.show', $id)
                ->with('error', 'すでに承認済みです。');
        }

        $attendance = $correctionRequest->attendance;

        if ($correctionRequest->start_time) {
            $attendance->start_time = $correctionRequest->start_time;
        }
        if ($correctionRequest->end_time) {
            $attendance->end_time = $correctionRequest->end_time;
        }
        if ($correctionRequest->note) {
            $attendance->note = $correctionRequest->note;
        }
        $attendance->save();

        $attendance->breaks()->delete();

        $breaks = json_decode($correctionRequest->breaks, true) ?? [];
        foreach ($breaks as $break) {
            if (!empty($break['start_time']) && !empty($break['end_time'])) {
                $attendance->breaks()->create([
                    'start_time' => $break['start_time'],
                    'end_time' => $break['end_time'],
                ]);
            }
        }

        $correctionRequest->status = 'approved';
        $correctionRequest->save();

        return redirect()
            ->route('stamp_correction_request.show', $id)
            ->with('success', '修正申請を承認しました。');
    }
}