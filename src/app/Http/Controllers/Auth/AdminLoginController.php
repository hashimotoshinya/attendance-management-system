<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        session(['login_type' => 'admin']);
        return view('auth.admin_login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $email = $credentials['email'];

        // 管理者権限のあるメールか確認
    if (!\App\Models\AdminUser::where('email', $email)->exists()) {
        return back()->withErrors(['email' => '管理者アカウントとして認証されていません。']);
    }

        if (Auth::attempt($credentials)) {
            session(['login_type' => 'admin']);
            $request->session()->regenerate();

            return redirect()->intended('/admin/attendance/list');
        }

        return back()->withErrors(['email' => '認証に失敗しました']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}