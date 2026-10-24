<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorAuthController extends Controller
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->middleware('platform');
        $this->middleware(function ($request, Closure $next) {
            if ($request->session()->has('auth.two-factor')) {
                return redirect()->intended(route('platform.dashboard'))
                    ->with('error', 'You have already provided your two factor authentication code.');
            }

            if (!$request->user()->getTotpSecret()) {
                return redirect()->intended(route('platform.dashboard'));
            }

            return $next($request);
        })->only(['showForm', 'login']);
        $this->entityManager = $entityManager;
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

        $user = $request->user();

        // Only accept codes newer than the last one used, so a code can't be replayed.
        $timestamp = app(Google2FA::class)->verifyKeyNewer(
            $user->getTotpSecret(),
            $attributes['code'],
            $user->getTotpTimestamp() ?? 0
        );

        if ($timestamp === false) {
            throw ValidationException::withMessages([
                'code' => ['Failed to verify code. Please try again.'],
            ]);
        }

        $user->setTotpTimestamp($timestamp);
        $this->entityManager->flush();

        $request->session()->put('auth.two-factor', new Carbon());

        return redirect()->intended(route('platform.dashboard'))
            ->with('success', 'You are now logged in!');
    }
}
