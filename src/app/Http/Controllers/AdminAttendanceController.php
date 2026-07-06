<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceCorrectionBreak;
use App\Http\Requests\AdminAttendanceRequest;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }

        $currentDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : today();
        $previousDate = $currentDate->copy()->subDay();
        $nextDate = $currentDate->copy()->addDay();
        $attendances = AttendanceRecord::with(['user', 'breakTimes'])
            ->whereDate('work_date', $currentDate)
            ->get();
        return view('admin.attendance.list', compact(
            'currentDate',
            'previousDate',
            'nextDate',
            'attendances'
        ));
    }

    public function show($id)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }

        $attendance = AttendanceRecord::with(['user', 'breakTimes'])
            ->findOrFail($id);
        $correction = $attendance->attendanceCorrection()->with('breakTimes')->latest()->first();

        return view('admin.attendance.show', compact('attendance', 'correction'));
    }

    public function update(AdminAttendanceRequest $request, $id)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }
        $attendance = AttendanceRecord::findOrFail($id);
        $correction = AttendanceCorrection::updateOrCreate(
            ['attendance_record_id' => $attendance->id],
            [
                'user_id' => $attendance->user_id,
                'work_date' => $attendance->work_date,
                'clock_in' => Carbon::parse($request->input('start_time')),
                'clock_out' => Carbon::parse($request->input('end_time')),
                'remarks' => $request->input('remarks'),
                'status' => 'pending',
            ]
        );
        foreach ($request->input('breaks', []) as $break) {
            $correction->breakTimes()->create([
                'start_time' => Carbon::parse($break['start_time']),
                'end_time' => Carbon::parse($break['end_time']),
            ]);
        }
        return redirect()->route('admin.attendance.show', $id);
    }

}