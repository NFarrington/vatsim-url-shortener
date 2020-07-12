<?php

namespace App\Http\Controllers\Platform;

use App\Entities\User;
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
            if ($request->user()->getEmail() && $request->user()->getEmailVerified()) {
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
        /** @var \App\Entities\User $user */
        $user = $request->user();

        $attributes = $this->validate($request, [
            'email' => 'required|email|max:255|unique:'.User::class.",email,{$user->getId()}",
        ]);

        $oldEmail = $user->getEmail();
        $newEmail = $attributes['email'];

        $user->setEmail($newEmail);
        event(new EmailChangedEvent($user, $newEmail, $oldEmail));

        return redirect()->route('platform.register')
            ->with('success', 'Please check your inbox for a verification email.');
    }
}
