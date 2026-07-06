@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <h2 class="attendance-list__title">

                勤怠一覧
            </h2>
            <div class="attendance-list__month">
                <a href="{{ route('attendance.list', ['month' => $previousMonth->format('Y-m')]) }}">

                    ← 前月
                </a>
                <div class="attendance-list__current-month">

                    {{ $currentMonth->format('Y/m') }}
                </div>
                <a href="{{ route('attendance.list', ['month' => $nextMonth->format('Y-m')]) }}">

                    翌月 →
                </a>
            </div>
            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($dates as $date)
                        @php

                            $attendance = $attendances->get($date->format('Y-m-d'));

                            $clockIn = '';

                            $clockOut = '';

                            $breakTime = '';

                            $workingTime = '';

                            if ($attendance) {
                                $clockIn = $attendance->clock_in
                                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                                    : '';

                                $clockOut = $attendance->clock_out
                                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                                    : '';

                                $totalBreakMinutes = 0;

                                foreach ($attendance->breakTimes as $break) {
                                    if ($break->start_time && $break->end_time) {
                                        $totalBreakMinutes += \Carbon\Carbon::parse($break->start_time)->diffInMinutes(
                                            \Carbon\Carbon::parse($break->end_time),
                                        );
                                    }
                                }

                                if ($totalBreakMinutes > 0) {
                                    $breakTime =
                                        floor($totalBreakMinutes / 60) .
                                        ':' .
                                        str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
                                }

                                if ($attendance->clock_in && $attendance->clock_out) {
                                    $workingMinutes =
                                        \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes(
                                            \Carbon\Carbon::parse($attendance->clock_out),
                                        ) - $totalBreakMinutes;

                                    $workingTime =
                                        floor($workingMinutes / 60) .
                                        ':' .
                                        str_pad($workingMinutes % 60, 2, '0', STR_PAD_LEFT);
                                }
                            }

                        @endphp
                        <tr>
                            <td>

                                {{ $date->locale('ja')->isoFormat('MM/DD(ddd)') }}
                            </td>
                            <td>

                                {{ $clockIn }}
                            </td>
                            <td>

                                {{ $clockOut }}
                            </td>
                            <td>

                                {{ $breakTime }}
                            </td>
                            <td>

                                {{ $workingTime }}
                            </td>
                            <td>

                                @if ($attendance)
                                    <a href="{{ route('attendance.show', $attendance->id) }}">

                                        詳細
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
