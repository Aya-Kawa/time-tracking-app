<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function create()
    {
        return view('auth.admin-login');
    }

    public function store(AdminLoginRequest $request)
    {
        $credentials = $request->validated();

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません。',
            ]);

        }

        if (!Auth::user()->admin_status) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => '管理者以外はログインできません。',
            ]);
        }

        $request->session()->regenerate();

        return redirect('/');

    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    }
}
