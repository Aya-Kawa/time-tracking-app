@extends('layouts.app')


@section('content')
    <div>

        <p>
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="https://mailtrap.io/inboxes" target="_blank">
            認証はこちらから
        </a>

        @if (session('message'))
            <p>{{ session('message') }}</p>
        @endif
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit">認証メールを再送する</button>
        </form>
    </div>
@endsection