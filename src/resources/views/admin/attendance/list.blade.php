@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/adminlist.css') }}">
@endsection

@section('title', '勤怠管理')
@section('content')
    <div class="admin-attendance">
        <div class="admin-attendance__inner">
            <h2 class="admin-attendance__title">
                {{ $currentDate->format('Y年n月j日') }}の勤怠
            </h2>
            <div class="admin-attendance__nav">
                <a href="{{ route('admin.attendance.list', ['date' => $previousDate->format('Y-m-d')]) }}">
                    ← 前日
                </a>
                <div class="admin-attendance__date">
                    {{ $currentDate->format('Y/m/d') }}
                </div>
                <a href="{{ route('admin.attendance.list', ['date' => $nextDate->format('Y-m-d')]) }}">
                    翌日 →
                </a>
            </div>
            <table class="admin-attendance__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        @php
                            $breakMinutes = 0;
                            foreach ($attendance->breakTimes as $break) {
                                if ($break->start_time && $break->end_time) {
                                    $breakMinutes += \Carbon\Carbon::parse($break->start_time)
                                        ->diffInMinutes(
                                            \Carbon\Carbon::parse($break->end_time)
                                        );
                                }
                            }
                            $workMinutes = null;
                            if ($attendance->clock_in && $attendance->clock_out) {
                                $workMinutes =
                                    \Carbon\Carbon::parse($attendance->clock_in)
                                        ->diffInMinutes(
                                            \Carbon\Carbon::parse($attendance->clock_out)
                                        ) - $breakMinutes;
                            }
                           @endphp
                        <tr>
                            <td>
                                {{ $attendance->user->name }}
                            </td>
                            <td>
                                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                            </td>
                            <td>
                                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                            </td>
                            <td>
                                {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                            </td>
                            <td>
                                @if ($workMinutes !== null)
                                    {{ floor($workMinutes / 60) }}:{{ str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.attendance.show', $attendance->id) }}">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection