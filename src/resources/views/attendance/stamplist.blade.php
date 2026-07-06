@extends('layouts.app')

@section('title', '申請一覧')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/stamplist.css') }}">
@endsection

@section('content')
    <div class="request-list">

        <div class="request-list__inner">
            <h2 class="request-list__title">申請一覧</h2>

            <div class="request-list__tabs">
                <a href="{{route('stamp_correction_request.index', ['status' => 'pending'])}}"
                    class="{{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
                <a href="{{route('stamp_correction_request.index', ['status' => 'approved'])}}"
                    class="{{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
            </div>

            <table class="request-list__table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($requests as $correction)
                        <tr>
                            <td>{{ $correction->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                            <td>{{ $correction->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($correction->attendanceRecord->work_date)->format('Y/m/d') }}</td>
                            <td>{{ $correction->remarks }}</td>
                            <td>{{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('attendance.show', $correction->attendance_record_id) }}"
                                    class="btn btn-primary">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>



        </div>
    </div>

@endsection