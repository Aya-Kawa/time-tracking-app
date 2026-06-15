<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AttendanceCorrectionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = AttendanceCorrection::where('user_id', Auth::id())->where('status', $status)->orderByDesc('created_at')->get();

        return view('attendance.stamplist', compact('status', 'requests'));
    }
}
