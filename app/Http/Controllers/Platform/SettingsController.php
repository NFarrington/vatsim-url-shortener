<?php

namespace App\Http\Controllers\Platform;

use App\Events\EmailChangedEvent;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, Closure $next) {
            if ($request->user()->totp_secret) {
                return redirect()->route('platform.settings')
                    ->with('error', 'Two factor authentication has already been configured.');
            }

            return $next($request);
        })->only(['show2FAForm', 'register2FA']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function edit(Request $request)
    {
        return view('platform.settings.edit')->with([
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $attributes = $this->validate($request, [
            'email' => 'required|email|max:255',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->fill($attributes);
        $emailChanged = $user->isDirty('email');
        $user->save();

        if ($emailChanged) {
            event(new EmailChangedEvent($user));
        }

        return redirect()->route('platform.settings')
            ->with('success', 'Settings updated.');
    }

    /**
     * Display the 2FA configuration form.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function show2FAForm(Request $request)
    {
        $secret = $request->user()->totp_secret;

        if (!$secret) {
            $secret = $request->session()->get('totp-secret');
        }

        if (!$secret) {
            $secret = app()->make(Google2FA::class)->generateSecretKey();
        }

        $request->session()->put('totp-secret', $secret);

        $qrCode = app()->make(Google2FA::class)->getQRCodeInline(
            config('app.name'),
            $request->user()->email,
            $secret,
            250
        );

        return view('platform.settings.two-factor')->with([
            'qrCode' => $qrCode,
            'secret' => $secret,
        ]);
    }

    /**
     * Set up and register 2FA.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register2FA(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|numeric',
        ]);

        $code = $request->input('code');

        $valid = app()->make(Google2FA::class)
            ->verifyKey($request->session()->get('totp-secret'), $code);

        if ($valid) {
            $secret = $request->session()->pull('totp-secret');
            $user = $request->user();

            $user->totp_secret = $secret;
            $user->save();

            $request->session()->put('auth.two-factor', new Carbon());

            return redirect()->route('platform.settings')
                ->with('success', 'Two factor authentication configured successfully.');
        }

        throw ValidationException::withMessages([
            'code' => ['Failed to verify code. Please try again.'],
        ]);
    }

    /**
     * Remove 2FA from the user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete2FA(Request $request)
    {
        $user = $request->user();
        $user->totp_secret = null;
        $user->save();

        $request->session()->forget(['auth.two-factor', 'totp-secret']);

        return redirect()->route('platform.settings')
            ->with('success', 'Two factor authentication disabled successfully.');
    }
}
