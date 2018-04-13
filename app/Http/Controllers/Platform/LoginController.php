<?php

namespace App\Http\Controllers\Platform;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('platform.login');
    }

    /**
     * The redirect path.
     *
     * @return string
     */
    protected function redirectTo()
    {
        return route('platform.dashboard');
    }
}
