<?php

namespace App\Http\Controllers\Platform;

use App\Events\EmailVerifiedEvent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, Closure $next) {
            if ($request->user()->email_verified) {
                return redirect()->intended(route('platform.dashboard'))
                    ->with('error', 'Your email has already been verified.');
            }

            return $next($request);
        });
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
        /* @var \App\Models\User $user */
        $user = $request->user();

        if (!$user->emailVerification || !Hash::check($token, $user->emailVerification->token)) {
            return redirect()->route('platform.register')
                ->with('error', 'Invalid verification token.');
        }

        $user->email_verified = true;
        $user->save();
        event(new EmailVerifiedEvent($user));

        return redirect()->route('platform.dashboard')
            ->with('success', 'Your email has now been verified.');
    }
}
