@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <h2 class="attendance-list__title">勤怠一覧</h2>

            <div class="attendance-list__month">
                <a href="{{ route('attendance.list', ['month' => $previousMonth->format('Y-m')]) }}">←前月</a>
                <span>{{ $currentMonth->format('Y/m') }}</span>
                <a href="{{ route('attendance.list', ['month' => $nextMonth->format('Y-m')]) }}">翌月→</a>
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

                @foreach ($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->display_date }}</td>
                        <td>{{ $attendance->display_clock_in }}</td>
                        <td>{{ $attendance->display_clock_out }}</td>
                        <td>{{ $attendance->display_break_time }}</td>
                        <td>{{ $attendance->display_working_time }}</td>
                        <td><a href="{{ route('attendance.show', $attendance->id) }}">詳細</a></td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection