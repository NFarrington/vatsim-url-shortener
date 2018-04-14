<?php

namespace App\Http\Controllers\Platform;

use App\Events\EmailChangedEvent;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('platform')->except('verifyEmail');
        $this->middleware('auth')->only('verifyEmail');
        $this->middleware(function ($request, Closure $next) {
            if ($request->user()->email && $request->user()->email_verified) {
                return redirect()->intended(route('platform.dashboard'))
                    ->with('error', 'You are already registered.');
            }

            return $next($request);
        })->except('verifyEmail');
    }

    /**
     * Show the application's registration form.
     *
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
        $attributes = $this->validate($request, [
            'email' => 'required|email|max:255',
        ]);

        $request->user()->update($attributes);

        event(new EmailChangedEvent($request->user()));

        return redirect()->route('register')
            ->with('success', 'Please check your inbox for a verification email.');
    }

    /**
     * Verify a user's email address.
     *
     * @param \Illuminate\Http\Request $request
     * @param $token
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function verifyEmail(Request $request, $token)
    {
        /** @var User $user */
        $user = $request->user();

        $verification = $user->emailVerification;

        if ($user->email_verified) {
            if ($verification) {
                $verification->delete();
            }

            return redirect()->intended(route('platform.dashboard'))
                ->with('error', 'Your email has already been verified.');
        }

        if (!$verification || !Hash::check($token, $verification->token)) {
            return redirect()->route('register')
                ->with('error', 'Invalid verification token.');
        }

        $user->email_verified = true;
        $user->save();
        $verification->delete();

        return redirect()->route('platform.dashboard')
            ->with('success', 'Your email has now been verified.');
    }
}
