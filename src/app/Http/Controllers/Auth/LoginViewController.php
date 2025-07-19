<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginViewController extends Controller
{
    public function showStaffLoginForm(Request $request)
    {
        session(['login_type' => 'staff']);
        return view('auth.staff_login');
    }

    public function showAdminLoginForm(Request $request)
    {
        session(['login_type' => 'admin']);
        return view('auth.admin_login');
    }
}
