<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;

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
}
