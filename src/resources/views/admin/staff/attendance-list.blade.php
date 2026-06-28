@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff-attendance-list.css') }}">
@endsection
@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <h2 class="attendance-list__title">
                {{ $user->name }}さんの勤怠
            </h2>
            <div class="attendance-list__month">
                <a
                    href="{{ route('admin.staff.attendance.list', ['id' => $user->id, 'month' => $previousMonth->format('Y-m')]) }}">
                    ← 前月
                </a>
                <span>
                    {{ $currentMonth->format('Y年m月') }}
                </span>
                <a
                    href="{{ route('admin.staff.attendance.list', ['id' => $user->id, 'month' => $nextMonth->format('Y-m')]) }}">
                    翌月 →
                </a>
            </div>
            <table class="attendance-list__table">
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach($dates as $dateItem)
                        @php
                            $date = $dateItem['date'];
                            $attendance = $dateItem['attendance'];
                            $breakMinutes = 0;
                            if ($attendance) {
                                foreach ($attendance->breakTimes as $breakTime) {
                                    if ($breakTime->start_time && $breakTime->end_time) {
                                        $breakMinutes += \Carbon\Carbon::parse($breakTime->start_time)
                                            ->diffInMinutes(\Carbon\Carbon::parse($breakTime->end_time));
                                    }
                                }
                            }
                            $workMinutes = null;
                            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                                $workMinutes =
                                    \Carbon\Carbon::parse($attendance->clock_in)
                                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out))
                                    - $breakMinutes;
                            }
                           @endphp
                        <tr>
                            <td>
                                {{ $date->format('m/d(D)') }}
                            </td>
                            <td>
                                {{ $attendance && $attendance->clock_in
                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                    : '' }}
                            </td>
                            <td>
                                {{ $attendance && $attendance->clock_out
                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                    : '' }}
                            </td>
                            <td>
                                @if($attendance)
                                    {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                                @endif
                            </td>
                            <td>
                                @if($workMinutes !== null)
                                    {{ floor($workMinutes / 60) }}:{{ str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                                @endif
                            </td>
                            <td>
                                @if($attendance)
                                    <a href="{{ route('admin.attendance.show', $attendance->id) }}">
                                        詳細
                                    </a>
                                @endif
                            </td>
                        </tr>
                @endforeach
            </table>
            <div class="attendance-list__csv">
                <a
                    href="{{ route('admin.staff.attendance.csv', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}">
                    CSV出力
                </a>
            </div>
        </div>
    </div>
@endsection