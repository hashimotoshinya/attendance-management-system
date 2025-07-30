<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

        $user = User::where('email', $email)->first();

        // ユーザーが存在しない
        if (!$user) {
            return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
        }

        // 管理者ではない
        if (!AdminUser::where('email', $email)->exists()) {
            return back()->withErrors(['email' => '管理者アカウントとして認証されていません']);
        }

        // メール未認証
        if (is_null($user->email_verified_at)) {
            return back()->withErrors(['email' => 'メール認証が完了していません']);
        }

        // 認証処理
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