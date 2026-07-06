@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
    <link rel="stylesheet" href="{{asset('css/auth.css')}}">
@endsection

@section('content')
    <div class="auth">
        <div class="auth__inner">
            <h1 class="auth__title">管理者ログイン</h1>

            <form action="/admin/login" method="POST" novalidate>
                @csrf
                <div class="form__group">
                    <label class="form__label">メールアドレス</label>
                    <input class="form__input" type="email" name="email" value="{{ old('email') }}">
                    @foreach($errors->get('email') as $message)
                        <p class="form__error">{{ $message }}</p>
                    @endforeach
                </div>

                <div class="form__group">
                    <label class="form__label">パスワード</label>
                    <input class="form__input " type="password" name="password">
                    @foreach($errors->get('password') as $message)
                        <p class="form__error">{{ $message }}</p>
                    @endforeach
                </div>

                <button class="form__button " type="submit">管理者ログインする</button>
            </form>

        </div>
        <div>
@endsection