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

   $attendances = AttendanceRecord::with('breakTimes')
       ->where('user_id', Auth::id())
       ->whereYear('work_date', $currentMonth->year)
       ->whereMonth('work_date', $currentMonth->month)
       ->get()
       ->keyBy(function ($attendance) {
           return Carbon::parse($attendance->work_date)->format('Y-m-d');
       });

   $dates = [];

   $startDate = $currentMonth->copy()->startOfMonth();
   $endDate = $currentMonth->copy()->endOfMonth();

   for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
       $dates[] = $date->copy();
   }

   return view('attendance.list', compact(
       'currentMonth',
       'previousMonth',
       'nextMonth',
       'attendances',
       'dates'
   ));
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

        foreach ($request->input('breaks',[]) as $break) {
            $correction->breakTimes()->create([
                'start_time' => Carbon::parse($break['start_time']),
                'end_time' => Carbon::parse($break['end_time']),
            ]);
        }
        return redirect()->route('attendance.show', $id);
    }

    public function report()
    {
        $user = Auth::user();
        $startMonth = Carbon::now()->copy()->subMonths(5)->startOfMonth();
        $endMonth = Carbon::now()->copy()->endOfMonth();
        $attendances = AttendanceRecord::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startMonth, $endMonth])
            ->get();

        $totalWorkMinutes = 0;
        $totalOvertimeMinutes = 0;
        $workDays = 0;
        $monthlyReports = [];
        $lateCount = 0;
        $earlyLeaveCount = 0;
        $longWorkCount = 0;
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->copy()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $monthlyReports[$monthKey] = [
                'month' => $monthKey,
                'work_minutes' => 0,
                'overtime_minutes' => 0,
            ];
        }

        foreach ($attendances as $attendance) {
            if (!$attendance->clock_in || !$attendance->clock_out) {
                continue;
            }

            $breakMinutes = 0;

            foreach ($attendance->breakTimes as $breakTime) {
                if ($breakTime->start_time && $breakTime->end_time) {
                    $breakMinutes += Carbon::parse($breakTime->start_time)
                        ->diffInMinutes(Carbon::parse($breakTime->end_time));
                }
            }

            $workMinutes = Carbon::parse($attendance->clock_in)
                ->diffInMinutes(Carbon::parse($attendance->clock_out))
                - $breakMinutes;

            $overtimeMinutes = max(0, $workMinutes - 480);
            $totalWorkMinutes += $workMinutes;
            $totalOvertimeMinutes += $overtimeMinutes;
            $workDays++;
            $monthKey = Carbon::parse($attendance->work_date)->format('Y-m');

            if (isset($monthlyReports[$monthKey])) {
                $monthlyReports[$monthKey]['work_minutes'] += $workMinutes;
                $monthlyReports[$monthKey]['overtime_minutes'] += $overtimeMinutes;
            }

            if (
                Carbon::parse($attendance->work_date)->isSameMonth(Carbon::now()) &&
                Carbon::parse($attendance->work_date)->isSameYear(Carbon::now())
            ) {

                if (Carbon::parse($attendance->clock_in)->format('H:i') > '09:00') {
                    $lateCount++;
                }

                if (Carbon::parse($attendance->clock_out)->format('H:i') < '18:00') {
                    $earlyLeaveCount++;
                }

                if ($workMinutes > 600) {
                    $longWorkCount++;
                }
            }
        }

        $averageWorkMinutes = $workDays > 0
            ? floor($totalWorkMinutes / $workDays)
            : 0;

        return view('attendance.report', compact(
            'user',
            'totalWorkMinutes',
            'totalOvertimeMinutes',
            'averageWorkMinutes',
            'monthlyReports',
            'lateCount',
            'earlyLeaveCount',
            'longWorkCount'
        ));
    }
}
