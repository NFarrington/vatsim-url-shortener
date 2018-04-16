<?php

namespace App\Http\Controllers\Platform;

use App\Events\EmailChangedEvent;
use Closure;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('platform');
        $this->middleware(function ($request, Closure $next) {
            if ($request->user()->email && $request->user()->email_verified) {
                return redirect()->intended(route('platform.dashboard'))
                    ->with('error', 'You are already registered.');
            }

            return $next($request);
        });
    }

    /**
     * Show the application's registration form.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm(Request $request)
    {
        return view('platform.register')->with([
            'user' => $request->user(),
        ]);
    }

    /**
     * Handle a registration request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $attributes = $this->validate($request, [
            'email' => "required|email|max:255|unique:users,email,{$user->id}",
        ]);

        $user->update($attributes);
        event(new EmailChangedEvent($user));

        return redirect()->route('platform.register')
            ->with('success', 'Please check your inbox for a verification email.');
    }
}
