<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Auth;

class AdminCorrectionController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }
        $status = $request->input('status', 'pending');
        $corrections = AttendanceCorrection::with(['user', 'attendanceRecord'])
            ->where('status', $status)
            ->get();
        return view('admin.correction.index', compact(
            'corrections',
            'status'
        ));
    }

    public function show($id)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }
        $correction = AttendanceCorrection::with([
            'user',
            'breakTimes',
            'attendanceRecord'
        ])->findOrFail($id);
        return view('admin.correction.show', compact('correction'));
    }

    public function approve($id)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }
        $correction = AttendanceCorrection::with([
            'attendanceRecord',
            'breakTimes'
        ])->findOrFail($id);

        $attendance = $correction->attendanceRecord;
        $attendance->update([
            'clock_in' => $correction->clock_in,
            'clock_out' => $correction->clock_out,
        ]);

        foreach ($correction->breakTimes as $index => $correctionBreak) {
            $breakTime = $attendance->breakTimes[$index] ?? null;
            if ($breakTime) {
                $breakTime->update([
                    'start_time' => $correctionBreak->start_time,
                    'end_time' => $correctionBreak->end_time,
                ]);
            } else {
                $attendance->breakTimes()->create([
                    'start_time' => $correctionBreak->start_time,
                    'end_time' => $correctionBreak->end_time,
                ]);
            }
        }
        $correction->update([
            'status' => 'approved',
        ]);
        return redirect()->route('admin.correction.index', [
            'status' => 'approved',
        ]);
    }
}
