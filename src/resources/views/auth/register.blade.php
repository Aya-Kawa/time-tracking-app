@extends('layouts.app')

@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{asset('css/auth.css')}}">
@endsection

@section('content')
    <div class="auth">
        <div class="auth__inner">
            <h1 class="auth__title">会員登録</h1>
            <form method="POST" action="/register" novalidate>
                @csrf

                <div class="form__group">
                    <label class="form__label">名前</label>
                    <input class="form__input" type="text" name="name" value="{{ old('name')}}">
                    @foreach($errors->get('name') as $message)
                        <p class="form__error">{{ $message }}</p>
                    @endforeach
                </div>

                <div class="form__group">
                    <label class="form__label">メールアドレス</label>
                    <input class="form__input" type="email" name="email" value="{{ old('email')}}">
                    @foreach($errors->get('email') as $message)
                        <p class="form__error">{{ $message }}</p>
                    @endforeach
                </div>

                <div class="form__group">
                    <label class="form__label">パスワード</label>
                    <input class="form__input" type="password" name="password" value="{{ old('password') }}">
                    @foreach($errors->get('password') as $message)
                        <p class="form__error">{{ $message }}</p>
                    @endforeach
                </div>

                <div class="form__group">
                    <label class="form__label">パスワード確認</label>
                    <input class="form__input" type="password" name="password_confirmation"
                        value="{{ old('password_confirmation') }}">
                    @foreach($errors->get('password_confirmation') as $message)
                        <p class="form__error">{{ $message }}</p>
                    @endforeach
                </div>

                <button class="form__button" type="submit">登録する</button>

            </form>

            <div class="auth__link">
                <p><a href="/login">ログインはこちら</a></p>
            </div>
        </div>
        <div>

@endsection