<?php

namespace App\Http\Controllers\Platform;

use App\Entities\User;
use App\Events\EmailChangedEvent;
use Carbon\Carbon;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FAQRCode\Google2FA;

class SettingsController extends Controller
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->middleware('platform');
        $this->middleware(function (Request $request, Closure $next) {
            if ($request->user()->getTotpSecret()) {
                return redirect()->route('platform.settings')
                    ->with('error', 'Two factor authentication has already been configured.');
            }

            return $next($request);
        })->only(['show2FAForm', 'register2FA']);
        $this->entityManager = $entityManager;
    }

    public function edit(Request $request)
    {
        return view('platform.settings.edit')->with([
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $attributes = $this->validate($request, [
            'email' => 'required|email|max:255|unique:'.User::class.",email,{$user->getId()}",
        ]);

        $oldEmail = $user->getEmail();
        $newEmail = $attributes['email'];
        if ($oldEmail !== $newEmail) {
            $user->setEmail($attributes['email']);
            $this->entityManager->flush();
            event(new EmailChangedEvent($user, $attributes['email'], $oldEmail));
        }

        return redirect()->route('platform.settings')
            ->with('success', 'Settings updated.');
    }

    public function show2FAForm(Request $request)
    {
        $secret = $request->session()->get('totp-secret')
            ?: tap(app(Google2FA::class)->generateSecretKey(), function ($secret) use ($request) {
                $request->session()->put('totp-secret', $secret);
            });

        $qrCode = app(Google2FA::class)->getQRCodeInline(
            config('app.name'),
            $request->user()->getEmail(),
            $secret,
            250
        );

        return view('platform.settings.two-factor')->with([
            'secret' => $secret,
            'qrCode' => $qrCode,
        ]);
    }

    public function register2FA(Request $request)
    {
        $attributes = $this->validate($request, [
            'code' => 'required|numeric',
        ]);

        $valid = app(Google2FA::class)->verifyKey(
            $secret = $request->session()->get('totp-secret'),
            $attributes['code']
        );

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => ['Failed to verify code. Please try again.'],
            ]);
        }

        $request->session()->remove('totp-secret');

        $request->user()->setTotpSecret($secret);
        $request->session()->put('auth.two-factor', new Carbon());

        $this->entityManager->flush();

        return redirect()->route('platform.settings')
            ->with('success', 'Two factor authentication configured successfully.');
    }

    public function delete2FA(Request $request)
    {
        $request->user()->setTotpSecret(null);
        $this->entityManager->flush();
        $request->session()->forget(['auth.two-factor', 'totp-secret']);

        return redirect()->route('platform.settings')
            ->with('success', 'Two factor authentication disabled successfully.');
    }
}
