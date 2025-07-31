<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['status' => '勤務外']
        );

        return view('attendance.index', [
            'status' => $attendance->status,
        ]);
    }

    public function start()
    {
        $attendance = $this->todayAttendance();
        $attendance->update([
            'start_time' => now(),
            'status' => '出勤中'
        ]);
        return redirect()->route('attendance.index');
    }

    public function breakStart()
    {
        $attendance = $this->todayAttendance();

        $attendance->breaks()->create([
            'start_time' => now(),
        ]);

        $attendance->status = '休憩中';
        $attendance->save();

        return redirect()->route('attendance.index');
    }

    public function breakEnd()
    {
        $attendance = $this->todayAttendance();

        $lastBreak = $attendance->breaks()->whereNull('end_time')->latest()->first();

        if ($lastBreak) {
            $lastBreak->end_time = now();
            $lastBreak->save();

            $start = Carbon::parse($lastBreak->start_time);
            $end = Carbon::parse($lastBreak->end_time);
            $diffInSeconds = $start->diffInSeconds($end);

            $existing = $attendance->total_break_time ?? 0;
            $attendance->total_break_time = (int) $existing + $diffInSeconds;
            $attendance->status = '出勤中';
            $attendance->save();
        }

        return redirect()->route('attendance.index');
    }

    public function end()
    {
        $attendance = $this->todayAttendance();
        $attendance->update([
            'end_time' => now(),
            'status' => '退勤済'
        ]);
        return redirect()->route('attendance.index');
    }

    private function todayAttendance()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        return Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['status' => '勤務外']
        );
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $currentMonth = $request->input('month') ? Carbon::parse($request->input('month')) : now()->startOfMonth();
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });
        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        return view('attendance.list', [
            'attendances' => $attendances,
            'dates' => $dates,
            'currentMonth' => $currentMonth,
            'previousMonth' => $currentMonth->copy()->subMonth(),
            'nextMonth' => $currentMonth->copy()->addMonth(),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with('breaks', 'user')->findOrFail($id);

        $correctionRequest = AttendanceCorrectRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        if ($correctionRequest) {
            $attendance->start_time = $correctionRequest->start_time;
            $attendance->end_time   = $correctionRequest->end_time;
            $attendance->note       = $correctionRequest->note;
            $attendance->breaks     = is_array($correctionRequest->breaks)
                ? $correctionRequest->breaks
                : [];
            $attendance->is_pending = true;
        } else {
            $attendance->is_pending = false;
        }

        return view('attendance.show', compact('attendance', 'correctionRequest'));
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $existing = AttendanceCorrectRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        $breaks = array_filter($request->breaks, function ($b) {
            return $b['start_time'] || $b['end_time'];
        });

        AttendanceCorrectRequest::updateOrCreate(
            ['attendance_id' => $attendance->id],
            [
                'start_time' => $request->start_time,
                'end_time'   => $request->end_time,
                'note'    => $request->note,
                'breaks'     => array_values($breaks),
                'status'     => 'pending',
            ]
        );

        $loginType = session('login_type');

        if ($loginType === 'admin') {
            return redirect()
                ->route('admin.attendance.list', ['id' => $attendance->user_id])
                ->with('status', '修正申請が送信されました（承認待ち）');
        }

        return redirect()->route('attendance.show', ['id' => $attendance->id])
            ->with('status', '修正申請が送信されました（承認待ち）');
    }
}
