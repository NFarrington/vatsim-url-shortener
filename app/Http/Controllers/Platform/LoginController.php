<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('platform.login');
    }

    public function logout(Request $request)
    {
        auth()->guard()->logout();

        $request->session()->invalidate();

        return redirect('/');
    }
}
