<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\AttendanceEditRequest;
use App\Models\User;

class AttendanceEditRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $loginType = session('login_type');

        if ($loginType === 'admin') {
            $pendingRequests = AttendanceEditRequest::where('status', 'pending')->with('attendance.user')->latest()->get();
            $approvedRequests = AttendanceEditRequest::where('status', 'approved')->latest()->get();
        } else {
            $pendingRequests = AttendanceEditRequest::whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'pending')->latest()->get();

            $approvedRequests = AttendanceEditRequest::whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'approved')->with('attendance.user')->latest()->get();
        }

        return view('stamp_correction_request.list', [
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'activeTab' => 'pending',
        ]);
    }
}