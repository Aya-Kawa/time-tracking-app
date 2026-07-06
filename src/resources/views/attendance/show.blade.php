@extends('layouts.app')

@section('title', '勤怠詳細')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endsection

@section('content')
    <div class="attendance">
        <div class="attendance__inner">
            <h2 class="attendance__title">勤怠詳細</h2>

            <form action="{{ route('attendance.correction.store', $attendance->id) }}" method="POST">
                @csrf

                <table class="attendance-detail__table">
                    <tr>
                        <th>名前</th>
                        <td>{{ Auth::user()->name }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            {{\Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                            <span></span>
                            {{\Carbon\Carbon::parse($attendance->work_date)->format('n月j日')}}
                        </td>

                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="attendance-detail__time">
                                <input class="attendance-detail__input" type="text" name="start_time"
                                    value="{{ old('start_time', $pendingCorrection ? \Carbon\Carbon::parse($pendingCorrection->clock_in)->format('H:i') : \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}"
                                    @if($pendingCorrection) readonly @endif>
                                <span>～</span>
                                <input class="attendance-detail__input" type="text" name="end_time"
                                    value="{{ old('end_time', $pendingCorrection ? \Carbon\Carbon::parse($pendingCorrection->clock_out)->format('H:i') : \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}"
                                    @if($pendingCorrection) readonly @endif>
                                @foreach($errors->get('start_time') as $message)
                                    <p class="form__error">{{ $message }}</p>
                                @endforeach
                                @foreach($errors->get('end_time') as $message)
                                    <p class="form__error">{{ $message }}</p>
                                @endforeach
                            </div>
                        </td>
                    </tr>

                    @foreach($attendance->breakTimes as $index => $breakTime)
                        @php
                            $correctionBreak = $pendingCorrection ? $pendingCorrection->breakTimes()->where('id', $breakTime->id)->first() : null;
                        @endphp

                        <tr>
                            <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                            <td>
                                <div class="attendance-detail__time">
                                    <input class="attendance-detail__input" type="text" name="breaks[{{ $index }}][start_time]"
                                        value="{{ old('breaks.' . $index . '.start_time', $correctionBreak ? \Carbon\Carbon::parse($correctionBreak->start_time)->format('H:i') : \Carbon\Carbon::parse($breakTime->start_time)->format('H:i')) }}"
                                        @if($pendingCorrection) readonly @endif>
                                    <span>～</span>
                                    <input class="attendance-detail__input" type="text" name="breaks[{{ $index }}][end_time]"
                                        value="{{ old('breaks.' . $index . '.end_time', $correctionBreak ? \Carbon\Carbon::parse($correctionBreak->end_time)->format('H:i') : \Carbon\Carbon::parse($breakTime->end_time)->format('H:i')) }}"
                                        @if($pendingCorrection) readonly @endif>
                                    @foreach($errors->get("breaks.$index.start_time") as $message)
                                        <p class="form__error">{{ $message }}</p>
                                    @endforeach
                                    @foreach($errors->get("breaks.$index.end_time") as $message)
                                        <p class="form__error">{{ $message }}</p>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <th>備考</th>
                        <td>
                            <div class="attendance-detail__remarks">
                                <textarea class="attendance-detail__textarea" name="remarks" rows="4"
                                    @if($pendingCorrection) readonly
                                    @endif>{{ old('remarks', $pendingCorrection ? $pendingCorrection->remarks : '') }}</textarea>
                                @foreach($errors->get('remarks') as $message)
                                    <p class="form__error">{{ $message }}</p>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                </table>

                @if ($pendingCorrection)
                    <p class="attendance-detail__message">
                        *承認待ちのため修正はできません
                    </p>
                @else
                    <div class="attendance-detail__button">
                        <button type="submit">修正</button>
                    </div>
                @endif
            </form>
        </div>
    </div>

@endsection