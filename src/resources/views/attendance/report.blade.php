@extends('layouts.app')

@section('title', 'マイ勤怠レポート')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-report.css') }}">

@endsection

@section('content')
    <div class="report">
        <div class="report__inner">
            <h2 class="report__title">マイ勤怠レポート</h2>
            <p class="report__description">

                過去6ヶ月の勤怠データから集計しています。
            </p>
            <section class="report__section">
                <h3 class="report__section-title">基本サマリー</h3>
                <div class="report__summary">
                    <div class="report__card">
                        <p class="report__card-title">総労働時間</p>
                        <p class="report__card-value">

                            {{ floor($totalWorkMinutes / 60) }}h {{ $totalWorkMinutes % 60 }}m
                        </p>
                    </div>
                    <div class="report__card">
                        <p class="report__card-title">総残業時間</p>
                        <p class="report__card-value">

                            {{ floor($totalOvertimeMinutes / 60) }}h {{ $totalOvertimeMinutes % 60 }}m
                        </p>
                    </div>
                    <div class="report__card">
                        <p class="report__card-title">平均労働時間/日</p>
                        <p class="report__card-value">

                            {{ floor($averageWorkMinutes / 60) }}h {{ $averageWorkMinutes % 60 }}m
                        </p>
                    </div>
                </div>
            </section>
            <section class="report__section">
                <h3 class="report__section-title">月次推移（過去6ヶ月）</h3>
                <table class="report__table">
                    <tr>
                        <th>月</th>
                        <th>労働時間</th>
                        <th>残業時間</th>
                    </tr>

                    @foreach($monthlyReports as $report)
                        <tr>
                            <td>{{ $report['month'] }}</td>
                            <td>

                                {{ floor($report['work_minutes'] / 60) }}h {{ $report['work_minutes'] % 60 }}m
                            </td>
                            <td>

                                {{ floor($report['overtime_minutes'] / 60) }}h {{ $report['overtime_minutes'] % 60 }}m
                            </td>
                        </tr>

                    @endforeach
                </table>
            </section>
            <section class="report__section">
                <h3 class="report__section-title">今月の異常検知</h3>
                <div class="report__summary">
                    <div class="report__card">
                        <p class="report__card-title">遅刻回数</p>
                        <p class="report__card-value">{{ $lateCount }}回</p>
                    </div>
                    <div class="report__card">
                        <p class="report__card-title">早退回数</p>
                        <p class="report__card-value">{{ $earlyLeaveCount }}回</p>
                    </div>
                    <div class="report__card">
                        <p class="report__card-title">長時間労働回数</p>
                        <p class="report__card-value">{{ $longWorkCount }}回</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection