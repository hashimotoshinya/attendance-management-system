<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceCorrectRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now()->startOfDay();

        $attendances = Attendance::with('user')
            ->whereDate('date', $date->toDateString())
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance_list', [
            'attendances' => $attendances,
            'date' => $date,
        ]);
    }

    public function staffList()
    {
        $users = User::all();

        return view('admin.attendance_staff_list', compact('users'));
    }

    public function staffAttendance(Request $request, $id)
    {
        $month = $request->input('month');
        $currentMonth = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();

        $user = User::findOrFail($id);

        $dates = collect();
        for ($date = $currentMonth->copy(); $date->month === $currentMonth->month; $date->addDay()) {
            $dates->push($date->copy());
        }

        $attendancesRaw = Attendance::where('user_id', $id)
            ->whereBetween('date', [$currentMonth->copy()->startOfMonth(), $currentMonth->copy()->endOfMonth()])
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        return view('admin.attendance_monthly_show', [
            'user' => $user,
            'dates' => $dates,
            'attendances' => $attendancesRaw,
            'currentMonth' => $currentMonth,
        ]);
    }

    public function exportCsv($id, Request $request): StreamedResponse
    {
        $user = User::findOrFail($id);

        $month = $request->input('month') ?? now()->format('Y-m');
        $startOfMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
            return Carbon::parse($item->date)->toDateString();
            });

        $fileName = "{$user->name}_{$month}_attendance.csv";
        $encodedFileName = rawurlencode($fileName);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"; filename*=UTF-8''{$encodedFileName}",
        ];

        return Response::stream(function () use ($attendances, $startOfMonth, $endOfMonth) {
            $handle = fopen('php://output', 'w');

            echo "\xEF\xBB\xBF";

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
                $attendance = $attendances->get($date->toDateString());

                fputcsv($handle, [
                    $date->format('Y-m-d'),
                    $attendance && $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                    $attendance && $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                    $attendance->total_break_time ?? '',
                    $attendance->total_work_time ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function show($id)
    {
        $attendance = Attendance::with('breaks', 'user')->findOrFail($id);

        $correctionRequest = AttendanceCorrectRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        return view('admin.attendance_show', compact('attendance', 'correctionRequest'));
    }

    public function requestList(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $requests = AttendanceCorrectRequest::with('attendance.user')
            ->when($tab === 'pending', fn($q) => $q->where('status', 'pending'))
            ->when($tab === 'approved', fn($q) => $q->where('status', 'approved'))
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_request.request_list', compact('requests'));
    }
}
