@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/stafflist.css') }}">

@endsection

@section('content')
    <div class="staff-list">
        <div class="staff-list__inner">
            <h2 class="staff-list__title">

                スタッフ一覧
            </h2>
            <table class="staff-list__table">
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>

                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('admin.staff.attendance.list', $user->id) }}">
                                詳細
                            </a>
                        </td>
                    </tr>

                @endforeach
            </table>
        </div>
    </div>

@endsection