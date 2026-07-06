@extends('layouts.app')
@section('title', '勤怠詳細')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection
@section('content')
    <div class="attendance">
        <div class="attendance__inner">
            <h2 class="attendance__title">勤怠詳細</h2>
            <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
                @csrf
                <table class="attendance-detail__table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                            <span></span>
                            {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="attendance-detail__time">
                                <input class="attendance-detail__input" type="text" name="start_time" value="{{ old(
        'start_time',
        $correction
        ? \Carbon\Carbon::parse($correction->clock_in)->format('H:i')
        : \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
    ) }}">
                                <span>～</span>
                                <input class="attendance-detail__input" type="text" name="end_time" value="{{ old(
        'end_time',
        $correction
        ? \Carbon\Carbon::parse($correction->clock_out)->format('H:i')
        : \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
    ) }}">
                            </div>
                            @error('start_time')
                                <p class="form__error">{{ $message }}</p>
                            @enderror
                            @error('end_time')
                                <p class="form__error">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                    @foreach($attendance->breakTimes as $index => $breakTime)
                                    @php
                                        $correctionBreak = $correction
                                            ? $correction->breakTimes->get($index)
                                            : null;
                                       @endphp
                                    <tr>
                                        <th>
                                            休憩{{ $index === 0 ? '' : $index + 1 }}
                                        </th>
                                        <td>
                                            <div class="attendance-detail__time">
                                                <input class="attendance-detail__input" type="text" name="breaks[{{ $index }}][start_time]"
                                                    value="{{ old(
                            'breaks.' . $index . '.start_time',
                            $correctionBreak
                            ? \Carbon\Carbon::parse($correctionBreak->start_time)->format('H:i')
                            : \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')
                        ) }}">
                                                <span>～</span>
                                                <input class="attendance-detail__input" type="text" name="breaks[{{ $index }}][end_time]"
                                                    value="{{ old(
                            'breaks.' . $index . '.end_time',
                            $correctionBreak
                            ? \Carbon\Carbon::parse($correctionBreak->end_time)->format('H:i')
                            : ($breakTime->end_time
                                ? \Carbon\Carbon::parse($breakTime->end_time)->format('H:i')
                                : '')
                        ) }}">
                                            </div>
                                            @error("breaks.$index.start_time")
                                                <p class="form__error">{{ $message }}</p>
                                            @enderror
                                            @error("breaks.$index.end_time")
                                                <p class="form__error">{{ $message }}</p>
                                            @enderror
                                        </td>
                                    </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea class="attendance-detail__textarea" name="remarks"
                                rows="4">{{ old('remarks', $correction ? $correction->remarks : '') }}</textarea>
                            @error('remarks')
                                <p class="form__error">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                </table>
                <div class="attendance-detail__button">
                    <button type="submit">
                        修正
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection