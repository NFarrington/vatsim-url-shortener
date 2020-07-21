<?php

namespace App\Http\Controllers\Platform;

use App\Entities\User;
use App\Events\EmailChangedEvent;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->middleware('platform');
        $this->middleware(function ($request, Closure $next) {
            if ($request->user()->getEmail() && $request->user()->getEmailVerified()) {
                return redirect()->intended(route('platform.dashboard'))
                    ->with('error', 'You are already registered.');
            }

            return $next($request);
        });
        $this->entityManager = $entityManager;
    }

    public function showRegistrationForm(Request $request)
    {
        return view('platform.register')->with([
            'user' => $request->user(),
        ]);
    }

    public function register(Request $request)
    {
        $user = $request->user();

        $attributes = $this->validate($request, [
            'email' => 'required|email|max:255|unique:'.User::class.",email,{$user->getId()}",
        ]);

        $oldEmail = $user->getEmail();
        $newEmail = $attributes['email'];

        $user->setEmail($newEmail);
        event(new EmailChangedEvent($user, $newEmail, $oldEmail));
        $this->entityManager->flush();

        return redirect()->route('platform.register')
            ->with('success', 'Please check your inbox for a verification email.');
    }
}
