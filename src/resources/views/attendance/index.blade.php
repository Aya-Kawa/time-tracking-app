@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{asset('css/attendance.css')}}">
@endsection

@section('content')
    <div class="attendance">
        <div class="attendance__innner">

            @if(!$attendance)
                <p class="attendance__status">勤務外</p>
            @elseif($attendance->clock_in && $attendance->breakTimes()->whereNull('end_time')->exists())
                <p class="attendance__status">休憩中</p>
            @elseif($attendance->clock_in && !$attendance->clock_out)
                <p class="attendance__status">出勤中</p>
            @elseif($attendance->clock_out)
                <p class="attendance__status">退勤済み</p>

            @endif


            <div>
                <p class="attendance__date">{{ now()->locale('ja')->isoformat('Y年M月D日(ddd)')}} </p>
                <p class="attendance__time">{{ now()->format('H:i') }}</p>
            </div>

            @if(!$attendance)
                <form action="{{ route('attendance.clock-in') }}" method="POST">
                    @csrf
                    <button class="attendance__button" type="submit">出勤</button>
                </form>
            @elseif($attendance->clock_in && $attendance->breakTimes()->whereNull('end_time')->exists())
                <form action="{{ route('attendance.break-end') }}" method="POST">
                    @csrf
                    <button class="attendance__button--white" type="submit">休憩戻</button>
                </form>
            @elseif($attendance->clock_in && !$attendance->clock_out)
                <div class="attendance__actions">
                    <form action="{{ route('attendance.clock-out') }}" method="POST">
                        @csrf
                        <button class="attendance__button" type="submit">退勤</button>
                    </form>

                    <form action="{{ route('attendance.break-start') }}" method="POST">
                        @csrf
                        <button class="attendance__button--white" type="submit">休憩入</button>
                    </form>
                </div>
            @elseif($attendance->clock_out)
                <p class="attendance__message">お疲れ様でした。</p>
            @endif
        </div>
    </div>

@endsection