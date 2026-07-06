@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">

@endsection

@section('content')
    <div class="attendance">
        <div class="attendance__inner">
            <h2 class="attendance__title">勤怠詳細</h2>
            <table class="attendance-detail__table">
                <tr>
                    <th>名前</th>
                    <td>{{ $correction->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        {{ \Carbon\Carbon::parse($correction->work_date)->format('Y年') }}
                        <span></span>
                        {{ \Carbon\Carbon::parse($correction->work_date)->format('n月j日') }}
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="attendance-detail__time">
                            <input class="attendance-detail__input" type="text"
                                value="{{ \Carbon\Carbon::parse($correction->clock_in)->format('H:i') }}" readonly>
                            <span>～</span>
                            <input class="attendance-detail__input" type="text"
                                value="{{ \Carbon\Carbon::parse($correction->clock_out)->format('H:i') }}" readonly>
                        </div>
                    </td>
                </tr>

                @foreach($correction->breakTimes as $index => $breakTime)
                    <tr>
                        <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                        <td>
                            <div class="attendance-detail__time">
                                <input class="attendance-detail__input" type="text"
                                    value="{{ \Carbon\Carbon::parse($breakTime->start_time)->format('H:i') }}" readonly>
                                <span>～</span>
                                <input class="attendance-detail__input" type="text"
                                    value="{{ \Carbon\Carbon::parse($breakTime->end_time)->format('H:i') }}" readonly>
                            </div>
                        </td>
                    </tr>

                @endforeach
                <tr>
                    <th>備考</th>
                    <td>
                        <textarea class="attendance-detail__textarea" rows="4"
                            readonly>{{ $correction->remarks }}</textarea>
                    </td>
                </tr>
            </table>

            @if($correction->status === 'pending')
                <form action="{{ route('admin.correction.approve', $correction->id) }}" method="POST">

                    @csrf
                    <div class="attendance-detail__button">
                        <button type="submit" class="approve-button">承認</button>
                    </div>
                </form>

            @else
                <div class="attendance-detail__button">
                    <button type="button" class="approve-button approve-button--disabled" disabled>承認済み</button>
                </div>

            @endif
        </div>
    </div>

@endsection