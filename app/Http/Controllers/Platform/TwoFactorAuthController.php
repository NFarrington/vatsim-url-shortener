<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('platform');
        $this->middleware(function ($request, Closure $next) {
            if ($request->session()->has('auth.two-factor')) {
                return redirect()->intended(route('platform.dashboard'))
                    ->with('error', 'You have already provided your two factor authentication code.');
            }

            return $next($request);
        })->only(['showForm', 'login']);
    }

    public function showForm(Request $request)
    {
        return view('platform.two-factor');
    }

    public function login(Request $request)
    {
        $attributes = $this->validate($request, [
            'code' => 'required|numeric',
        ]);

        $valid = app(Google2FA::class)->verifyKey(
            $request->user()->getTotpSecret(),
            $attributes['code']
        );

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => ['Failed to verify code. Please try again.'],
            ]);
        }

        $request->session()->put('auth.two-factor', new Carbon());

        return redirect()->intended(route('platform.dashboard'))
            ->with('success', 'You are now logged in!');
    }
}
