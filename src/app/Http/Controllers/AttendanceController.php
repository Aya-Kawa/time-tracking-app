<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->whereDate('work_date', today())->first();
        return view('attendance.index', compact('attendance'));
    }

    public function clockIn()
    {
        AttendanceRecord::create([
            'user_id' => Auth::id(),
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakStart()
    {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->whereDate('work_date', today())->first();
        $attendance->breakTimes()->create([
            'start_time' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakEnd()
    {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->whereDate('work_date', today())->first();

        $breakTime = $attendance->breakTimes()->whereNull('end_time')->first();

        $breakTime->update([
            'end_time' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->whereDate('work_date', today())->first();

        if ($attendance->clock_out) {
            return redirect()->route('attendance.index');
        }
        $attendance->update([
            'clock_out' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /* 勤怠一覧画面 */
    public function list(Request $request)
    {
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();

        $previousMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        $attendances = AttendanceRecord::where('user_id', Auth::id())
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->get();

        foreach ($attendances as $attendance) {
            $attendance->display_date = Carbon::parse($attendance->work_date)->locale('ja')->isoformat('MM/DD (ddd)');
            $attendance->display_clock_in = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '';
            $attendance->display_clock_out = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '';

            $totalBreakMinutes = 0;
            foreach ($attendance->breakTimes as $breakTime) {
                if ($breakTime->start_time && $breakTime->end_time) {
                    $totalBreakMinutes += Carbon::parse($breakTime->start_time)->diffInMinutes(Carbon::parse($breakTime->end_time));
                }

            }
            $attendance->display_break_time = floor($totalBreakMinutes / 60) . ':' . str_pad($totalBreakMinutes % 60, 2, 0, STR_PAD_LEFT);

            $workingMinutes = 0;
            if ($attendance->clock_in && $attendance->clock_out) {
                $workingMinutes = Carbon::parse($attendance->clock_in)->diffInMinutes(Carbon::parse($attendance->clock_out)) - $totalBreakMinutes;
            }
            $actualWorkingMinutes = $workingMinutes - $totalBreakMinutes;
            $attendance->display_working_time = floor($actualWorkingMinutes / 60) . ':' . str_pad($actualWorkingMinutes % 60, 2, 0, STR_PAD_LEFT);
        }
        return view('attendance.list', compact('currentMonth', 'previousMonth', 'nextMonth', 'attendances'));
    }

    /* 勤怠詳細画面 */
    public function show($id)
    {
        $attendance = AttendanceRecord::with('breakTimes')->where('user_id', Auth::id())->findOrFail($id);
        $pendingCorrection = $attendance->attendanceCorrection()->where('status', 'pending')->first();
        return view('attendance.show', compact('attendance', 'pendingCorrection'));
    }

    public function storeCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = AttendanceRecord::where('user_id', Auth::id())->findOrFail($id);

        $pendingCorrection = $attendance->attendanceCorrection()->where('status', 'pending')->exists();
        if ($pendingCorrection) {
            return redirect()->route('attendance.show', $id);
        }

        $correction = AttendanceCorrection::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => Auth::id(),
            'work_date' => $attendance->work_date,
            'clock_in' => Carbon::parse($request->input('start_time')),
            'clock_out' => Carbon::parse($request->input('end_time')),
            'remarks' => $request->input('remarks'),
            'status' => 'pending',
        ]);

        foreach ($request->breaks as $break) {
            $correction->breakTimes()->create([
                'start_time' => Carbon::parse($break['start_time']),
                'end_time' => Carbon::parse($break['end_time']),
            ]);
        }
        return redirect()->route('attendance.show', $id);
    }
}
