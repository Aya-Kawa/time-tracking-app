<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AttendanceRecord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffController extends Controller
{
    public function index()
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }
        $users = User::where('admin_status', 0)
            ->get();
        return view('admin.staff.list', compact('users'));
    }


    public function attendanceList(Request $request, $id)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
        $attendances = AttendanceRecord::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });
        $dates = [];
        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();
        while ($startDate <= $endDate) {
            $dateKey = $startDate->format('Y-m-d');
            $dates[] = [
                'date' => $startDate->copy(),
                'attendance' => $attendances->get($dateKey),
            ];
            $startDate->addDay();
        }
        return view('admin.staff.attendance-list', compact(
            'user',
            'currentMonth',
            'previousMonth',
            'nextMonth',
            'dates'
        ));
    }

    public function exportCsv(Request $request, $id)
    {
        if (!Auth::user()->admin_status) {
            abort(403);
        }
        $user = User::findOrFail($id);
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now();
        $attendances = AttendanceRecord::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });
        $fileName = $user->name . '_' . $currentMonth->format('Y_m') . '_attendance.csv';
        $response = new StreamedResponse(function () use ($currentMonth, $attendances) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);
            $startDate = $currentMonth->copy()->startOfMonth();
            $endDate = $currentMonth->copy()->endOfMonth();
            while ($startDate <= $endDate) {
                $dateKey = $startDate->format('Y-m-d');
                $attendance = $attendances->get($dateKey);
                $breakMinutes = 0;
                $workMinutes = null;
                if ($attendance) {
                    foreach ($attendance->breakTimes as $breakTime) {
                        if ($breakTime->start_time && $breakTime->end_time) {
                            $breakMinutes += Carbon::parse($breakTime->start_time)
                                ->diffInMinutes(Carbon::parse($breakTime->end_time));
                        }
                    }
                    if ($attendance->clock_in && $attendance->clock_out) {
                        $workMinutes = Carbon::parse($attendance->clock_in)
                            ->diffInMinutes(Carbon::parse($attendance->clock_out))
                            - $breakMinutes;
                    }
                }
                fputcsv($handle, [
                    $startDate->format('Y/m/d'),
                    $attendance && $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance && $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                    $attendance ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
                    $workMinutes !== null ? floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
                ]);
                $startDate->addDay();
            }
            fclose($handle);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        return $response;
    }
}
