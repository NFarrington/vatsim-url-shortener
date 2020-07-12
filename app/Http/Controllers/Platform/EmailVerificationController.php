<?php

namespace App\Http\Controllers\Platform;

use App\Events\EmailVerifiedEvent;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmailVerificationController extends Controller
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->middleware('auth');
        $this->middleware(function (Request $request, Closure $next) {
            if ($request->user()->getEmailVerified()) {
                return redirect()->intended(route('platform.dashboard'))
                    ->with('error', 'Your email has already been verified.');
            }

            return $next($request);
        });
        $this->em = $entityManager;
    }

    public function verifyEmail(Request $request, string $token)
    {
        /* @var \App\Entities\User $user */
        $user = $request->user();

        if (!$user->getEmailVerification() || !Hash::check($token, $user->getEmailVerification()->getToken())) {
            return redirect()->route('platform.register')
                ->with('error', 'Invalid verification token.');
        }

        $user->setEmailVerified(true);
        $this->em->flush();
        event(new EmailVerifiedEvent($user));

        return redirect()->route('platform.dashboard')
            ->with('success', 'Your email has now been verified.');
    }
}
