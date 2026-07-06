@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/correction-list.css') }}">

@endsection

@section('content')
    <div class="correction-list">
        <div class="correction-list__inner">
            <h2 class="correction-list__title">申請一覧</h2>
            <div class="correction-list__tabs">
                <a href="{{ route('admin.correction.index', ['status' => 'pending']) }}"
                    class="{{ $status === 'pending' ? 'active' : '' }}">

                    承認待ち
                </a>
                <a href="{{ route('admin.correction.index', ['status' => 'approved']) }}"
                    class="{{ $status === 'approved' ? 'active' : '' }}">

                    承認済み
                </a>
            </div>
            <table class="correction-list__table">
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>

                @foreach($corrections as $correction)
                    <tr>
                        <td>

                            {{ $correction->status === 'pending' ? '承認待ち' : '承認済み' }}
                        </td>
                        <td>

                            {{ $correction->user->name }}
                        </td>
                        <td>

                            {{ \Carbon\Carbon::parse($correction->work_date)->format('Y/m/d') }}
                        </td>
                        <td>

                            {{ $correction->remarks }}
                        </td>
                        <td>

                            {{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}
                        </td>
                        <td>
                            <a href="{{ route('admin.correction.show', ['id' => $correction->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>

                @endforeach
            </table>
        </div>
    </div>

@endsection